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
class XmlInputFile extends XmlnukeDocumentObject
{
	/**
	*@var string
	*/
	protected $_name;
	/**
	*@var string
	*/
	protected $_caption;
	/**
	*@var string
	*/
	protected $_value;
	
	/**
	*@desc XmlInputFile constructor
	*@param string $name
	*/
	public function XmlInputFile($caption, $name)
	{ 
		$this->_caption = $caption;
		$this->_name = $name;
	}
	
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		XmlUtil::AddAttribute($current, "ENCTYPE", 'multipart/form-data');
		$nodeWorking = XmlUtil::CreateChild($current, "file", "");
		XmlUtil::AddAttribute($nodeWorking, "caption", $this->_caption);
		XmlUtil::AddAttribute($nodeWorking, "name", $this->_name);
	}
}
?>