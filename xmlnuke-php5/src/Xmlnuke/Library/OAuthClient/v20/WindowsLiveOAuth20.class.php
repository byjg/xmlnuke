<?php

namespace OAuthClient\v20;

use Exception;

/**
 * WindowsLiveOAuth20 is an OAuth 2.0 client implementation
 * More information can be found at http://msdn.microsoft.com/en-us/library/live/hh243647.aspx
 *
 * @author jg
 */

class WindowsLiveOAuth20 extends BaseOAuth20
{
	public function authorizationURL() { return "https://login.live.com/oauth20_authorize.srf"; }

	public function accessTokenURL() { return "https://login.live.com/oauth20_token.srf"; }
	
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
