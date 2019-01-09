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

use ByJG\Util\XmlUtil;

/**
 * @package xmlnuke
 */
class XmlnukeFaq extends XmlnukeDocumentObject 
{
	protected $_faqs = array();
	protected $_title = "";
	
	/**
	*@desc Generate page, processing yours childs.
	*@param string $title
	*@return void
	*/
	public function __construct($title)
	{	
		$this->_title = $title;
	}
	
	public function addFaqItem($title, $docobj)
	{
		if (is_null($docobj) || !($docobj instanceof IXmlnukeDocumentObject)) 
		{
			throw new InvalidArgumentException("Object is null or not is IXmlnukeDocumentObject. Found object type: " . get_class($docobj), 853);
		}
		$this->_faqs[$title] = $docobj;
	}
		
	public function generateObject($current)
	{
		$node = XmlUtil::createChild($current, "faq", "");
		XmlUtil::addAttribute($node, "title", $this->_title);
		foreach ($this->_faqs as $key=>$value) 
		{
			$nodefaq = XmlUtil::createChild($node, "item", "");
			XmlUtil::addAttribute($nodefaq, "question", $key);
			$value->generateObject($nodefaq);
		}
	}
}

?>