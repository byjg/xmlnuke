<?php
/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  Acknowledgments to: Roan Brasil Monteiro
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
class XmlInputGroup extends XmlnukeCollection implements IXmlnukeDocumentObject 
{
	/**
	 * @var string
	 */
	protected $_name;
	/**
	 * @var bool
	 */
	protected $_canhide = false;
	/**
	 * @var bool
	 */
	protected $_breakline = false;
	/**
	 * @var string
	 */
	protected $_caption;
	/**
	 * @var Context
	 */
	protected $_context;
	/**
	 * @var bool
	 */
	protected $_visible = true;
	
	
	/**
	 * Enter description here...
	 *
	 * @param Context $context
	 * @param string $name
	 * @param bool $breakline
	 * @param bool $canhide
	 * @param string $caption
	 * @return XmlInputGroup
	 */
	public function XmlInputGroup($context, $name = "", $breakline = false, $canhide = false, $caption="")
	{
		if (!($context instanceof Context))
		{
			throw new Exception("Class XmlInputGroup must have a value Xmlnuke Context");
		}
		$this->_context = $context;
		if ($name == "")
			$this->_name = "ING" . $this->_context->getRandomNumber(100000); 
		else
			$this->_name = $name;
		$this->_canhide = $canhide;
		$this->_breakline = $breakline;
		$this->_caption = $caption;
	}
	
	/**
	 * @param bool $value
	 */
	public function setVisible($value)
	{
		$this->_visible = $value;
	}
	
	public function generateObject($current)
	{
		$node = XmlUtil::CreateChild($current, "inputgroup", "");
		XmlUtil::AddAttribute($node, "name", $this->_name);
		if ($this->_caption)
		{
			XmlUtil::AddAttribute($node, "caption", $this->_caption);
		}
		if ($this->_canhide)
		{
			XmlUtil::AddAttribute($node, "canhide", "true");
		}
		if ($this->_breakline)
		{
			XmlUtil::AddAttribute($node, "breakline", "true");
		}
		if (!$this->_visible)
		{
			XmlUtil::AddAttribute($node, "visible", "false");
		}
		parent::generatePage($node);
	}
}

?>
