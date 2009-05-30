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
class XmlInputMemo extends XmlnukeDocumentObject
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
	*@var int
	*/
	protected $_cols;
	/**
	*@var int
	*/
	protected $_rows;
	/**
	*@var string
	*/
	protected $_wrap;
	/**
	*@var bool
	*/
	protected $_readonly;
	/**
	 *@var int
	 */
	protected $_maxLength;
	/**
	 * @var bool
	 */
	protected $_visualEditor = false;
	/**
	 * @var bool
	 */
	protected $_visualEditorBaseHref = "";
	
	/**
	*@desc XmlInputMemo constructor
	*@param 
	*/	
	public function XmlInputMemo($caption, $name, $value)
	{
		parent::XmlnukeDocumentObject();
		$this->_name = $name;
		$this->_value = $value;
		$this->_caption = $caption;
		$this->_cols = 50;
		$this->_rows = 10;
		$this->_maxLength = 0;
		$this->_wrap = "SOFT";  // "OFF"
		$this->_readonly = false;
		$this->_visualEditor = false;
	}

	/**
	*@desc set memo size
	*@param int $cols
	*@param int $rows
	*@return void
	*/
	public function setSize($cols, $rows)
	{
		$this->_cols = $cols;
		$this->_rows = $rows;
	}

	/**
	*@desc 
	*@param string $wrap
	*@return void
	*/
	public function setWrap($wrap)
	{
		if (($wrap != "SOFT") && ($wrap != "OFF"))
		{
			throw new XmlNukeObjectException(851, "InputMemo wrap values must be SOFT or OFF");
		}
		$this->_wrap = $wrap;
	}

	/**
	*@desc 
	*@param bool $value
	*@return void
	*/
	public function setReadOnly($value)
	{
		$this->_readonly = $value;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param int $value
	 */
	public function setMaxLength($value)
	{
		$this->_maxLength = $value;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param bool $value
	 */
	public function setVisualEditor($value)
	{
		$this->_visualEditor = $value;
	}

	/**
	 * Defines the Base Href to locate images and other objects
	 * 
	 * @param string $value
	 */
	public function setVisualEditorBaseHref($value)
	{
		$this->_visualEditorBaseHref = $value;
	}
	
	/**
	*@desc Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		if ($this->_readonly)
		{
			// XmlInputLabelField ic
			$ic = new XmlInputLabelField($this->_caption, $this->_value);
			$ic->generateObject($current);

			// XmlInputHidden $ih
			$ih = new XmlInputHidden($this->_name, $this->_value);
			$ih->generateObject($current);
		}
		else
		{
			$nodeWorking = XmlUtil::CreateChild($current, "memo", "");
			XmlUtil::AddAttribute($nodeWorking, "caption", $this->_caption);
			XmlUtil::AddAttribute($nodeWorking, "name", $this->_name);
			XmlUtil::AddAttribute($nodeWorking, "cols", $this->_cols);
			XmlUtil::AddAttribute($nodeWorking, "rows", $this->_rows);
			XmlUtil::AddAttribute($nodeWorking, "wrap", $this->_wrap);
			if ($this->_visualEditor) 
			{
				XmlUtil::AddAttribute($nodeWorking, "visualedit", "true");
				XmlUtil::AddAttribute($nodeWorking, "visualeditbasehref", $this->_visualEditorBaseHref);
			}
			elseif ($this->_maxLength > 0) 
			{
				XmlUtil::AddAttribute($nodeWorking, "maxlength", $this->_maxLength);
			}
			XmlUtil::AddTextNode($nodeWorking, $this->_value);
		}
	}

}

?>
