<?php

/**
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Net;

use InvalidArgumentException;
use ReflectionClass;
use ByJG\AnyDataset\Repository\AnyDataset;
use ByJG\AnyDataset\Repository\IteratorFilter;
use Xmlnuke\Core\Engine\Context;
use ByJG\AnyDataset\Enum\Relation;
use Xmlnuke\Core\Exception\NotAuthenticatedException;
use Xmlnuke\Core\Processor\AnydatasetFilenameProcessor;

class OAuthClient10
{
	/* Consumer key form OAuth Server */
	protected $_consumer_key;
	/* Consumer Secret from OAuth Server */
	protected $_consumer_secret;
	/* Set state if previous session */
	protected $_state;
	/* Checks if oauth_token is set from returning from OAuth Server */
	protected $_session_token;
	/* Checks if oauth_token is set from returning from OAuth Server */
	protected $_oauth_token;

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
				throw new NotAuthenticatedException("You have to be authenticated to access this feature");
			}
		}

		$oauthFile = new AnydatasetFilenameProcessor("_oauthclient10");
		$oauthAny = new AnyDataset($oauthFile->FullQualifiedNameAndPath());

		$itf = new IteratorFilter();
		$itf->addRelation("appname",  Relation::EQUAL, $appName);
		$it = $oauthAny->getIterator($itf);

		if ($it->hasNext())
		{
			$sr = $it->moveNext();

			$this->_consumer_key = $sr->getField("consumer_key");
			$this->_consumer_secret = $sr->getField("consumer_secret");
			$this->_className = $sr->getField("appclass");

			$this->_extraArgs = $sr->getFieldArray("extra_arg");
		}
		else
		{
			throw new InvalidArgumentException("Cant find OAuth AppName " . $appName);
		}

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

	protected function getAccessToken()
	{
		if ($this->_saveToUser)
		{
			$names = array('oauth_access_token', 'oauth_access_token_secret');
			$users = $this->_context->getUsersDatabase();

			$this->_user = $users->getUserName($this->_saveToUser);
			
			foreach ($names as $name)
			{
				$field = $this->_appName . '_' . $name;
				$value = $this->_user->getField($field);
				if ($value == "")
					return; // Abort
				$this->setVar($name, $value);
			}
			
			$this->setVar("oauth_state", "returned");
		}
	}

	protected function saveAccessToken($forget = false)
	{
		$names = array('oauth_access_token', 'oauth_access_token_secret');
		
		if ($this->_saveToUser)
		{
			$users = $this->_context->getUsersDatabase();
		
			foreach ($names as $name)
			{
				$field = $this->_appName . '_' . $name;
				$users->removePropertyValueFromUser($this->_user->getField($users->getUserTable()->id), null, $field);
				
				if (!$forget)
					$users->addPropertyValueToUser($this->_user->getField($users->getUserTable()->id), $this->getVar($name), $field);
				else
					$this->forgetVar($name);
			}
		
			$this->_user = $users->getUserName($this->_saveToUser);
		}
	}
	
	public function forgetAccessToken()
	{
		$this->saveAccessToken(true); // Forget = true
	}

	public function handle()
	{
		$this->getAccessToken();
		
		$state = $this->getVar('oauth_state');
		
		/* If oauth_token is missing get it */
		if ($this->_context->get('oauth_token') != "" && $state === 'start')
		{/*{{{*/
			$this->setVar('oauth_state', 'returned');
			$state = 'returned';
		}/*}}}*/
		
		
		$class = new ReflectionClass($this->_className);

		switch ($state)
		{/*{{{*/
			default:
				/* Create CredentialsOAuth object with app key/secret */
				$to = $class->newInstance($this->_consumer_key, $this->_consumer_secret);

				/* Request tokens from OAuth Server */
				$tok = $to->getRequestToken();
				
				/* Save tokens for later */
				$this->setVar('oauth_request_token', $token = $tok['oauth_token']);
				$this->setVar('oauth_request_token_secret', $tok['oauth_token_secret']);
				$this->setVar('oauth_state', "start");

				/* Build the authorization URL */
				$request_link = $to->getAuthorizeURL($token);

				$this->_context->redirectUrl($request_link);
				break;
				
			case 'returned':
				/* If the access tokens are already set skip to the API call */
				if ($this->getVar('oauth_access_token') === "" && $this->getVar('oauth_access_token_secret') === "")
				{
					/* Create CredentialOAuth object with app key/secret and token key/secret from default phase */
					$to = $class->newInstance($this->_consumer_key, $this->_consumer_secret, $this->getVar('oauth_request_token'), $this->getVar('oauth_request_token_secret'));

					/* Request access tokens from OAuth Server */
					$tok = $to->getAccessToken();

					/* Save the access tokens. Normally these would be saved in a database for future use. */
					$this->setVar('oauth_access_token', $tok['oauth_token']);
					$this->setVar('oauth_access_token_secret', $tok['oauth_token_secret']);
					$this->saveAccessToken();
				}

				/* Create CredentialsOAuth with app key/secret and user access key/secret */
				$to = $class->newInstance($this->_consumer_key, $this->_consumer_secret, $this->getVar('oauth_access_token'), $this->getVar('oauth_access_token_secret'));

				return $to;
				break;
		}/*}}}*/
		
	}

}

?>
