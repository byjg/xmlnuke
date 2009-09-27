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

class URLTYPE
{
	const ADMINENGINE = 'admin:engine';
	const ADMIN = 'admin:';
	const ENGINE = 'engine:xmlnuke';
	const MODULE = 'module:';
	const HTTP = 'http://';
	const FTP = 'ftp://';
	const JAVASCRIPT = 'javascript:';
}

/**
*this Class manager url to Xmlnuke engine
*@package com.xmlnuke
*/
class XmlnukeManageUrl
{
	/**
	@var string
	*/
	protected $_urltype;

	/**
	@var string
	*/
	protected $_target;
	/**
	*@var array
	*/
	private $_parameters = array();

	/**
	*@param URLTYPE $urltype
	*@param string $target
	*@desc XmlnukeManageUrl Constructor
	*If URLTYPE is MODULE $target must to be the Module name target
	*If URLTYPE is ENGINE or ADMIN $target must to be NULL
	*If URLTYPE is HTTP $target must to be the full URL whitout "http://"
	*If URLTYPE is JAVASCRIPT $target must to be the javascript command whitout "http://javascript:"
	*/
	public function XmlnukeManageUrl($urltype, $target="")
	{
		$this->_urltype = $urltype;
		$arr = explode("?", $target);
		$this->_target = $arr[0];
		if (sizeof($arr)==2)
		{
			$params = explode("&", $arr[1]);
			foreach ($params as $value)
			{
				$paramPart = explode("=", $value);
				$this->addParam($paramPart[0], $paramPart[1]);
			}
		}
	}

	/**
	*@desc Add a param to url
	*@param string $param
	*@param string $value
	*@return void
	*/
	public function addParam($param, $value)
	{
		$this->_parameters[$param] = $value;
	}
	/**
	*@desc Build URL link based on xmlnuke model.
	*@desc Note: target must be the follow values:
	*@desc  - site if URLTYPE is equal to ENGINE or ADMIN
	*@desc  - module is URLTYPE is equal to MODULE
	*@desc  - Full URL (without protocol) if any other.
	*@return string
	*/
	public function getUrl()
	{
		if ($this->_urltype == URLTYPE::ENGINE || $this->_urltype == URLTYPE::ADMINENGINE )
		{
			$url = $this->_urltype;
		}
		else
		{
			$url = $this->_target;
			if ($this->_urltype == URLTYPE::MODULE || $this->_urltype == URLTYPE::JAVASCRIPT || $this->_urltype == URLTYPE::ADMIN)
			{
				if (strpos($this->_target, $this->_urltype) === false)
				{
					$url = $this->_urltype . $url;
				}
			}
		}

		$separator = (strpos($this->_target, "?")===false ? '?' : "&");

		$count = 0;
		foreach ($this->_parameters as $param => $value)
		{
			if ($count > 0)
			{
				$separator = '&';
			}
			$count++;
			$url .= $separator . $param . '=' . XmlnukeManageUrl::encodeParam($value);
		}
		return str_replace('&', '&amp;', $url);
	}

	/**
	*@param Context $context
	*@return string
	*@desc Build full URL.
	*/
	public function getUrlFull($context)
	{
		$url = $this->_target;
		$separator = "?";

		foreach ($this->_parameters as $param => $value)
		{
			if ($separator == "?")
			{
				if (strpos($url, "?") !== false)
				{
					$separator = '&';
				}
			}

			$url .= $separator . $param . "=" . urlencode($value);
		}

		$processor = new ParamProcessor($context);

		return htmlentities($processor->GetFullLink($url));
	}

	/**
	*@param string $param
	*@return string
	*@desc Build the full URL link
	*/
	public static function encodeParam($param)
	{
		$param = str_replace("/", "%2f",$param);
		$param = str_replace("?", "%3f",$param);
		$param = str_replace("&", "%26",$param);
		$param = str_replace("=", "%3d",$param);

		return $param;
	}

	/**
	* @param string $parameter
	* @access public
	* @return string
	*/
	public static function decodeParam($parameter)
	{
		$parameter = str_replace("%2f", "/", $parameter);
		$parameter = str_replace("%2F", "/", $parameter);
		$parameter = str_replace("%3f", "?", $parameter);
		$parameter = str_replace("%3F", "?", $parameter);
		$parameter = str_replace("%3d", "=", $parameter);
		$parameter = str_replace("%3D", "=", $parameter);
		$parameter = str_replace("%3a", ":", $parameter);
		$parameter = str_replace("%3A", ":", $parameter);
		$parameter = str_replace("%26", "&", $parameter);
		return $parameter;
	}

}

?>
