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
class InputCheckType
{
	const CHECKBOX = 1; 
	const RADIOBOX = 2;
}
/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlInputCheck extends XmlnukeDocumentObject
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
	*@var bool
	*/
	protected $_checked;
	/**
	*@var bool
	*/
	protected $_readonly;
	/**
	*@var InputCheckType
	*/
	protected $_inputCheckType;
	
	/**
	*@desc XmlInputCheck constructor
	*@param string $caption
	*@param string $name
	*@param string $value
	*/
	public function XmlInputCheck($caption, $name, $value)
	{
		parent::XmlnukeDocumentObject();
		$this->_name = $name;
		$this->_value = $value;
		$this->_caption = $caption;
		$this->_checked = false;
		$this->_inputCheckType = InputCheckType::CHECKBOX;
		$this->_readonly = false;
	}

	/**
	*@desc 
	*@param bool $caption
	*@return void
	*/
	public function setChecked($value)
	{
		$this->_checked = $value;
	}

	/**
	*@desc config input type
	*@param InputCheckType $inputCheckType
	*@return void
	*/
	public function setType($inputCheckType)
	{
		$this->_inputCheckType = $inputCheckType;
	}
	
	/**
	*@desc config the input as Read Onlye
	*@param bool $value
	*@return void
	*/
	public function setReadOnly($value)
	{
		$this->_readonly = $value;
	}

	/**
	*@desc Contains specific instructions to generate all XML informations-> This method is processed only one time-> Usually is the last method processed->
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		if ($this->_readonly)
		{
//			XmlInputLabelField $ic;
			if ($this->_checked)
			{
//				XmlInputHidden $ih
				$ih = new XmlInputHidden($this->_name, $this->_value);
				$ic = new XmlInputLabelField($this->_caption, "[X]");
				$ih->generateObject($current);
			}
			else
			{
				$ic = new XmlInputLabelField($this->_caption, "[ ]");
			}
			$ic->generateObject($current);
		}
		else
		{
//			XmlNode $nodeWorking;
			if ($this->_inputCheckType == InputCheckType::CHECKBOX)
			{
				$nodeWorking = XmlUtil::CreateChild($current, "checkbox", "");
			}
			else
			{
				$nodeWorking = XmlUtil::CreateChild($current, "radiobox", "");
			}
			XmlUtil::AddAttribute($nodeWorking, "caption", $this->_caption);
			XmlUtil::AddAttribute($nodeWorking, "name", $this->_name);
			XmlUtil::AddAttribute($nodeWorking, "value", $this->_value);
			if ($this->_checked)
			{
				XmlUtil::AddAttribute($nodeWorking, "selected", "yes");
			}
		}
	}
}
?>