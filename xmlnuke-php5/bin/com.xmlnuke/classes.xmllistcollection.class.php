<?php
/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  Acknowledgments to: Thiago Bellandi
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

class XmlListType
{
	const UnorderedList = 1;
	const OrderedList = 2;
}

/**
 * Xml List Collection
 *
 * @package com.xmlnuke
 * @subpackage xmlnukeobject
 */
class XmlListCollection extends XmlnukeCollection implements IXmlnukeDocumentObject 
{
	/**
	 * XmlListType
	 *
	 * @var XmlListType
	 */
	protected $_type;
	
	/**
	 * Caption
	 *
	 * @var String
	 */
	protected $_caption;
	
	/**
	 * Name
	 *
	 * @var String
	 */
	protected $_name;
	
	/**
	 * Default Constructor
	 *
	 * @param XmlListType $type
	 * @param String $caption
	 * @param String $name
	 * @return XmlListCollection
	 */
	public function XmlListCollection($type, $caption = "", $name = "")
	{	
		$this->_type = $type;
		$this->_caption = $caption;
		$this->_name = $name;
	}
	
	/**
	 * Generate page, processing yours childs.
	 *
	 * @param DOMNode $current
	 */
	protected function generatePage($current)
	{
		if (!is_null($this->_items))
		{
			foreach( $this->_items as $item )
			{
				if (($this->_type == XmlListType::UnorderedList) || ($this->_type == XmlListType::OrderedList))
				{
					$node = XmlUtil::CreateChild($current, "li", "");
				}
				$item->generateObject($node);
			}
		}
	}
	
	/**
	 * Generate page, processing yours childs.
	 *
	 * @param DOMNode $current
	 */
	public function generateObject($current)
	{
		if ($this->_caption != "")
		{
			$text = new XmlnukeText($this->_caption, true);
			$text->generateObject($current);
		}
		if ($this->_type == XmlListType::UnorderedList)
		{
			$node = XmlUtil::CreateChild($current, "ul", "");
		}
		elseif ($this->_type == XmlListType::OrderedList)
		{
			$node = XmlUtil::CreateChild($current, "ol", "");
		}
		if ($this->_name != "")
		{
			XmlUtil::AddAttribute($node, "name", $this->_name);
		}
		$this->generatePage($node);
	}
}

?>