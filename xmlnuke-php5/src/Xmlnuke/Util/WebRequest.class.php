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
namespace Xmlnuke\Util;

use Exception;
use SoapClient;
use SoapParam;
use Xmlnuke\Core\Engine\Context;

class WebRequest
{
	protected $_url;
	protected $_soapClass = null;
	protected $_username;
	protected $_password;
	protected $_referer;
	protected $_requestHeader = array();
	protected $_responseHeader = null;
	protected $_cookies = array();
	protected $_followLocation = true;
	protected $_lastStatus = "";

	const POST = "POST";
	const PUT = "PUT";
	const GET = "GET";
	const DELETE = "DELETE";

	/**
	 *
	 * @param string $url
	 */
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

	public function getLastStatus()
	{
		return $this->_lastStatus;
	}

	public function getResponseHeader()
	{
		return $this->_responseHeader;
	}

	/**
	 *
	 * @param mixed $key Key may be a string or an associative array. In this case value have to be null;
	 * @param string $value
	 */
	public function addRequestHeader($key, $value = null)
	{
		if (is_array($key))
		{
			foreach ($key as $newKey=>$newValue)
				$this->addRequestHeader($newKey, $newValue);
		}
		else
		{
			$key = preg_replace_callback('/([\s\-_]|^)([a-z0-9-_])/',
				function($match) {
					return strtoupper($match[0]);
				},
				$key
			);
			$this->_requestHeader[] = "$key: $value";
		}
	}

	/**
	 *
	 * @param mixed $key Key may be a string or an associative array. In this case value have to be null;
	 * @param string $value If value is null so, try to parse
	 */
	public function addCookie($key, $value = null)
	{
		if (is_array($key))
		{
			foreach ($key as $newKey=>$newValue)
				$this->addCookie($newKey, $newValue);
		}
		else
		{
			$value = preg_replace('/(;\s*path=.+)/', '', $value);

			if (is_numeric($key))
				$this->_cookies[] = $value;
			else
				$this->_cookies[] = "$key=$value";
		}
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
	public function Soap($method, $params = null, $soapOptions = null)
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

		if (!is_array($soapOptions) || ($soapOptions == null))
		{
			$soapOptions = array(
	            "uri" => "urn:xmethods-delayed-quotes",
            	"soapaction" => "urn:xmethods-delayed-quotes#getQuote"
        	);
		}

		// Chamando método do webservice
		$result = $this->getSoapClient()->__call(
			$method,
			$soapParams,
	        $soapOptions
		);

		return $result;
	}


	protected function CurlWrapper($method, $fields = null, $content_type = null, $data = null)
	{
		$curl = curl_init();
	    if (defined("CURL_CA_BUNDLE_PATH")) curl_setopt($ch, CURLOPT_CAINFO, CURL_CA_BUNDLE_PATH);
		curl_setopt($curl, CURLOPT_URL, $this->_url);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		//curl_setopt($hCurl, CURLOPT_STDERR, fopen('php://output', 'w+'));
		//curl_setopt($hCurl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_HEADER, true);
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
		
		// Add the content-type
		if ($content_type != null)
		{
			$this->addRequestHeader("Content-Type", $content_type);
		}

		// Adjust parameters
		$fields_string = "";
		if (is_array($fields) && (sizeof($fields) > 0))
		{
			foreach($fields as $key=>$value)
			{
				if (!is_array($value))
					$fields_string .= ($fields_string != "" ? "&" : "") . $key.'='.urlencode($value);
				else 
				{
					foreach ($value as $valueItem)
					{
						$fields_string .= ($fields_string != "" ? "&" : "") . $key.'='.urlencode($valueItem);
					}
				}
			}
		}
		// Check if pass file
		elseif ($data != null)
		{
			$fields_string = $data;			
		}

		// Check if have header
		if (count($this->_requestHeader) > 0)
		{
			curl_setopt($curl, CURLOPT_HTTPHEADER, $this->_requestHeader);
			$this->_requestHeader = array(); // Reset request Header
		}

		// Add Cookies
		if (count($this->_cookies) > 0)
		{
			curl_setopt($curl, CURLOPT_COOKIE, implode(";", $this->_cookies));
			$this->_cookies = array(); // Reset request Header
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

		$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$this->_header = curl_getinfo($curl);
		$this->_lastStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		
		if ($result === false)
		{
			throw new Exception("CURL - " . curl_error($curl));
		}
		else
		{
			$this->_responseHeader = $this->parseHeader(substr($result, 0, $header_size));
			return substr($result, $header_size);
		}
	}

	protected function parseHeader($raw_headers)
	{
        $headers = array();
        $key = ''; // [+]

        foreach(explode("\n", $raw_headers) as $i => $h)
        {
            $h = explode(':', $h, 2);

            if (isset($h[1]))
            {
                if (!isset($headers[$h[0]]))
                    $headers[$h[0]] = trim($h[1]);
                elseif (is_array($headers[$h[0]]))
                {
                    // $tmp = array_merge($headers[$h[0]], array(trim($h[1]))); // [-]
                    // $headers[$h[0]] = $tmp; // [-]
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1]))); // [+]
                }
                else
                {
                    // $tmp = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [-]
                    // $headers[$h[0]] = $tmp; // [-]
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [+]
                }

                $key = $h[0]; // [+]
            }
            else // [+]
            { // [+]
                if (substr($h[0], 0, 1) == "\t") // [+]
                    $headers[$key] .= "\r\n\t".trim($h[0]); // [+]
                elseif (!$key) // [+]
                    $headers[0] = trim($h[0]);trim($h[0]); // [+]
            } // [+]
        }
		return $headers;
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
	
	/**
	 * Makes a URL Redirection based on the current client navigation (Browser)
	 * @param type $params
	 * @param type $atClientSide 
	 */
	public function Redirect($params = null, $atClientSide = false)
	{
		$url = $this->_url;
		
		if ($params != null)
		{
			if (strpos($url, "?") === false)
				$sep = "?";
			else
				$sep = "&";
			
			foreach ($params as $key=>$value)
			{
				$url .= $sep . $key . "=" . urlencode($value);
				$sep = "&";
			}
		}
		
		if (!$atClientSide)
			Context::getInstance()->redirectUrl($url);
		else
		{
			ob_clean();
			echo "<script language='javascript'>window.top.location = '" . $url . "'; </script>";
			die();
		}
	}

}


?>
