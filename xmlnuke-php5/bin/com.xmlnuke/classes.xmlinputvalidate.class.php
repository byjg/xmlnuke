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
class XmlInputValidate extends XmlnukeDocumentObject 
{
	/**
	*@var bool
	*/
	private $_required;
	/**
	*@var INPUTTYPE
	*/
	private $_inputtype;
	/**
	*@var string
	*/
	private $_minvalue;
	/**
	*@var string
	*/
	private $_maxvalue;
	/**
	*@var string
	*/
	private $_description;
	/**
	*@var string
	*/
	private $_customjs;
	
	/**
	*@desc XmlnukeText constructor
	*/
	public function XmlInputValidate()
	{
		$this->_required = false;
		$this->_inputtype = INPUTTYPE::TEXT;
		$this->_minvalue = "";
		$this->_maxvalue = "";
		$this->_description = "";
		$this->_customjs = "";
	}
	
	/**
	*@desc set input as required in JavaScript validation
	*@param bool $required
	*@return void
	*/
	public function setRequired($required)
	{
		$this->_required = $required;
	}
	
	/**
	*@desc set range min and max values the input field to JavaScript validation
	*@param string $minvalue
	*@param string $maxvalue
	*@return void
	*/
	public function setRange($minvalue, $maxvalue)
	{
		$this->_minvalue = $minvalue;
		$this->_maxvalue = $maxvalue;
	}

	/**
	*@desc set input description
	*@param string $description
	*@return void
	*/
	public function setDescription( $description)
	{
		$this->_description = $description;
	}

	/**
	*@desc set input custom JavaScript
	*@param string $customjs
	*@return void
	*/
	public function setCustomJS($customjs)
	{
		$this->_customjs = $customjs;
	}
	
	/**
	*@desc set input datatype
	*@param INPUTTYPE $itype
	*@return void
	*/
	public function setDataType($itype)
	{
		$this->_inputtype = $itype;
	}

	/**
	 * Get the DataType
	 * @return INPUTTYPE
	 */
	public function getDataType()
	{
		return $this->_inputtype;
	}
	
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		XmlUtil::AddAttribute($current, "required", ($this->_required ? "true" : "false" ));
		XmlUtil::AddAttribute($current, "type", $this->_inputtype);
		if ($this->_minvalue != "")
		{
			XmlUtil::AddAttribute($current, "minvalue", $this->_minvalue);
		}
		if ($this->_maxvalue != "")
		{
			XmlUtil::AddAttribute($current, "maxvalue", $this->_maxvalue);
		}
		if ($this->_description != "")
		{
			XmlUtil::AddAttribute($current, "description", $this->_description);
		}
		if ($this->_customjs != "")
		{
			XmlUtil::AddAttribute($current, "customjs", $this->_customjs);
		}
	}
}
?>