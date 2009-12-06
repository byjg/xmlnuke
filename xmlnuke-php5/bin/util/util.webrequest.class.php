<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*
*  This file is part of XMLNuke project. Visit http://www.xmlnuke.com
*  for more information.
*
*  This program is free software; you can redistribute it and/or
*  modify it under the terms of the GNU General Public License
*  as published by the Free Software Foundation; either version 2
*  of the License, or (at your option) any later version.
*
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with this program; if not, write to the Free Software
*  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*/

/**
 * Class to abstract Soap and REST calls
 * @author jg
 *
 */
class WebRequest
{
	protected $_url;
	protected $_soapClass = null;
	protected $_username;
	protected $_password;
	protected $_referer;
	protected $_header = false;
	protected $_followLocation = true;

	const POST = "POST";
	const PUT = "PUT";
	const GET = "GET";
	const DELETE = "DELETE";

	public function __construct($url)
	{
		$this->_url = $url;
	}

	/**
	 * Defines Basic credentials for access the service.
	 * @param $username
	 * @param $password
	 * @return unknown_type
	 */
	public function setCredentials($username, $password)
	{
		$this->_username = $username;
		$this->_password = $password;
	}

	public function getReferer()
	{
		return $this->_referer;
	}
	/**
	 *
	 * @param string $value
	 * @return unknown_type
	 */
	public function setReferer($value)
	{
		$this->_referer = $value;
	}

	public function getOutputHeader()
	{
		return $this->_header;
	}
	/**
	 *
	 * @param bool $value
	 * @return unknown_type
	 */
	public function setOutputHeader($value)
	{
		$this->_header = $value;
	}

	public function getFollowLocation()
	{
		return $this->_followLocation;
	}
	/**
	 *
	 * @param bool $value
	 * @return unknown_type
	 */
	public function setFollowLocation($value)
	{
		$this->_followLocation = $value;
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
				"location" => $this->_url,
				"uri"      => "urn:xmethods-delayed-quotes",
				"style"    => SOAP_RPC,
				"use"      => SOAP_ENCODED
				)
			);

			if (($this->_username != "") && ($this->_password != ""))
			{
				//$this->_soapClass-> setCredentials($this->_username, $this->_password);
			}
		}

		return $this->_soapClass;
	}

	/**
	 * Call a Soap client.
	 *
	 * For example:
	 *
	 * $webreq = new WebRequest("http://www.byjg.com.br/webservice.php/ws/cep");
	 * $result = $webreq->Soap("obterCep", new array("cep", "11111233"));
	 *
	 * @param string $method
	 * @param array $params
	 * @return object
	 */
	public function Soap($method, $params = null)
	{
		if (is_array($params))
		{
			$soapParams = array();
			foreach ($params as $key=>$value)
			{
				$soapParams[] = new SoapParam($value, $key);
			}
		}
		else
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
		curl_setopt($curl, CURLOPT_HEADER, $this->_header);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $this->_followLocation);
		# sometimes we need this for https to work
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		if ($this->getReferer() != "")
		{
			curl_setopt($ch, CURLOPT_REFERER , $this->getReferer());
		}

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
					curl_setopt($curl, CURLOPT_URL, $this->_url . (strpos($this->_url, "?") === false ? "?" : "&") . $fields_string);
				}
				break;

			case WebRequest::GET:
				if ($fields_string != "")
				{
					curl_setopt($curl, CURLOPT_URL, $this->_url . (strpos($this->_url, "?") === false ? "?" : "&") . $fields_string);
				}
				break;
		}

		$result = curl_exec($curl);
    	$this->_header = curl_getinfo($curl);
		if ($result === false)
		{
			throw new Exception("CURL - " . curl_error($curl));
		}
		else
		{
			return $result;
		}
	}


	/**
	 * Make a REST Get method call
	 * @param array $params
	 * @return string
	 */
	public function Get($params = null)
	{
		return $this->CurlWrapper(WebRequest::GET, $params);
	}

	/**
	 * Make a REST POST method call with parameters
	 * @param array $params
	 * @return string
	 */
	public function Post($params)
	{
		return $this->CurlWrapper(WebRequest::POST, $params);
	}

	/**
	 * Make a REST POST method call sending a file
	 * @param array $params
	 * @return string
	 */
	public function PostFile($data, $content_type = "text/plain")
	{
		return $this->CurlWrapper(WebRequest::POST, null, $content_type, $data);
	}

	/**
	 * Make a REST PUT method call with parameters
	 * @param array $params
	 * @return string
	 */
	public function Put($params)
	{
		return $this->CurlWrapper(WebRequest::PUT, $params);
	}

	/**
	 * Make a REST PUT method call sending a file
	 * @param array $params
	 * @return string
	 */
	public function PutFile($data, $content_type = "text/plain")
	{
		return $this->CurlWrapper(WebRequest::PUT, null, $content_type, $data);
	}

	/**
	 * Make a REST DELETE method call with parameters
	 * @param array $params
	 * @return string
	 */
	public function Delete($params = null)
	{
		return $this->CurlWrapper(WebRequest::DELETE, $params);
	}

}


?>