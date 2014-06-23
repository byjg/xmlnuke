<?php

/**
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Net;

use InvalidArgumentException;
use OAuthClient\v20\BaseOAuth20;
use Xmlnuke\Core\AnyDataset\AnyDataSet;
use Xmlnuke\Core\AnyDataset\IteratorFilter;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Enum\Relation;
use Xmlnuke\Core\Exception\NotAuthenticatedException;
use Xmlnuke\Core\Processor\AnydatasetFilenameProcessor;
use Xmlnuke\Util\WebRequest;

class OAuthClient20
{
	/* The OAuth Client ID Parameter */
	protected $_client_id;
	/* The OAuth Client Secret Parameter */
	protected $_client_secret;
	/* Redirect URI */
	protected $_redirect_uri;
	/* Scope */
	protected $_scope;
	
	/** SPECIFIC CUSTOMIZATIONS **/
	
	/* App Uri to be redirect after the login. It is necessary if the window_top is true mostly */
	protected $_app_uri;
	/* If true the browser will be redirected to the window.top.location. */
	protected $_window_top;
	

	protected $_className = "";
	protected $_extraArgs = array();

	/**
	 * @var Context
	 */
	protected $_context;
	protected $_saveToUser;
	protected $_appName;
	protected $_user;

	/**
	 *
	 * @param Context $context
	 * @param string $appName
	 * @param bool $saveToUser
	 */
	public function  __construct($appName, $saveToUser = false)
	{
		$this->_context = Context::getInstance();
		$this->_saveToUser = $saveToUser;
		$this->_appName = $appName;

		if ($saveToUser)
		{
			$users = $this->_context->getUsersDatabase();
			$this->_user = $users->getUserName($saveToUser);
			if ($this->_user == null)
			{
				throw new NotAuthenticatedException("You need to be authenticated in order to save data into a existing user");
			}
		}

		$oauthFile = new AnydatasetFilenameProcessor("_oauthclient20");
		$oauthAny = new AnyDataSet($oauthFile);

		$itf = new IteratorFilter();
		$itf->addRelation("appname", Relation::Equal, $appName);
		$it = $oauthAny->getIterator($itf);

		if ($it->hasNext())
		{
			$sr = $it->moveNext();

			$this->_client_id = $sr->getField("client_id");
			$this->_client_secret = $sr->getField("client_secret");
			$this->_redirect_uri = $sr->getField("redirect_uri");
			$this->_scope = $sr->getField("scope") != "" ? $sr->getField("scope") : $this->_scope;
			
			$this->_window_top = ($sr->getField("window_top") == "true");
			$this->_app_uri = $sr->getField("app_uri");
			
			$this->_className = $sr->getField("appclass");
			$this->_extraArgs = $sr->getFieldArray("extra_arg");
		}
		else
		{
			throw new InvalidArgumentException("The OAuth 2.0 AppName " . $appName . " is not defined in _oauthclient20.anydata.xml file");
		}

	}

	public static function existsApp($appName)
	{
		$oauthFile = new AnydatasetFilenameProcessor("_oauthclient20");
		$oauthAny = new AnyDataSet($oauthFile);

		$itf = new IteratorFilter();
		$itf->addRelation("appname", Relation::Equal, $appName);
		$it = $oauthAny->getIterator($itf);

		return ($it->hasNext());
	}

	protected function getVar($name)
	{
		$name = $this->_appName . '_' . $name;		
		return $this->_context->getSession($name);
	}

	protected function setVar($name, $value)
	{
		$name = $this->_appName . '_' . $name;
		return $this->_context->setSession($name, $value);
	}

	protected function forgetVar($name)
	{
		$name = $this->_appName . '_' . $name;
		return $this->_context->removeSession($name);
	}

	public function getAccessToken()
	{
		$name = 'access_token';
		
		if ($this->_saveToUser)
		{
			$users = $this->_context->getUsersDatabase();

			$this->_user = $users->getUserName($this->_saveToUser);
			
			$field = $this->_appName . '_' . $name;
			$value = $this->_user->getField($field);
			if ($value == "")
				return ""; // Abort
			$this->setVar($name, $value);
			
			return $value;
		}
		else
			return $this->getVar($name);
	}

	protected function saveAccessToken($forget = false)
	{
		$name = 'access_token';
		
		if ($this->_saveToUser)
		{
			$users = $this->_context->getUsersDatabase();
		
			$field = $this->_appName . '_' . $name;
			$users->removePropertyValueFromUser($this->_user->getField($users->getUserTable()->Id), null, $field);

			if (!$forget)
				$users->addPropertyValueToUser($this->_user->getField($users->getUserTable()->Id), $this->getVar($name), $field);
			else
					$this->forgetVar($name);
		
			$this->_user = $users->getUserName($this->_saveToUser);
		}
	}
	
	public function forgetAccessToken()
	{
		$this->saveAccessToken(true); // Forget = true
	}

	/**
	 * Handle OAuth 2.0 Flow
	 * @return BaseOAuth20
	 */
	public function handle()
	{
		// Get Var Elements
		$accessToken = $this->getAccessToken();

		$state = $this->getVar("state");
		
		// Initiate OAuth Client with Specific server configuration
		$to = new $this->_className();

		// Try to Handle the Authentication Process
		if ($accessToken == "")
		{
			$code = $this->_context->get("code");
			
			// If not received the "Code" Parameter, initiate the autorization request
			if ($code == "")
			{
				$state = md5(uniqid(rand(), TRUE)); //CSRF protection
				$this->setVar("state", $state);
				
				$params = array(
					"client_id" => $this->_client_id,
					"redirect_uri" => $this->_redirect_uri,
					"state" => $state,
					"scope" => $this->_scope
				);
				
				$req = new WebRequest($to->authorizationURL());
				$req->Redirect($params, $this->_window_top);
			}
			
			// Request the Access Token
			if ($this->_context->get("state") == $this->getVar("state"))
			{
				$params = array(
					"client_id" => $this->_client_id,
					"redirect_uri" => $this->_redirect_uri,
					"client_secret" => $this->_client_secret,
					"code" => $code
				);
				
				$req = new WebRequest($to->accessTokenURL());
				$response = $req->Get($params);

				$paramsResp = null;
				parse_str($response, $paramsResp);
				
				$accessToken = $paramsResp['access_token'];
				
				$this->setVar("access_token", $accessToken);
				$to->setAccessToken($accessToken);
				
				$this->saveAccessToken();
				
				if ($this->_app_uri != "")
				{
					$req = new WebRequest($this->_app_uri);
					$response = $req->Redirect();
				}
			}
		}
		else 
		{
			$to->setAccessToken($this->getVar('access_token'));
		}
		
		return $to;
		
	}

}

?>
