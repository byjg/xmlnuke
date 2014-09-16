<?php

namespace OAuthClient\v20;

use Exception;

/**
 * WindowsLiveOAuth20 is an OAuth 2.0 client implementation
 * More information can be found at http://msdn.microsoft.com/en-us/library/live/hh243647.aspx
 *
 * @author jg
 */

class YahooOAuth20 extends BaseOAuth20
{
	public function authorizationURL() { return "https://api.login.yahoo.com/oauth2/request_auth"; }

	public function accessTokenURL() { return "https://api.login.yahoo.com/oauth2/get_token"; }
	
	public function validateRequest($result) {
		$statusCode = trim(parent::validateRequest($result));
		
		if ($statusCode == '200')
			return $result;
		else
		{
			if ($result != "")
			{
				$obj = json_decode($result);
				$statusCode .= ": " . $obj->error->message;
			}
			throw new Exception($statusCode . "\n\n" . $result);
		}
	}
	
	public function decodeAccessToken($result)
	{
		$response =  json_decode($result);
		$accessToken = $response->access_token;
		$this->_yahooGuid = $response->xoauth_yahoo_guid;
		//$response->refresh_token
		return $accessToken;
	}

	protected $_yahooGuid = null;

	public function getYahooGuid()
	{
		return $this->_yahooGuid;
	}

	public function getData($objectId = "me", $params = null)
	{
		return json_decode($this->get(($objectId[0] != "/" ? "/" : "") . $objectId, $params));
	}
	
	public function publishData($objectId = "me", $type = "", $params = null)
	{
		return json_decode($this->post(($objectId[0] != "/" ? "/" : "") . $objectId . "/" . $type, $params));
	}
}

?>
