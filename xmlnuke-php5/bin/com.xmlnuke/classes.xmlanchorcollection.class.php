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
class XmlAnchorCollection extends XmlnukeCollection implements IXmlnukeDocumentObject 
{
	/**
	*@var string
	*/
	protected $_src;
	/**
	*@var string
	*/
	protected $_target;

	/**
	*@desc XmlAnchorCollection constructor
	*@param string $hrefSrc
	*@param string $target
	*/
	public function XmlAnchorCollection($hrefSrc, $target = '')
	{
		parent::XmlnukeCollection();
		$this->_src = $hrefSrc;
		$this->_target = $target;
	}

	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		
		$nodeWorking = XmlUtil::CreateChild($current, "a", "");
		$link = str_replace("&amp;amp;", "&amp;", str_replace("&", "&amp;", $this->_src));
		XmlUtil::AddAttribute($nodeWorking, "href", $link);
		if ($this->_target != "")
		{
			XmlUtil::AddAttribute($nodeWorking, "target", $this->_target);
		}
		$this->generatePage($nodeWorking);		
	}
}

?>
