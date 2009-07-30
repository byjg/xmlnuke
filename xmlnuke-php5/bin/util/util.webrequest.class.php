<?php
class WebRequest
{
	protected $_url;
	protected $_soapClass = null;
	protected $_username;
	protected $_password;
	
	public function __construct($url)
	{
		$this->_url = $url;
	}
	
	public function setCredentials($username, $password)
	{
		$this->_username = $username;
		$this->_password = $password;
	}

	/**
	 * 
	 * @return SoapClient
	 */
	protected function getSoapClient()
	{
		if ($this->_soapClass == null)
		{
			$this->_soapClass = new SoapClient(NULL,
			array(
				"location" => $url,
				"uri"      => "urn:xmethods-delayed-quotes",
				"style"    => SOAP_RPC,
				"use"      => SOAP_ENCODED
				)
			);
		}
		
		return $this->_soapClass;
	}
	
	public function Soap($method, $params)
	{
		$soapParams = array();
		foreach ($params as $key=>$value)
		{
			$soapParams[] = new SoapParam($value, $key);
		}
		
		if (sizeof($soapParams) == 0)
		{
			$soapParams = null;
		}
		
		// Chamando método do webservice
		$result = $this->getSoapClient()->__call(
			$method,
			$soapParams,
	        // Opções
        	array(
	            "uri" => "urn:xmethods-delayed-quotes",
            	"soapaction" => "urn:xmethods-delayed-quotes#getQuote"
        	)
		);
	
		return $result;
	}
	
	
	protected function CurlWrapper($post=false, $fields = null, $content_type = null, $data = null)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->_url); 
		//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");		
		//curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		// Check if pass credentials
		if (($this->_username != "") && ($this->_password != ""))
		{
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($curl, CURLOPT_USERPWD, $this->_username . ":" . $this->_password);
		}

		// Check if pass Fields
		if (is_array($fields) && (sizeof($fields) > 0))
		{
			$fields_string = "";
			foreach($fields as $key=>$value) 
			{ 
				$fields_string .= ($fields_string != "" ? "&" : "") . $key.'='.$value; 
			}

			if ($post)
			{
				curl_setopt($curl, CURLOPT_POST, count($fields));
				curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
			}
			else
			{
				// Re-define
				if ($fields_string != "")
				{
					curl_setopt($curl, CURLOPT_URL, $this->_url . (strpos($this->_url, "?") === false ? "&" : "?") . $query);
				} 
			}
		}
		// Check if pass file
		elseif ($content_type != null)
		{
			curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type: $content_type")); 
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);		
		}
				
		$result = curl_exec($curl);
		if ($result === false)
		{
			throw new Exception("CURL - " . curl_error($curl));			
		}
		else
		{
			return $result;
		}
	} 

	
	public function Get($params = null)
	{
		return $this->CurlWrapper(false, $params); 
	}

	public function Post($params)
	{
		return $this->CurlWrapper(true, $params);
	}

	public function PostFile($content_type, $data)
	{
		return $this->CurlWrapper(true, null, $content_type, $data);
	}
	
	
}


?>