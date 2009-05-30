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
class XmlnukeFaq extends XmlnukeDocumentObject 
{
	protected $_faqs = array();
	protected $_title = "";
	
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function XmlnukeFaq($title)
	{	
		$this->_title = $title;
	}
	
	public function addFaqItem($title, $docobj)
	{
		if (is_null($docobj) || !($docobj instanceof IXmlnukeDocumentObject)) 
		{
			throw new XmlNukeObjectException(853, "Object is null or not is IXmlnukeDocumentObject. Found object type: " . get_class($docobj));
		}
		$this->_faqs[$title] = $docobj;
	}
		
	public function generateObject($current)
	{
		$node = XmlUtil::CreateChild($current, "faq", "");
		XmlUtil::AddAttribute($node, "title", $this->_title);
		foreach ($this->_faqs as $key=>$value) 
		{
			$nodefaq = XmlUtil::CreateChild($node, "item", "");
			XmlUtil::AddAttribute($nodefaq, "question", $key);
			$value->generateObject($nodefaq);
		}
	}
}

?>