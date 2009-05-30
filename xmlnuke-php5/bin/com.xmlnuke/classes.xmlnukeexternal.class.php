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
class XmlnukeExternal extends XmlnukeDocumentObject 
{

	/**
	*@var string
	*/
	private $_name;
	/**
	*@var string
	*/
	private $_src;
	/**
	 * @var string
	 */
	private $_width = "100%";
	/**
	 * @var string
	 */
	private $_height = "100%";


	/**
	 * Enter description here...
	 *
	 * @param string $src
	 * @param string $name
	 * @param string $width
	 * @param string $height
	 */
	public function XmlnukeExternal($src, $name = "", $width = "100%", $height = "100%")
	{
		if ($name != "")
		{
			$this->_name = $name;
		}
		$this->_src = $src;
		$this->_width = $width;
		$this->_height = $height;
	}
	
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		$node = XmlUtil::CreateChild($current, "external");
		if ($this->_name != "")
		{
			XmlUtil::AddAttribute($node, "name", $this->_name);
		}
		XmlUtil::AddAttribute($node, "src", $this->_src);
		XmlUtil::AddAttribute($node, "width", $this->_width);
		XmlUtil::AddAttribute($node, "height", $this->_height);
	}
}
?>
