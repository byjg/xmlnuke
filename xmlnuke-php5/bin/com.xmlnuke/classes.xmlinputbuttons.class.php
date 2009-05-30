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
class ButtonType
{
	const SUBMIT = 1;
	const RESET = 2;
	const CLICKEVENT = 3;
	const BUTTON = 4;
}
/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class InputButton
{
	/**
	*@var ButtonType
	*/
	public $buttonType;
	/**
	*@var string
	*/
	public $name;
	/**
	*@var string
	*/
	public $caption;
	/**
	*@var string
	*/
	public $onClick;
}
/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlInputButtons extends XmlnukeDocumentObject
{
	/**
	*@var array
	*/
	protected $_values;
	
	/**
	*@desc XmlInputButtons constructor
	*@param 
	*@return 
	*/
	public function XmlInputButtons()
	{
		$this->_values = array();
	}
	
	/**
	*@desc add submit
	*@param string $caption
	*@param string $name
	*@return void
	*/
	public function addSubmit($caption, $name = "")
	{
//		InputButton $ib
		$ib = new InputButton();
		$ib->caption = $caption;
		$ib->name = $name;
		$ib->buttonType = ButtonType::SUBMIT;
		$this->_values[] = $ib;
	}

	/**
	@desc Add a Submit button with an event associated with him. The method created must be the suffix "_Event".
	@param string $caption
	@param string $methodName
	*/
	public function addClickEvent($caption, $methodName)
	{
//		InputButton $ib
		$ib = new InputButton();
		$ib->caption = $caption;
		$ib->name = $methodName;
		$ib->buttonType = ButtonType::CLICKEVENT;
		$this->_values[] = $ib;
	}

	/**
	*@desc 
	*@param string $caption
	*@param string $name
	*@return void
	*/
	public function addReset($caption, $name = "")
	{
//		InputButton $ib
		$ib = new InputButton();
		$ib->caption = $caption;
		$ib->name = $name;
		$ib->buttonType = ButtonType::RESET;
		$this->_values[] = $ib;
	}

	/**
	*@desc 
	*@param string $caption
	*@param string $name
	*@param string $onclick
	*@return void
	*/
	public function addButton( $caption, $name, $onclick)
	{
//		InputButton $ib
		$ib = new InputButton();
		$ib->caption = $caption;
		$ib->name = $name;
		$ib->onClick = $onclick;
		$ib->buttonType = ButtonType::BUTTON;
		$this->_values[] = $ib;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $caption
	 * @return XmlInputButtons
	 */
	public static function CreateSubmitButton($caption)
	{
		$button = new XmlInputButtons();
		$button->addSubmit($caption);
		return $button;
	}

	/**
	 * Enter description here...
	 *
	 * @param string $captionSubmit
	 * @param string $captionCancel
	 * @param string $urlCancel
	 * @return XmlInputButtons
	 */
	public static function CreateSubmitCancelButton($captionSubmit, $captionCancel, $urlCancel)
	{
		$button = XmlInputButtons::CreateSubmitButton($captionSubmit);
		$button->addButton($captionCancel, "cancel", "javacript:window.location = '$urlCancel'");
		return $button;
	}

	/**
	*@desc Contains specific instructions to generate all XML informations-> This method is processed only one time-> Usually is the last method processed->
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		$objBoxButtons = XmlUtil::CreateChild($current, "buttons", "");
		$clickEvent = "";
		
		foreach($this->_values as $button)
		{
//			InputButton $button
			if ($button->buttonType == ButtonType::CLICKEVENT)
			{
				$clickEvent .= ($clickEvent=="" ? "" : "|") . $button->name;
			}
			
			$nodeWorking = null;
			switch ($button->buttonType)
			{
				case ButtonType::CLICKEVENT:
				case ButtonType::SUBMIT:
				{
					$nodeWorking = XmlUtil::CreateChild($objBoxButtons, "submit", "");
					break;
				}
				case ButtonType::RESET:
				{
					$nodeWorking = XmlUtil::CreateChild($objBoxButtons, "reset", "");
					break;
				}
				case ButtonType::BUTTON:
				{
					$nodeWorking = XmlUtil::CreateChild($objBoxButtons, "button", "");
					XmlUtil::AddAttribute($nodeWorking, "onclick", $button->onClick);
					break;
				}
			}
			XmlUtil::AddAttribute($nodeWorking, "caption", $button->caption);
			XmlUtil::AddAttribute($nodeWorking, "name", $button->name);
		}
		
		// Add Click Event
		$clickEventNode = XmlUtil::selectSingleNode($current, "clickevent");
		if (is_null($clickEventNode))
		{
			$clickEventNode = XmlUtil::CreateChild($current, "clickevent", $clickEvent);
		}
		else
		{
			$clickEventNode->nodeValue = $clickEventNode->nodeValue . "|" . $clickEvent;
		}
	}

}

?>