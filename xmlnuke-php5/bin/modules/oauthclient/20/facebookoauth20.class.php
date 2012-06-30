<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of facebookoauth20
 *
 * @author jg
 */
class FacebookOAuth20 extends baseOAuth20
{
	protected $GRAPH_API = "https://graph.facebook.com";
	
	public function authorizationURL() { return "http://www.facebook.com/dialog/oauth"; }

	public function accessTokenURL() { return "https://graph.facebook.com/oauth/access_token"; }
	
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
