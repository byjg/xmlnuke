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
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Classes;

class  XmlInputTextBox extends XmlInputValidate 
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
	protected $_size;
	/**
	*@var InputTextBoxType
	*/
	protected $_inputextboxtype;
	/**
	*@var bool
	*/
	protected $_readonly;
	/**
	*@var int
	*/
	protected $_maxlength;
	/**
	 * @var string
	 */
	protected $_readOnlyDeli = "[]";
	/**
	 * @var string
	 */
	protected $_autosuggestUrl = "";
	/**
	 * @var string
	 */
	protected $_autosuggestParamReq = "";
	/**
	 * @var string
	 */
	protected $_autosuggestCallback ="";
	/**
	 * @var string
	 */
	protected $_autosuggestJsonArray = "";
	/**
	 * @var string
	 */
	protected $_autosuggestJsonObjKey = "";
	/**
	 * @var string
	 */
	protected $_autosuggestJsonObjValue = "";
	/**
	 * @var string
	 */
	protected $_autosuggestJsonObjInfo = "";


	/**
	 * Only used if sets autocomplete!
	 *
	 * @var Context
	 */
	protected $_context;
	
	/**
	 * Only used if sets mask
	 * @var string
	 */
	protected $_maskText;
	/**
	 * @var bool
	 */
	protected $_disableAutoComplete = false;
	
	/**
	*@desc XmlInputTextBox constructor
	*@param string $caption
	*@param string $name
	*@param string $value
	*@param int $size 
	*/
	public function __construct($caption, $name, $value, $size = 20)
	{
		parent::__construct();
		$this->_name = $name;
		$this->_value = $value;
		$this->_caption = $caption;
		$this->_size = $size;
		$this->_readonly = false;
		$this->_required = false;
		$this->_maxlength = 0;
		$this->_inputextboxtype = InputTextBoxType::TEXT;
	}
	
	/**
	*@desc set input type
	*@param InputTextBoxType $inputextboxtype
	*@return void
	*/
	public function setInputTextBoxType($inputextboxtype)
	{
		$this->_inputextboxtype = $inputextboxtype;
	}
	
	/**
	*@desc set input as read only
	*@param bool $value
	*@return void
	*/
	public function setReadOnly($value)
	{
		$this->_readonly = $value;
	}
	
	public function setReadOnlyDelimeters($value)
	{
		if ((strlen($value) != 2) && (strlen($value) != 0))
		{
			throw new InvalidArgumentException("Read Only Delimiters must have two characters or is empty");
		}
		else 
		{
			$this->_readOnlyDeli = $value;
		}
	}

	public function setMaxLength($value)
	{
		$this->_maxlength = $value;
	}
	
	public function setValue($value)
	{
		$this->_value = $value;
	}
	public function getValue()
	{
		return $this->_value;
	}
	
	public function setName($name)
	{
		$this->_name = $name;
	}
	public function getName()
	{
		return $this->_name;
	}
	
	public function setCaption($caption)
	{
		$this->_caption = $caption;
	}
	public function getCaption()
	{
		return $this->_caption;
	}
	
	public function setMask($text)
	{
		$this->_maskText = $text;
	}
	public function getMask()
	{
		return $this->_maskText;
	}
	
	/**
	 * Configure an basic Autosuggest based on a JSON request.
	 *
	 * The Default parameters expected a json from XMLNuke "select" tag (xpath = //select). The Json looks like to:
	 *
	 * {"select":
	 *		{"caption":"Foto","name":"foto1",
	 *			"option":[
	 *				{"value":"13","#text":"Flamengo"},
	 *				{"value":"2626","#text":"Flamengo (Basquete)"},
	 *				{"value":"2597","#text":"Flavio Canto"},
	 *				{"value":"332","#text":"Flavio Venturini"},
	 *				{"value":"333","#text":"Flea"},
	 *				{"value":"334","#text":"Fleetwood Mac"},
	 *				{"value":"2441","#text":"Floresta"},
	 *				{"value":"12","#text":"Fluminense"}
	 *			]
	 *		}
	 * }
	 *
	 * The parameter $jsonArray will be "select.option" and the key "@value" and the value "#text";
	 *
	 * @param string $url
	 * @param string $attrInfo
	 * @param string $attrId
	 * @param string $attrCallback
	 * @param string $jsonArray
	 * @param string $jsonObjKey
	 * @param string $jsonObjValue
	 * @param string $jsonObjInfo
	 */
	public function setAutosuggest($context, $url, $paramReq, $jsCallback="", $jsonArray = "select.option", $jsonObjKey = "value", $jsonObjValue = "_text", $jsonObjInfo = "_text")
	{
		$this->_context = $context;
		$this->_autosuggestUrl = $url;
		$this->_autosuggestParamReq = $paramReq;
		$this->_autosuggestCallback = $jsCallback;
		$this->_autosuggestJsonArray = $jsonArray;
		$this->_autosuggestJsonObjKey = $jsonObjKey;
		$this->_autosuggestJsonObjValue = $jsonObjValue;
		$this->_autosuggestJsonObjInfo = $jsonObjInfo;
	}
	
	public function setDisableAutoComplete($value)
	{
		$this->_disableAutoComplete = $value;
	}
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return function
	*/
	public function generateObject($current)
	{
		if ($this->_readonly) 
		{ 
			$deliLeft = (strlen($this->_readOnlyDeli) != 0 ? $this->_readOnlyDeli[0] : "");
			$deliRight = (strlen($this->_readOnlyDeli) != 0 ? $this->_readOnlyDeli[1] : "");
			
			// XmlInputLabelField $ic; 
			if ($this->_inputextboxtype == InputTextBoxType::TEXT) 
			{ 
				$ic = new XmlInputLabelField($this->_caption, $deliLeft . $this->_value . $deliRight); 
			} 
			else 
			{ 
				$ic = new XmlInputLabelField($this->_caption, $deliLeft . "**********" . $deliRight); 
			} 
			$ic->generateObject($current); 

			// XmlInputHidden $ih
        		$ih = new XmlInputHidden($this->_name, $this->_value); 
			$ih->generateObject($current); 
		}
		else 
		{
			if ($this->_inputextboxtype == InputTextBoxType::TEXT)
			{
				$nodeWorking = XmlUtil::CreateChild($current, "textbox", "");
			}
			else
			{
				$nodeWorking = XmlUtil::CreateChild($current, "password", "");
			}
			if(intval($this->_maxlength) != 0)
			{
				XmlUtil::AddAttribute($nodeWorking, "maxlength", $this->_maxlength);
			}
			XmlUtil::AddAttribute($nodeWorking, "caption", $this->_caption);
			XmlUtil::AddAttribute($nodeWorking, "name", $this->_name);
			XmlUtil::AddAttribute($nodeWorking, "value", $this->_value);
			XmlUtil::AddAttribute($nodeWorking, "size", $this->_size);
			
			if ($this->_autosuggestUrl != "")
			{
				$url = new XmlnukeManageUrl(URLTYPE::MODULE, $this->_autosuggestUrl);
				$urlStr = $url->getUrlFull();
				if (strpos($urlStr, "?")===false)
				{
					$urlStr .= "?";
				}
				else 
				{
					$urlStr .= "&";
				}
				XmlUtil::AddAttribute($nodeWorking, "autosuggesturl", str_replace("&", "&amp;", $urlStr));
				XmlUtil::AddAttribute($nodeWorking, "autosuggestparamreq", $this->_autosuggestParamReq);
				if ($this->_autosuggestCallback) XmlUtil::AddAttribute($nodeWorking, "autosuggestcallback", $this->_autosuggestCallback);
				XmlUtil::AddAttribute($nodeWorking, "autosuggest_array", $this->_autosuggestJsonArray);
				XmlUtil::AddAttribute($nodeWorking, "autosuggest_objid", $this->_autosuggestJsonObjKey);
				XmlUtil::AddAttribute($nodeWorking, "autosuggest_objvalue", $this->_autosuggestJsonObjValue);
				XmlUtil::AddAttribute($nodeWorking, "autosuggest_objinfo", $this->_autosuggestJsonObjInfo);
			}
			
			if ($this->getMask() == "")
			{
				if ($this->getDataType() == INPUTTYPE::DATE)
				{
					$this->setMask("99/99/9999");
				}
				elseif ($this->getDataType() == INPUTTYPE::DATETIME)
				{
					$this->setMask("99/99/9999 99:99:99");
				}
			}
			
			if ($this->getMask() != "")
			{
				XmlUtil::AddAttribute($nodeWorking, "mask", $this->_maskText);
			}
			
			if ($this->_disableAutoComplete)
			{
				XmlUtil::AddAttribute($nodeWorking, "autocomplete", "off");
			}
			
			parent::generateObject($nodeWorking);
		}
	}
	
}	

?>
