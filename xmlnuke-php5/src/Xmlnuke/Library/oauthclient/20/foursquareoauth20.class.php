<?php

/**
 * FourSquareOAuth20 is an OAuth 2.0 client implementation
 * More information can be found at https://developer.foursquare.com/overview/auth
 *
 * @author jg
 */
class FourSquareOAuth20 
{
	public function authorizationURL() { return "https://foursquare.com/oauth2/authenticate"; }

	public function accessTokenURL() { return "https://foursquare.com/oauth2/access_token"; }
	
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
