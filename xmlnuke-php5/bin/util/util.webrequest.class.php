<?php
class WebRequest
{
	protected $_url;
	protected $_soapClass = null;
	protected $_username;
	protected $_password;
	
	const POST = "POST";
	const PUT = "PUT";
	const GET = "GET";
	const DELETE = "DELETE";
	
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
	
	
	protected function CurlWrapper($method, $fields = null, $content_type = null, $data = null)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->_url); 
		//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");		
		//curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		# sometimes we need this for https to work
		//curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
		//curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

		// Check if pass credentials
		if (($this->_username != "") && ($this->_password != ""))
		{
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($curl, CURLOPT_USERPWD, $this->_username . ":" . $this->_password);
		}

		// Adjust parameters
		if (is_array($fields) && (sizeof($fields) > 0))
		{
			$fields_string = "";
			foreach($fields as $key=>$value) 
			{ 
				$fields_string .= ($fields_string != "" ? "&" : "") . $key.'='.$value; 
			}
		}
		// Check if pass file
		elseif ($content_type != null)
		{
			curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type: $content_type"));
			$fields_string = $data; 
		}
		
		// Set the proper method
		switch($method)
		{
			case WebRequest::POST:
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
				break;
		
			case WebRequest::PUT:
				curl_setopt($curl, CURLOPT_PUT, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
				break;
		
			case WebRequest::DELETE:
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if ($fields_string != "")
				{
					curl_setopt($curl, CURLOPT_URL, $this->_url . (strpos($this->_url, "?") === false ? "&" : "?") . $fields_string);
				} 
				break;
				
			case WebRequest::GET:
				if ($fields_string != "")
				{
					curl_setopt($curl, CURLOPT_URL, $this->_url . (strpos($this->_url, "?") === false ? "&" : "?") . $fields_string);
				} 
				break;
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
		return $this->CurlWrapper(WebRequest::GET, $params); 
	}

	public function Post($params)
	{
		return $this->CurlWrapper(WebRequest::POST, $params);
	}

	public function PostFile($data, $content_type = "text/plain")
	{
		return $this->CurlWrapper(WebRequest::POST, null, $content_type, $data);
	}
	
	public function Put($params)
	{
		return $this->CurlWrapper(WebRequest::PUT, $params);
	}

	public function PutFile($data, $content_type = "text/plain")
	{
		return $this->CurlWrapper(WebRequest::PUT, null, $content_type, $data);
	}
	
	public function Delete($params = null)
	{
		return $this->CurlWrapper(WebRequest::DELETE, $params); 
	}
	
}


?>