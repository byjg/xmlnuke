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
class XmlnukeCode extends XmlnukeDocumentObject 
{

	/**
	*@var string
	*/
	private $_text;
	/**
	*@var string
	*/
	private $_title;

	/**
	 * Enter description here...
	 *
	 * @param string $text
	 * @param string $title
	 * @return XmlnukeCode
	 */
	public function XmlnukeCode($text = "", $title = "")
	{
		if ($title == "")
		{
			$this->_title = $text;
		}
		else 
		{
			$this->_text = $text;
			$this->_title = $title;
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $text
	 */
	public function AddText($text)
	{
		$this->_text .= $text;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $text
	 */
	public function AddTextLine($text)
	{
		$this->_text .= $text . "\r\n";
	}

	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		$node = XmlUtil::CreateChild($current, "code", $this->_text);
		if ($this->_title != "")
		{
			XmlUtil::AddAttribute($node, "information", $this->_title);
		}
	}
}
?>