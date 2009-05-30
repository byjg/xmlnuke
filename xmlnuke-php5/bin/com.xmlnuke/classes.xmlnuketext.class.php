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
class XmlnukeText extends XmlnukeDocumentObject 
{

	/**
	*@var string
	*/
	private $_text;
	/**
	*@var bool
	*/
	private $_bold;
	/**
	*@var bool
	*/
	private $_italic;
	/**
	*@var bool
	*/
	private $_underline;
	/**
	*@var bool
	*/
	private $_breakline;
	
	/**
	*@desc XmlnukeText constructor
	*@param string $text
	*@param bool $bold
	*@param bool $italic
	*@param bool $underline
	*@param bool $breakline
	*/
	public function XmlnukeText($text, $bold = false, $italic = false, $underline = false, $breakline = false)
	{
		$this->_text = $text;
		$this->_bold = $bold;
		$this->_italic = $italic;
		$this->_underline = $underline;
		$this->_breakline = $breakline;
	}

	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		$node = $current;
		if ($this->_bold)
		{
			$node = XmlUtil::CreateChild($node, "b", "");
		}
		if ($this->_italic)
		{
			$node = XmlUtil::CreateChild($node, "i", "");
		}
		if ($this->_underline)
		{
			$node = XmlUtil::CreateChild($node, "u", "");
		}

		XmlUtil::AddTextNode($node, $this->_text);
		
		if ($this->_breakline)
		{
			XmlUtil::CreateChild($node, "br", "");
		}

	}
}
?>