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
/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlContainerCollection extends XmlnukeCollection implements IXmlnukeDocumentObject 
{
	protected $_class = "";
	
	protected $_align = "";

	protected $_style = "";
	
	protected $_id = "";

	protected $_timeOut = 0;
	
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function XmlContainerCollection($id = "")
	{	
		if ($id != "")
		{
			$this->_id = $id;
		}
		else
		{
			$this->_id = "div" . (rand(0, 9000)+1000);
		}
	}
	
	public function setClass($class)
	{
		$this->_class = $class;
	}
	public function getClass()
	{
		return $this->_class;
	}
	public function setAlign($align)
	{
		$this->_align = $align;
	}
	public function getAlign()
	{
		return $this->_align;
	}
	public function setStyle($style)
	{
		$this->_style = $style;
	}
	public function getStyle()
	{
		return $this->_style;
	}	
	public function setId($id)
	{
		$this->_id = $id;
	}
	public function getId()
	{
		return $this->_id;
	}
	public function setHideAfterTime($milisecs)
	{
		$this->_timeOut = $milisecs;
	}
	public function getHideAfterTime()
	{
		return $this->_timeOut;
	}
	
	public function generateObject($current)
	{
		$node = XmlUtil::CreateChild($current, "container", "");
		if ($this->_class != "")
		{
			XmlUtil::AddAttribute($node, "class", $this->getClass());
		}
		if ($this->_align != "")
		{
			XmlUtil::AddAttribute($node, "align", $this->getAlign());
		}
		if ($this->_style != "")
		{
			XmlUtil::AddAttribute($node, "style", $this->getStyle());
		}
		if ($this->_id != "")
		{
			XmlUtil::AddAttribute($node, "id", $this->getId());
		}
		if ($this->_timeOut > 0)
		{
			XmlUtil::AddAttribute($node, "timeout", $this->getHideAfterTime());
		}
		parent::generatePage($node);
	}
}

?>