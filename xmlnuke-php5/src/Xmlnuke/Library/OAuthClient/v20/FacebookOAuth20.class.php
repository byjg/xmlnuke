<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OAuthClient\v20;

use Exception;

/**
 * Facebook20 is an OAuth 2.0 client implementation
 * More information can be found at https://developers.facebook.com/docs/reference/api/
 *
 * @author jg
 */
class FacebookOAuth20 extends BaseOAuth20
{
	protected $GRAPH_API = "https://graph.facebook.com/v2.0";
	
	public function authorizationURL() { return "http://www.facebook.com/v2.0/dialog/oauth"; }

	public function accessTokenURL() { return "https://graph.facebook.com/v2.0/oauth/access_token"; }
	
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
		$paramsResp = null;
		parse_str($result, $paramsResp);
		$accessToken = $paramsResp['access_token'];
		return $accessToken;
	}
	
	protected function preparedUrl($url) {
		return parent::preparedUrl($this->GRAPH_API . $url);
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
