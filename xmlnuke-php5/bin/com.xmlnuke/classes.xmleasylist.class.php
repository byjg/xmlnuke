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
 * @package com.xmlnuke
 * @subpackage xmlnukeobject
 */
class EasyListType
{
	const CHECKBOX = 1;
	const RADIOBOX = 2;
	const SELECTLIST = 3;
	const UNORDEREDLIST = 4;
	const SELECTIMAGELIST = 5;
}
	
/**
 * Class to represent all the most used list of itens in XML
 * You can create the object pass a $name $value collection and the list of object will be create
 * List of objects: CheckBox, RadioBox, SelectList and UnorderedList
 *
 * @package com.xmlnuke
 * @subpackage xmlnukeobject
 */
class XmlEasyList extends XmlnukeDocumentObject
{
	/**
	*@var string
	*/
	protected $_name;
	/**
	*@var string
	*/
	protected $_selected;
	/**
	*@var string
	*/
	protected $_caption;
	/**
	*@var array
	*/
	protected $_values;
	/**
	*@var EasyListType
	*/
	protected $_easyListType;
	/**
	*@var bool
	*/
	protected $_readOnly;
	/**
	*@var bool
	*/
	protected $_required;
	/**
	 * @var string
	 */
	protected $_readOnlyDeli = "[]";
	/**
	 * Used only in SelectList
	 *
	 * @var int
	 */
	protected $_size;
	
	protected $_notFoundImage = "";
	protected $_thumbnailSize = 100;
	protected $_noImage = "No Image";
	
	/**
	 * XmlEditList constructor
	 *
	 * @param EasyListType $listType
	 * @param string $name
	 * @param string $caption
	 * @param array $values
	 * @param string $selected
	 * @return XmlEasyList
	 */
	public function XmlEasyList( $listType, $name, $caption, $values, $selected = null)
	{
		parent::XmlnukeDocumentObject();
		$this->_name = $name;
		$this->_caption = $caption;
		$this->_values = $values;
		$this->_selected = $selected;
		$this->_easyListType = $listType;
		$this->_readOnly = false;
		$this->_size = 1;
	}
	
	public function setSize($size)
	{
		if ($this->_easyListType != EasyListType::SELECTLIST)
		{
			throw new Exception("Size is valid only in SelectList type");
		}
		else 
		{
			$this->_size = $size;
		}
	}

	public function setNotFoundImage($img)
	{
		if ($this->_easyListType != EasyListType::SELECTIMAGELIST)
		{
			throw new Exception("NotFoundImage is valid only in SelectImageList type");
		}
		else 
		{
			$this->_notFoundImage = $img;
		}
	}

	public function setThumbnailSize($size)
	{
		if ($this->_easyListType != EasyListType::SELECTIMAGELIST)
		{
			throw new Exception("ThumbnailSize is valid only in SelectImageList type");
		}
		else 
		{
			$this->_thumbnailSize = $size;
		}
	}

	public function setNoImage($text)
	{
		if ($this->_easyListType != EasyListType::SELECTIMAGELIST)
		{
			throw new Exception("NoImage is valid only in SelectImageList type");
		}
		else 
		{
			$this->_noImage = $text;
		}
	}

	/**
	 * config input as read only
	 *
	 * @param bool $value
	 */
	public function setReadOnly($value)
	{
		$this->_readOnly = $value;
	}
	
	/**
	 * config input as required
	 *
	 * @param bool $value
	 */
	public function setRequired($value)
	{
		$this->_required = $value;
	}
	
	public function setReadOnlyDelimeters($value)
	{
		if ((strlen($value) != 2) && (strlen($value) != 0))
		{
			throw new Exception("Read Only Delimiters must have two characters or is empty");
		}
		else 
		{
			$this->_readOnlyDeli = $value;
		}
	}


	/**
	 *  Contains specific instructions to generate all XML informations
	 *  This method is processed only one time
	 *  Usually is the last method processed
	 *
	 * @param DOMNode $current
	 */
	public function generateObject($current)
	{
		$nodeWorking = null;
		// Criando o objeto que conterá a lista
		switch ($this->_easyListType)
		{
			case EasyListType::CHECKBOX:
			{
				XmlUtil::CreateChild($current, "caption", $this->_caption);
				$nodeWorking = $current;
				$iHid = new XmlInputHidden("qty" . $this->_name, sizeof($this->_values));
				$iHid->generateObject($nodeWorking);
				break;
			}
			case EasyListType::RADIOBOX:
			{
				XmlUtil::CreateChild($current, "caption", $this->_caption);
				$nodeWorking = $current;
				break;
			}
			case EasyListType::SELECTLIST:
			case EasyListType::SELECTIMAGELIST:
			{
				if ($this->_readOnly)
				{
					if ($this->_easyListType == EasyListType::SELECTLIST)
					{
						$deliLeft = (strlen($this->_readOnlyDeli) != 0 ? $this->_readOnlyDeli[0] : "");
						$deliRight = (strlen($this->_readOnlyDeli) != 0 ? $this->_readOnlyDeli[1] : "");
									
						//XmlInputHidden $xih
						//XmlInputLabelField $xlf
						$xlf = new XmlInputLabelField($this->_caption, $deliLeft . $this->_values[$this->_selected] . $deliRight);
						$xih = new XmlInputHidden($this->_name, $this->_selected);
						$xlf->generateObject($current);
						$xih->generateObject($current);
					}
					elseif ($this->_easyListType == EasyListType::SELECTIMAGELIST)
					{
						$img = new XmlnukeImage($this->_values[$this->_selected]);
						$img->generateObject($current);
					}
					return;
				}
				else
				{
					$nodeWorking = XmlUtil::CreateChild($current, "select", "");
					XmlUtil::AddAttribute($nodeWorking, "caption", $this->_caption);
					XmlUtil::AddAttribute($nodeWorking, "name", $this->_name);
					if($this->_required)
					{
						XmlUtil::AddAttribute($nodeWorking , "required", "true");
					}
					if($this->_size > 1)
					{
						XmlUtil::AddAttribute($nodeWorking , "size", $this->_size);
					}
					
					if ($this->_easyListType == EasyListType::SELECTIMAGELIST)
					{
						XmlUtil::AddAttribute($nodeWorking , "imagelist", "true");
						XmlUtil::AddAttribute($nodeWorking , "thumbnailsize", $this->_thumbnailSize);
						XmlUtil::AddAttribute($nodeWorking , "notfoundimage", $this->_notFoundImage);
						XmlUtil::AddAttribute($nodeWorking , "noimage", $this->_noImage);
					}
				}
				break;
			}
			case EasyListType::UNORDEREDLIST:
			{
				XmlUtil::CreateChild($current, "b", $this->_caption);
				$nodeWorking = XmlUtil::CreateChild($current, "ul", "");
				break;
			}
		}
		
		$i = 0;
		
		foreach($this->_values as $key => $value)
		{
			switch ($this->_easyListType)
			{
				case EasyListType::CHECKBOX:
				{
//					XmlInputCheck $iCk
					$iCk = new XmlInputCheck($value, $this->_name . ($i++), $key);
					$iCk->setType(InputCheckType::CHECKBOX);
					$iCk->setChecked($key == $this->_selected);
					$iCk->setReadOnly($this->_readOnly);
					$iCk->generateObject($nodeWorking);
					break;
				}
				case EasyListType::RADIOBOX:
				{
//					XmlInputCheck $iCk
					$iCk = new XmlInputCheck($value, $this->_name, $key);
					$iCk->setType(InputCheckType::RADIOBOX);
					$iCk->setChecked($key == $this->_selected);
					$iCk->setReadOnly($this->_readOnly);
					$iCk->generateObject($nodeWorking);
					break;
				}
				case EasyListType::SELECTLIST:
				case EasyListType::SELECTIMAGELIST:
				{
					$node = XmlUtil::CreateChild($nodeWorking, "option", "");
					XmlUtil::AddAttribute($node, "value", $key);
					if ($key == $this->_selected)
					{
						XmlUtil::AddAttribute($node, "selected", "yes");
					}
					XmlUtil::AddTextNode($node, $value);
					break;
				}
				case EasyListType::UNORDEREDLIST:
				{
					XmlUtil::CreateChild($nodeWorking, "li", $value);
					break;
				}
			}
		}
		
	}

}


?>
