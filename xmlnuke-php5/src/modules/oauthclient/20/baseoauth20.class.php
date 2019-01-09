<?php
/*
 * Abraham Williams (abraham@abrah.am) http://abrah.am
 *
 * Basic lib to work with Twitter's OAuth beta. This is untested and should not
 * be used in production code. Twitter's beta could change at anytime.
 *
 * Code based on:
 * Fire Eagle code - http://github.com/myelin/fireeagle-php-lib
 * twitterlibphp - http://github.com/poseurtech/twitterlibphp
 */

/* Load OAuth lib. You can find it at http://oauth.net */

/**
 * Changes in this lib for XMLNuke support
 * João Gilberto Magalhães
 */

use ByJG\Util\WebRequest;


/**
 * Base OAuth class
 */
abstract class baseOAuth20 {/*{{{*/

	protected $_lastStatusCode = "";
	protected $_accessToken = "";

	/**
	* Set API URLS
	*/
	abstract function authorizationURL();
	abstract function accessTokenURL();

	/**
	* It is a good idea to implement this also
	*/
	function validateRequest($result)
	{
		$statusCodes = array(
			"200" => "",
			"304" => "Not Modified",
			"400" => "Bad Request",
			"401" => "Unauthorized: Authentication credentials were missing or incorrect",
			"403" => "Forbidden: The request is understood, but it has been refused",
			"404" => "Not Found",
			"406" => "Not Acceptable",
			"420" => "Enhance Your Calm",
			"500" => "Internal Server Error",
			"502" => "Bad Gateway",
			"503" => "Service Unavailable"
		);

		if (array_key_exists($this->lastStatusCode(), $statusCodes))
			return $this->lastStatusCode() . " " . $statusCodes[$this->lastStatusCode()];
		else {
			return $this->lastStatusCode() . " Unknow";
		}	
	}
	
	public function setAccessToken($value)
	{
		$this->_accessToken = $value;
	}
	
	public function lastStatusCode()
	{
		return $this->_lastStatusCode;
	}
	
	protected function preparedUrl($url)
	{
		if (strpos($url, "?") === false)
			$url .= "?";
		else
			$url .= "&";
		
		$url .= "access_token=" . $this->_accessToken;
		
		return $url;
	}
	
	public function get($url, $params = null)
	{
		$req = new WebRequest($this->preparedUrl($url));
		$result = $req->get($params);
		$this->_lastStatusCode = $req->getLastStatus();
		return $this->validateRequest($result);
	}
	
	public function post($url, $params = null)
	{
		$req = new WebRequest($this->preparedUrl($url));
		$result = $req->post($params);
		$this->_lastStatusCode = $req->getLastStatus();
		return $this->validateRequest($result);
	}

	public function delete($url, $params = null)
	{
		$req = new WebRequest($this->preparedUrl($url));
		$result = $req->delete($params);
		$this->_lastStatusCode = $req->getLastStatus();
		return $this->validateRequest($result);
	}
	
}