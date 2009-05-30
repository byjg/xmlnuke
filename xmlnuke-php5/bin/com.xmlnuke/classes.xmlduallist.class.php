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
*Dual list Button Types
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class DualListButtonType
{
	const Button = 1;
	const Image = 2;
	const None = 3;
}

/**
*Dual list Buttons
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class DualListButton
{
	/**
	 * Button inside text
	 *
	 * @var string
	 */
	public $text;
	/**
	 * Button type
	 *
	 * @var DualListButtonType
	 */
	public $type;
	/**
	 * If image button type, needle a url from image
	 *
	 * @var string
	 */
	public $href;
	
	/**
	 * Enter description here...
	 *
	 * @param string $text
	 * @param DualListButtonType $type
	 * @param string $imgurl
	 */
	public function __construct($text, $type, $imgurl = "")
	{
		$this->text = $text;
		$this->type = $type;
		$this->href = $imgurl;
	}
}

/**
*Edit list class
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlDualList extends XmlnukeDocumentObject
{
	/**
	*@var string
	*/
	protected $_name;
	/**
	*@var DualListButton
	*/
	protected $_buttonOneLeft;
	/**
	*@var DualListButton
	*/
	protected $_buttonAllLeft;
	/**
	*@var DualListButton
	*/
	protected $_buttonOneRight;
	/**
	*@var DualListButton
	*/
	protected $_buttonAllRight;
	/**
	*@var string
	*/
	protected $_listLeftName;
	/**
	*@var string
	*/
	protected $_listRightName;
	/**
	*@var IIterator
	*/
	protected $_listLeftDataSource;
	/**
	*@var IIterator
	*/
	protected $_listRightDataSource;
	/**
	*@var string
	*/
	protected $_listLeftCaption;
	/**
	*@var string
	*/
	protected $_listRightCaption;
	/**
	*@var int
	*/
	protected $_listLeftSize = 5;
	/**
	*@var int
	*/
	protected $_listRightSize = 5;
	/**
	*@var string
	*/
	protected $_dataTableFieldId;
	/**
	*@var string
	*/
	protected $_dataTableFieldText;

	/**
	* XmlEditList constructor
	* 
	* @param Context $context
	* @param string $name
	* @param string $captionLeft
	* @param string $captionRight
	*/
	public function XmlDualList($context, $name, $captionLeft = "", $captionRight = "")
	{
		$this->_name = $name;
		$this->_context = $context;
		$this->_listLeftName = "DL_LEFT_" . $this->_context->getRandomNumber(100000);
		$this->_listRightName = "DL_RIGHT_" . $this->_context->getRandomNumber(100000);
		$this->_listLeftCaption = $captionLeft;
		$this->_listRightCaption = $captionRight;
	}
	
	/**
	 * Config DataSource to Dual List
	 *
	 * @param IIterator $listLeft
	 * @param IIterator $listRight
	 */
	public function setDataSource($listLeft, $listRight = null)
	{
		$this->_listLeftDataSource = $listLeft;
		$this->_listRightDataSource = $listRight;
	}
	
	/**
	 * Config Database table fields of datasource to Dual List
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function setDataSourceFieldName($id, $text)
	{
		$this->_dataTableFieldId = $id;
		$this->_dataTableFieldText = $text;
	}
	
	/**
	 * Config move one element from a list to left Button
	 *
	 * @param DualListButton $button
	 */
	public function setButtonOneLeft($button)
	{
		$this->_buttonOneLeft = $button;
	}
	
	/**
	 * Create all default buttons.
	 *
	 */
	public function createDefaultButtons()
	{
		$this->setButtonOneLeft(new DualListButton("&#60;--", DualListButtonType::Button));
		$this->setButtonAllLeft(new DualListButton("&#60;&#60;&#60;", DualListButtonType::Button));
		$this->setButtonOneRight(new DualListButton("--&#62;", DualListButtonType::Button));
		$this->setButtonAllRight(new DualListButton("&#62;&#62;&#62;", DualListButtonType::Button));
	}
	
	/**
	 * Config move all elements from a list to left Button
	 *
	 * @param DualListButton $button
	 */
	public function setButtonAllLeft($button)
	{
		$this->_buttonAllLeft = $button;
	}
	
	/**
	 * Config move one element from a list to right Button
	 *
	 * @param DualListButton $button
	 */
	public function setButtonOneRight($button)
	{
		$this->_buttonOneRight = $button;
	}
	
	/**
	 * Config move all elements from a list to right Button
	 *
	 * @param DualListButton $button
	 */
	public function setButtonAllRight($button)
	{
		$this->_buttonAllRight = $button;
	}
	
	/**
	 * Set Dual Lists names
	 *
	 * @param string $leftName
	 * @param string $rightName
	 */
	public function setDualListName($leftName, $rightName)
	{
		$this->_listLeftName = $leftName;
		$this->_listRightName = $rightName;
	}
	
	/**
	 * Set Dual Lists names
	 *
	 * @param int $leftSize
	 * @param int $rightSize
	 */
	public function setDualListSize($leftSize = 5, $rightSize = 5)
	{
		$this->_listLeftSize = $leftSize;
		$this->_listRightSize = $rightSize;
	}
		
	/**
	*@desc Generate $page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		$submitFunction = "buildDualListField(this, '$this->_listRightName', '$this->_name');";
		
		$editForm = $current;
		while (($editForm != null) && ($editForm->tagName != "editform")) 
		{
			$editForm = $editForm->parentNode;
		} 
		
		if ($editForm != null)
		{
			if ($editForm->hasAttribute("customsubmit"))
			{
				$editForm->getAttributeNode("customsubmit")->value = $editForm->getAttributeNode("customsubmit")->value . "  &amp;amp;&amp;amp;  " . $submitFunction;
			}
			else 
			{
				XmlUtil::AddAttribute($editForm, "customsubmit", $submitFunction);
			}
		}
		else
		{
			throw new XMLNukeException(0, "XMLDualList must be inside a XmlFormCollection");
		}
		
		$nodeWorking = XmlUtil::CreateChild($current, "duallist", "");
		
		if (is_a($this->_buttonAllRight, "DualListButton") && $this->_buttonAllRight->type != DualListButtonType::None ) 
		{
			$this->makeButton($this->_buttonAllRight, "allright", $nodeWorking, $this->_listLeftName, $this->_listRightName, "true");
		}
		if (is_a($this->_buttonOneRight, "DualListButton") && $this->_buttonOneRight->type != DualListButtonType::None ) 
		{
			$this->makeButton($this->_buttonOneRight, "oneright", $nodeWorking, $this->_listLeftName, $this->_listRightName, "false");
		}
		if (is_a($this->_buttonOneLeft, "DualListButton") && $this->_buttonOneLeft->type != DualListButtonType::None ) 
		{
			$this->makeButton($this->_buttonOneLeft, "oneleft", $nodeWorking, $this->_listRightName, $this->_listLeftName, "false");
		}		
		if (is_a($this->_buttonAllLeft, "DualListButton") && $this->_buttonAllLeft->type != DualListButtonType::None ) 
		{
			$this->makeButton($this->_buttonAllLeft, "allleft", $nodeWorking, $this->_listRightName, $this->_listLeftName, "true");
		}
		
		XmlUtil::AddAttribute($nodeWorking, "name", $this->_name);
		$leftList = XmlUtil::CreateChild($nodeWorking, "leftlist", "");
		$rightList = XmlUtil::CreateChild($nodeWorking, "rightlist", "");
		XmlUtil::AddAttribute($leftList, "name", $this->_listLeftName);
		XmlUtil::AddAttribute($leftList, "caption", $this->_listLeftCaption);
		XmlUtil::AddAttribute($leftList, "size", $this->_listLeftSize);
		XmlUtil::AddAttribute($rightList, "name", $this->_listRightName);
		XmlUtil::AddAttribute($rightList, "caption", $this->_listRightCaption);
		XmlUtil::AddAttribute($rightList, "size", $this->_listRightSize);
		
		$arrRight = array();
		if (!is_null($this->_listRightDataSource)) 
		{
			while ($this->_listRightDataSource->hasNext())
			{
				$row = $this->_listRightDataSource->moveNext();
				$arrRight[$row->getField($this->_dataTableFieldId)] = $row->getField($this->_dataTableFieldText);
			}
		}	

		$arrLeft = array();
		while ($this->_listLeftDataSource->hasNext())
		{
			$row = $this->_listLeftDataSource->moveNext();
			if (!array_key_exists($row->getField($this->_dataTableFieldId), $arrRight))
			{
				$arrLeft[$row->getField($this->_dataTableFieldId)] = $row->getField($this->_dataTableFieldText);
			}
		}
		
		$this->buildListItens($leftList, $arrLeft);
		$this->buildListItens($rightList, $arrRight);
		
		return $nodeWorking;
	}
	
	/**
	 * Parse RESULTSS from DualList object
	 *
	 * @param Context $context
	 * @param string $duallistaname
	 * @return string[]
	 */
	public static function Parse($context, $duallistaname)
	{
		$val = $context->ContextValue($duallistaname);
		if ($val != "")
		{
			return explode(",", $val);
		}
		else 
		{
			return array();
		}
	}
	

	/**
	 * Build Dual lista data
	 *
	 * @param DOMNode $list
	 * @param array $arr
	 */
	private function buildListItens($list, $arr)
	{
		foreach ($arr as $key=>$value) 
		{
			$item = XmlUtil::CreateChild($list, "item", "");
			XmlUtil::AddAttribute($item, "id", $key);
			XmlUtil::AddAttribute($item, "text", $value);
		}
	}
	
	/**
	 * Make a buttom
	 *
	 * @param DualListButton $button
	 * @param string $name
	 * @param DOMNode $duallist
	 * @param string $from
	 * @param string $to
	 * @param string $all
	 */
	private function makeButton($button, $name, $duallist, $from, $to, $all)
	{
		$newbutton = XmlUtil::CreateChild($duallist, "button", "");
		XmlUtil::AddAttribute($newbutton, "name", $name);
		if ($button->type == DualListButtonType::Image ) {
			XmlUtil::AddAttribute($newbutton, "type", "image");
			XmlUtil::AddAttribute($newbutton, "src", $button->href);
			XmlUtil::AddAttribute($newbutton, "value", $button->text);
		}else {
			XmlUtil::AddAttribute($newbutton, "type", "button");
			XmlUtil::AddAttribute($newbutton, "value", $button->text);
		}
		XmlUtil::AddAttribute($newbutton, "from", $from);
		XmlUtil::AddAttribute($newbutton, "to", $to);
		XmlUtil::AddAttribute($newbutton, "all", $all);
	}
}

?>