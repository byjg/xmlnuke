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

abstract class XmlTableCollectionBase extends XmlnukeCollection implements IXmlnukeDocumentObject
{
	protected $_NODE = "";
	protected $_genNode = null;

	protected $_style = "";
	public function setStyle($value)
	{
		$this->_style = $value;
	}
	public function getStyle()
	{
		return $this->_style;
	}
	
	protected $_id = "";
	public function setId($value)
	{
		$this->_id = $value;
	}
	public function getId()
	{
		return $this->_id;
	}
	
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		$this->_genNode = XmlUtil::CreateChild($current, $this->_NODE, "");
		XmlUtil::AddAttribute($this->_genNode, "id", $this->getId());
		XmlUtil::AddAttribute($this->_genNode, "style", $this->getStyle());
		parent::generatePage($this->_genNode);
	}
}

/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlTableCollection extends XmlTableCollectionBase
{
	public function __construct()
	{
		$this->_NODE = "table";
	}
}

class XmlTableRowCollection extends XmlTableCollectionBase
{
	public function __construct()
	{
		$this->_NODE = "tr";
	}
}


class XmlTableColumnCollection extends XmlTableCollectionBase
{
	protected $_colspan = "";
	public function setColspan($value)
	{
		$this->_colspan = $value;
	}
	public function getColspan()
	{
		return $this->_colspan;
	}
	
	protected $_rowspan = "";
	public function setRowspan($value)
	{
		$this->_rowspan = $value;
	}
	public function getRowspan()
	{
		return $this->_rowspan;
	}
	
	public function __construct()
	{
		$this->_NODE = "td";
	}
	
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		parent::generateObject($current);
		XmlUtil::AddAttribute($this->_genNode, "colspan", $this->getColspan());
		XmlUtil::AddAttribute($this->_genNode, "rowspan", $this->getRowspan());
	}
	
}
?>