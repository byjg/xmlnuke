<?php

namespace OAuthClient\v10;

use Exception;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of linkedinoauth
 *
 * @author jg
 */
class LinkedinOAuth extends BaseOAuth
{
	/* Set up the API root URL */
	public static $TO_API_ROOT = "https://api.linkedin.com";

	/**
	* Set API URLS
	*/
	function requestTokenURL() { return self::$TO_API_ROOT.'/uas/oauth/requestToken'; }
	function authorizeURL() { return self::$TO_API_ROOT.'/uas/oauth/authenticate'; }
	function accessTokenURL() { return self::$TO_API_ROOT.'/uas/oauth/accessToken'; }

	function validateRequest($result) 
	{
		$status = trim(parent::validateRequest($result));
		
		if ($status != "200")
		{
			$obj = json_decode($result);
			throw new Exception($status . ": " . ($obj->error != "" ? $obj->error : $result));
		}
	}
	
	public function getData($data, $params = null)
	{
		return json_decode($this->OAuthRequest(self::$TO_API_ROOT . $data . ".json", ($params == null ? array() : $params), 'GET'));
	}
	
	public function publishData($data, $params = null)
	{
		return json_decode($this->OAuthRequest(self::$TO_API_ROOT . $data . ".json", ($params == null ? array() : $params), 'POST'));
	}
}

?>
