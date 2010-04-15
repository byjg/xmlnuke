<?php

require_once(PHPXMLNUKEDIR . 'bin/modules/oauthclient/oauth.class.php');
require_once(PHPXMLNUKEDIR . 'bin/modules/oauthclient/baseoauth.class.php');
require_once(PHPXMLNUKEDIR . 'bin/modules/oauthclient/twitteroauth.class.php');

class OAuthClient
{
	/* Consumer key form OAuth Server */
	protected $_consumer_key;
	/* Consumer Secret from OAuth Server */
	protected $_consumer_secret;
	/* Set state if previous session */
	protected $_state;
	/* Checks if oauth_token is set from returning from twitter */
	protected $_session_token;
	/* Checks if oauth_token is set from returning from twitter */
	protected $_oauth_token;

	protected $_className = "";
	protected $_extraArgs = array();

	/**
	 * @var Context
	 */
	protected $_context;
	protected $_saveToUser;
	protected $_user;

	/**
	 *
	 * @param Context $context
	 * @param string $appName
	 * @param bool $saveToUser
	 */
	public function  __construct($context, $appName, $saveToUser = false)
	{
		$this->_context = $context;
		$this->_saveToUser = $saveToUser;

		if ($saveToUser)
		{
			$users = $this->_context->getUsersDatabase();
			$this->_user = $users->getUserName($saveToUser);
			if ($this->_user == null)
			{
				throw new NotAuthenticatedException("You have to be authenticated to access this feature");
			}
		}

		$oauthFile = new AnydatasetFilenameProcessor("_oauthclient", $context);
		$oauthAny = new AnyDataSet($oauthFile);

		$itf = new IteratorFilter();
		$itf->addRelation("appname", Relation::Equal, $appName);
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
			throw new Exception("Cant find OAuth AppName " . $appName);
		}

	}

	protected function getVar($name)
	{
		if (!$this->_saveToUser)
		{
			return $this->_context->getSession($name);
		}
		else
		{
			return $this->_user->getField($name);
		}
	}

	protected function setVar($name, $value)
	{
		if (!$this->_saveToUser)
		{
			return $this->_context->setSession($name, $value);
		}
		else
		{
			$users = $this->_context->getUsersDatabase();
			$users->removePropertyValueFromUser($sr->getField($this->_UserTable->Id), null, $name);
			$users->addPropertyValueToUser($sr->getField($this->_UserTable->Id), $value, $name);
		}
	}

	public function handle()
	{
		$state = $this->getVar('oauth_state');

		/* If oauth_token is missing get it */
		if ($this->_context->ContextValue('oauth_token') != "" && $state === 'start')
		{/*{{{*/
			$this->setVar('oauth_state', 'returned');
			$state = 'returned';
		}/*}}}*/
		
		$class = new ReflectionClass($this->_className);

		switch ($state)
		{/*{{{*/
			default:
				/* Create TwitterOAuth object with app key/secret */
				$to = $class->newInstance($this->_consumer_key, $this->_consumer_secret);

				/* Request tokens from twitter */
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
					/* Create TwitterOAuth object with app key/secret and token key/secret from default phase */
					$to = $class->newInstance($this->_consumer_key, $this->_consumer_secret, $this->getVar('oauth_request_token'), $this->getVar('oauth_request_token_secret'));

					/* Request access tokens from twitter */
					$tok = $to->getAccessToken();

					/* Save the access tokens. Normally these would be saved in a database for future use. */
					$this->setVar('oauth_access_token', $tok['oauth_token']);
					$this->setVar('oauth_access_token_secret', $tok['oauth_token_secret']);
				}

				/* Create TwitterOAuth with app key/secret and user access key/secret */
				$to = $class->newInstance($this->_consumer_key, $this->_consumer_secret, $this->getVar('oauth_access_token'), $this->getVar('oauth_access_token_secret'));

				return $to;
				//$content = $to->OAuthRequest('https://twitter.com/statuses/replies.xml', array(), 'POST');
				break;
		}/*}}}*/
		
	}

}

?>
