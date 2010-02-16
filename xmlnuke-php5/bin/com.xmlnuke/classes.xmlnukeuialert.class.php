<?php
/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  Acknowledgments to: Roan Brasil Monteiro
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

class UIAlert
{
	const Dialog = "dialog";
	const ModalDialog = "modaldialog";
	const BoxInfo = "boxinfo";
	const BoxAlert = "boxalert";
}

class UIAlertOpenAction
{
	const URL = "url";
	const Image = "image";
	const Button = "button";
	const NoAutoOpen = "noautoopen";
	const None = "";
}


/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlnukeUIAlert extends XmlnukeCollection implements IXmlnukeDocumentObject 
{
	/**
	 * @var Context
	 */
	protected $_context;
	
	protected $_uialert = "";
	protected $_title = "";
	protected $_name = "";
	protected $_openAction = null;
	protected $_openActionText = null;
	protected $_autoHide = 0;
	protected $_width = 0;
	protected $_height = 0;
	
	protected $_buttons = array();
	
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function  __construct($context, $uialert, $title = "")
	{
		$this->_context = $context;
		$this->_uialert = $uialert;
		if ($title == "")
		{
			$this->_title = "Message";
		}
		else
		{
			$this->_title = $title;
		}
		$this->_name = "uialert_" . rand(1000, 9999);
	}
	
	public function getName()
	{
		return $this->_name;
	}
	public function setName($value)
	{
		$this->_name = $value;
	}
	
	public function getAutoHide()
	{
		return $this->_autoHide;
	}
	public function setAutoHide($value)
	{
		$this->_autoHide = $value;
	}
	
	public function setDimensions($width, $height = 0)
	{
		$this->_width = $width;
		$this->_height = $height;
	}
	
	public function setUIAlertType($type)
	{
		$this->_uialert = $type;
	}
	
	public function addCloseButton($text)
	{
		$this->_buttons[$text] = "$(this).dialog('close');";
	}
	
	public function addRedirectButton($text, $url)
	{
		$param = new ParamProcessor($this->_context);
		$urlXml = $param->GetFullLink($url);
		$this->_buttons[$text] = "window.location='$urlXml';";
	}
	
	public function addCustomButton($text, $javascript)
	{
		$this->_buttons[$text] = $javascript;
	}
	
	public function setOpenAction($openaction, $text)
	{
		if ($openaction != UIAlertOpenAction::None)
		{
			$this->_openAction = $openaction;
			$this->_openActionText = $text;
		}
		else
		{
			$this->_openAction = false;
		}
	}
	
	public function generateObject($current)
	{
		$node = XmlUtil::CreateChild($current, "uialert", "");
		XmlUtil::AddAttribute($node, "type", $this->_uialert);
		XmlUtil::AddAttribute($node, "name", $this->_name);
		XmlUtil::AddAttribute($node, "title", $this->_title);
		if ($this->_autoHide > 0)
		{
			XmlUtil::AddAttribute($node, "autohide", $this->_autoHide);
		}
		if ($this->_openAction)
		{
			XmlUtil::AddAttribute($node, "openaction", $this->_openAction);
			XmlUtil::AddAttribute($node, "openactiontext", $this->_openActionText);
		}
		if ($this->_width > 0)
		{
			XmlUtil::AddAttribute($node, "width", $this->_width);
		}
		if ($this->_height > 0)
		{
			XmlUtil::AddAttribute($node, "height", $this->_height);
		}
		foreach ($this->_buttons as $key=>$value)
		{
			$btn = XmlUtil::CreateChild($node, "button", $value);
			XmlUtil::AddAttribute($btn, "text", $key);
		}
		$body = XmlUtil::CreateChild($node, "body");
		parent::generatePage($body);
	}
}

?>