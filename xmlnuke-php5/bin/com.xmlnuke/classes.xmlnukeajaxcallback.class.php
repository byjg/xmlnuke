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
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlnukeAjaxCallback extends XmlnukeDocumentObject 
{
	protected $_context;
	
	protected $_class = "";
	
	protected $_style = "";
	
	protected $_id = "";
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function XmlnukeAjaxCallback($context)
	{	
		$this->_context = $context;
	}
	
	public function setClass($class)
	{
		$this->_class = $class;
	}
	public function getClass()
	{
		return $this->_class;
	}
	public function setStyle($style)
	{
		$this->_style = $style;
	}
	public function getStyle()
	{
		if ($this->_style == "")
		{
			$this->setCustomStyle();
		}
		return $this->_style;
	}	
	public function setId($id)
	{
		$this->_id = $id;
	}
	public function getId()
	{
		if ($this->_id == "")
		{
			$this->_id = "ACB_" . $this->_context->getRandomNumber(10000);
		}
		return $this->_id;
	}
	
	public function setCustomStyle($width=400, $border=true)
	{
		$halfWidth = intval($width/2);
		$borderStr = ($border ? "border: 1px dashed gray;" : "");
		$this->_style = $borderStr . "display: none; width: ".$width."px; position: relative; left: 50%; margin-left: -".$halfWidth."px;";
	}
	
	public function generateObject($current)
	{
		$node = XmlUtil::CreateChild($current, "ajaxcallback", "");
		if ($this->_class != "")
		{
			XmlUtil::AddAttribute($node, "class", $this->getClass());
		}
		XmlUtil::AddAttribute($node, "style", $this->getStyle());
		XmlUtil::AddAttribute($node, "id", $this->getId());
	}
}

?>