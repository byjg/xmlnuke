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

use ByJG\Util\XmlUtil;

/**
 * @package xmlnuke
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
	 *
	 * @param string $context
	 * @param UIAlert $uialert
	 * @param string $title
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
		$param = new ParamProcessor();
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
		$node = XmlUtil::createChild($current, "uialert", "");
		XmlUtil::addAttribute($node, "type", $this->_uialert);
		XmlUtil::addAttribute($node, "name", $this->_name);
		XmlUtil::addAttribute($node, "title", $this->_title);
		if ($this->_autoHide > 0)
		{
			XmlUtil::addAttribute($node, "autohide", $this->_autoHide);
		}
		if ($this->_openAction)
		{
			XmlUtil::addAttribute($node, "openaction", $this->_openAction);
			XmlUtil::addAttribute($node, "openactiontext", $this->_openActionText);
		}
		if ($this->_width > 0)
		{
			XmlUtil::addAttribute($node, "width", $this->_width);
		}
		if ($this->_height > 0)
		{
			XmlUtil::addAttribute($node, "height", $this->_height);
		}
		foreach ($this->_buttons as $key=>$value)
		{
			$btn = XmlUtil::createChild($node, "button", $value);
			XmlUtil::addAttribute($btn, "text", $key);
		}
		$body = XmlUtil::createChild($node, "body");
		parent::generatePage($body);
	}
}

?>