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

class SortableListItemState
{
	const Normal="";
	const Highligth="highlight";
	const Disabled="disabled";
}

/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlInputSortableList extends XmlnukeDocumentObject 
{
	protected $_items = array();
	protected $_name;
	protected $_caption;
	
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function XmlInputSortableList($caption, $name)
	{	
		$this->_name = $name;
		$this->_caption = $caption;
	}
	
	/**
	 * 
	 * @param string $key
	 * @param IXmlnukeDocumentObject $docobj
	 * @param SortableListItemState $state
	 * @return unknown_type
	 */
	public function addSortableItem($key, $docobj, $state = "")
	{
		if (is_null($docobj) || !($docobj instanceof IXmlnukeDocumentObject)) 
		{
			throw new XmlNukeObjectException(853, "Object is null or not is IXmlnukeDocumentObject. Found object type: " . get_class($docobj));
		}
		$this->_items[$key . "|" . $state] = $docobj;
	}
		
	public function generateObject($current)
	{
		$editForm = $current;
		while (($editForm != null) && ($editForm->tagName != "editform")) 
		{
			$editForm = $editForm->parentNode;
		} 
		
		if ($editForm == null)
		{
			throw new XMLNukeException(0, "XmlInputSortableList must be inside a XmlFormCollection");
		}

		$node = XmlUtil::CreateChild($current, "sortablelist", "");
		XmlUtil::AddAttribute($node, "name", $this->_name);
		XmlUtil::AddAttribute($node, "caption", $this->_caption);
		foreach ($this->_items as $key=>$value) 
		{
			$info = explode("|", $key);
			$nodeitem = XmlUtil::CreateChild($node, "item", "");
			XmlUtil::AddAttribute($nodeitem, "key", $info[0]);
			XmlUtil::AddAttribute($nodeitem, "state", $info[1]);
			$value->generateObject($nodeitem);
		}
	}
}

?>