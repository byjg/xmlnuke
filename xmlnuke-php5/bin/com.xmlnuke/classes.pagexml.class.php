<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  Acknowledgments to: Yuri Bastos Wanderley
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

/// <summary>
/// PageXml class abstract the XMLNuke's XML structure. Use this class to create custom user modules.
/// </summary>
/**
*@package com.xmlnuke
*@subpackage xmlnuke_kernel
*/
class DATEFORMAT
{
	const DMY = 0;
	const MDY = 1;
	const YMD = 2;
}
/**
*@package com.xmlnuke
*@subpackage xmlnuke_kernel
*/
class INPUTTYPE
{
	const TEXT = 0;
	const LOWER = 1;
	const UPPER = 2;
	const NUMBER = 3;
	const DATE = 4;
	const DATETIME = 5;
	const UPPERASCII = 9;
	const EMAIL = 10;
}

/**
*@package com.xmlnuke
*@subpackage xmlnuke_kernel
*/
class PageXml implements IXmlnukeDocument 
{
	/**
	*@var DOMDocument
	*/
	private $_xmlDoc;
	/**
	*@var DOMNode
	*/
	private $_nodePage;
	/**
	*@var DOMNode
	*/
	protected $_nodeGroup;
	/**
	*@var bool
	*/
	private $_breakLine;
	
	/**
	*@param XMLFilenameProcessor $xmlfilename
	*@param string $path
	*@param string $strfilename
	*@return void
	*@desc 
	*PageXml Constructor. Empty page. and PageXml Constructor. Create from XML.
	*PageXml Constructor. Create from file name and path. Do not use with XmlNukeDB repository.
	*/
	public function PageXml($xmlfilename=null,$path=null, $strfilename=null)
	{

		if(($xmlfilename == null)&&($path == null)&&($strfilename == null))
		{
			$auxStr = "<page>\r\n".
			"<meta>\r\n".
			"<title/>\r\n".
			"<abstract/>\r\n".
			"<created>".date("D M j Y G:i:s")."</created>\r\n".
			"<modified>".date("D M j Y G:i:s")."</modified>\r\n".
			"<keyword>XMLSite ByJG</keyword>\r\n".
			"<groupkeyword/>\r\n".
			"</meta>\r\n".
			"<group>\r\n".
			"<id>__DEFAULT__</id>\r\n".
			"<title/>\r\n".
			"<keyword>all</keyword>\r\n".
			"</group>\r\n".
			"</page>";
			$this->_xmlDoc = XmlUtil::CreateXmlDocumentFromStr($auxStr);
			$xpath = new DOMXPath($this->_xmlDoc);
			$this->_nodePage = $this->_xmlDoc->getElementsByTagName("page")->item(0);
			$this->_nodeGroup = $xpath->query("/page/group")->item(0);

		}
		else if(($xmlfilename != null)&&($path == null)&&($strfilename == null))
		{
			$this->_xmlDoc = $xmlfilename->getContext()->getXMLDataBase()->getDocument($xmlfilename->FullQualifiedName());
		}
		else if(($xmlfilename == null)&&($path != null)&&($strfilename != null))
		{
			$xmlDoc = XmlUtil::CreateXmlDocument();
			$xmlDoc->Load(FileUtil::AdjustSlashes($path.FileUtil::Slash().$strfilename) );
		}

	}

	/**
	*@param string $value
	*@return void
	*@desc 
	*Get/Set the xml metadata title
	*/
	public function setTitle($value)//ok
	{
		$xpath = new DOMXPath($this->_xmlDoc);
		$xpath->query("/page/meta/title")->item(0)->nodeValue = $value ;
	}
	/**
	*@return string
	*@desc Get Title
	*/
	public function getTitle()//ok
	{
		$xpath = new DOMXPath($this->_xmlDoc);
		return 	$xpath->query("/page/meta/title")->item(0)->nodeValue;
	}


	/**
	*@param mixed $value
	*@return void
	*@desc setAbstract
	*/	
	
	
	public function setAbstract($value)
	{
		$xpath = new DOMXPath($this->_xmlDoc);
		$xpath->query("/page/meta/abstract")->item(0)->nodeValue= $value ;

	}
	
	/**
	*@return mixed
	*@desc Get Abstract
	*/
	
	public function getAbstract()
	{
		$xpath = new DOMXPath($this->_xmlDoc);
		return 	$xpath->query("/page/meta/abstract")->item(0)->nodeValue;
	}



	
	
	/**
	*@param mixed $value
	*@return void
	*@desc setGroupKeyword (used to list menus)
	*/		
	
	public function setGroupKeyword($value)
	{
		$xpath = new DOMXPath($this->_xmlDoc);
		$xpath->query("/page/meta/groupkeyword")->item(0)->nodeValue= $value ;

	}
	
	/**
	*@return mixed
	*@desc Get GroupKeyword (used to list menus)
	*/	
	
	public function getGroupKeyword()
	{
		$xpath = new DOMXPath($this->_xmlDoc);
		return 	$xpath->query("/page/meta/groupkeyword")->item(0)->nodeValue;
	}



	/**
	*@return void
	*@desc set Modified date
	*/			
	
	private function setModified()
	{
		$xpath = new DOMXPath($this->_xmlDoc);
		$xpath->query("/page/meta/modified")->item(0)->nodeValue= date("D M j Y G:i:s");
	}

	
	/**
	*@return void
	*@desc  Get the xml metadata datetime created
	*/	
		
	public function Created()
	{
		$xpath = new DOMXPath($this->_xmlDoc);
		return 	$xpath->query("/page/meta/created")->item(0)->nodeValue;
	}

	/**    
	*@return mixed
	*@desc Get the BreakLine information. After add text to a paragraph BreakLine or not
	*/			
	
	public function getBreakLine()
	{
		return $this->_breakLine;
	}
	
	/**    
	*@return mixed
	*@desc Set the BreakLine information. After add text to a paragraph BreakLine or not
	*/		
	
	public function setBreakLine($value)
	{
		$this->_breakLine = $value;
	}

	//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
	// Paragraph Methods
	//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

	
	/**
	*@param string $XML
	*@return void
	*@desc Add a free XML string to structure.
	*Be careful. Only Works on tags blockcenter, blockleft, blockright.</b></p>
	*/
	
	public function addXMLBlock($XML)
	{
		$xmlDocstr = $this->_xmlDoc->saveXml();
		$i = strpos($xmlDocstr,"</page>");
		$xmlDocstr = substr($xmlDocstr,0, $i).$XML."</page>";
		$this->_xmlDoc->LoadXml($xmlDocstr);
	}

	/// <summary>
	/// Add a single blockcenter.
	/// <code>
	/// <blockcenter>
	///		<title></title>
	///		<body></body>
	/// </blockcenter>
	/// </code>
	/// </summary>
	/// <param name="title"></param>
	/// <returns>Return the BODY element from Block</returns>
	//Parameter: String // Return: DOMNode
	
	
	/**
	*@param string $title
	*@return DOMNode
	*@desc Add a single blockcenter.
	*<code><blockcenter><title></title><body></body></blockcenter></code>
	*<returns>Return the BODY element from Block</returns>
	*/
	
	
	public function addBlockCenter($title)
	{
		//$objBlockCenter = XmlUtil::CreateChild($this->_nodePage, "blockcenter", "");
		$objBlockCenter = XmlUtil::CreateChild($this->_xmlDoc->documentElement, "blockcenter", "");
		XmlUtil::CreateChild($objBlockCenter, "title", $title);
		return XmlUtil::CreateChild($objBlockCenter, "body", "");
	}
	
	public function addBlockRight($title)
	{		
		$objBlockRight = XmlUtil::CreateChild($this->_xmlDoc->documentElement, "blockright", "");
		XmlUtil::CreateChild($objBlockRight, "title", $title);
		return XmlUtil::CreateChild($objBlockRight, "body", "");
	}
	public function addBlockLeft($title)
	{		
		$objBlockLeft = XmlUtil::CreateChild($this->_xmlDoc->documentElement, "blockleft", "");
		XmlUtil::CreateChild($objBlockLeft, "title", $title);
		return XmlUtil::CreateChild($objBlockLeft, "body", "");
	}
	

	/// <summary>
	/// A single paragraph into Body element.
	/// <code>
	///		<body>
	///			<p></p>
	///		</body>
	/// </code>
	/// </summary>
	/// <param name="objBlockCenter"></param>
	//Parameter: DOMNode // Return: DOMNode
	
	/**
	*@param DOMNode $objBlockCenter
	*@return DOMNode
	*@desc A single paragraph into Body element.
	*<code><blockcenter><title></title><body></body></blockcenter></code>
	*<returns>Return the BODY element from Block</returns>
	*/
	
	public function  addParagraph($objBlockCenter)
	{
		return XmlUtil::CreateChild($objBlockCenter, "p", "");
	}
	
	/**
	*@param DOMNode $code
	*@param DOMNode $objParagraph
	*/
	public function addCode($objParagraph,$code)
	{
		XmlUtil::CreateChild($objParagraph, "code", $code);
	}

	/// <summary>
	/// Add text to a paragraph structure
	/// </summary>
	/// <param name="objParagraph">Paragraph structure</param>
	/// <param name="strText">Text to be added</param>
	/// Parameters: DOMNode $objParagraph, string $strText
	
	/**
	*@param string $strText
	*@param DOMNode $objParagraph	
	*@desc Add text to a paragraph structure
	*<param name="objParagraph">Paragraph structure</param>
	*<param name="strText">Text to be added</param>
	*/
	
	public function addText($objParagraph,$strText)
	{
		XmlUtil::AddTextNode($objParagraph, $strText);
		if ($this->_breakLine)
		{
			XmlUtil::CreateChild($objParagraph, "br", "");
		}
	}

	/// <summary>
	/// Add Italic text to a paragraph structure
	/// </summary>
	/// <param name="objParagraph">Paragraph structure</param>
	/// <param name="strText">Text to be added</param>
	/// Parameters: DOMNode $objParagraph, string $strText
	
	/**
	*@param string $strText
	*@param DOMNode $objParagraph	
	*@desc Add Italic text to a paragraph structure
	*<param name="objParagraph">Paragraph structure</param>
	*<param name="strText">Text to be added</param>
	*/
	
	public function addItalic($objParagraph, $strText)
	{
		XmlUtil::CreateChild($objParagraph, "i", $strText);
		if ($this->_breakLine)
		{
			XmlUtil::CreateChild($objParagraph, "br", "");
		}
	}

	/// <summary>
	/// Add bold text to a paragraph structure
	/// </summary>
	/// <param name="objParagraph">Paragraph structure</param>
	/// <param name="strText">Text to be added</param>
	/// Parameters: DOMNode $objParagraph, string $strText
	
	/**
	*@param string $strText
	*@param DOMNode $objParagraph	
	*@desc Add bold text to a paragraph structure
	*<param name="objParagraph">Paragraph structure</param>
	*<param name="strText">Text to be added</param>
	*/
	
	public function addBold($objParagraph, $strText)
	{
		XmlUtil::CreateChild($objParagraph, "b", $strText);
		if ($this->_breakLine)
		{
			XmlUtil::CreateChild($objParagraph, "br", "");
		}
	}
	//Parameter: DOMNode $objParagraph // Return: DOMNode
	public function addTable($objParagraph)
	{
		return XmlUtil::CreateChild($objParagraph, "table", "");
	}
	// DOMNode $objTable // Return: DOMNode
	public function addTableRow($objTable)
	{
		return XmlUtil::CreateChild($objTable, "tr", "");
	}
	// DOMNode $objTableRow // Return: DOMNode
	public function addTableColumn($objTableRow)
	{
		return XmlUtil::CreateChild($objTableRow, "td", "");
	}

	/// <summary>
	/// Add image to a paragragh structure
	/// <code>
	///		<body>
	///			<p>
	///				<img src="" />
	///			</p>
	///		</body>
	/// </code>
	/// </summary>
	/// <param name="objParagraph">Paragragh structure</param>DOMNode
	/// <param name="strSrc">SRC tag</param>string
	/// <param name="strAlt">ALT tag</param>string
	/// <param name="intWidth">Width</param>int
	/// <param name="intHeight">Height</param>int
	
	/**
	*@param DOMNode $objParagraph Paragragh structure
	*@param string $strSrc SRC tag
	*@param string $strAlt ALT tag
	*@param int $intWidth Width
	*@param int $intHeight	Height
	*@desc Add image to a paragragh structure
	*/
	
	public function addImage($objParagraph, $strSrc, $strAlt, $intWidth, $intHeight)
	{
		$nodeWorking = XmlUtil::CreateChild($objParagraph, "img", "");
		XmlUtil::AddAttribute($nodeWorking, "src", $strSrc);
		XmlUtil::AddAttribute($nodeWorking, "alt", $strAlt);
		XmlUtil::AddAttribute($nodeWorking, "width", $intWidth);
		XmlUtil::AddAttribute($nodeWorking, "height", $intHeight);
		if ($this->_breakLine)
		{
			XmlUtil::CreateChild($objParagraph, "br", "");
		}
		return $nodeWorking;
	}
	//SOBRECARGA SUPRIMIDA//
	/// <summary>
	/// Add HREF to paragraph structure
	/// </summary>
	/// <param name="objParagraph">Paragraph structure</param>
	/// <param name="link">Hyperlink</param>
	/// <param name="text">Text</param>
	/// <param name="target">Target</param>
	///Parameters: DOMNode $objParagraph, string $link, string $text, string $target
	
	/**
	*@param DOMNode $objParagraph
	*@param string $link
	*@param string $text
	*@param string $target
	*@desc Add HREF to paragraph structure
	*/
	
	public  function addHref($objParagraph, $link, $text, $target = null)
	{
		if($target == null)
		{
			$target = "";
		}

		$nodeWorking = XmlUtil::CreateChild($objParagraph, "a", $text);
		
		if(strpos($link, "&amp;")=== false)
			$link = str_replace("&", "&amp;",$link);#Acrescentado PHP Version
		
		XmlUtil::AddAttribute($nodeWorking, "href", $link);
		if ($target != "")
		{
			XmlUtil::AddAttribute($nodeWorking, "target", $target);
		}
		if ($this->_breakLine)
		{
			XmlUtil::CreateChild($objParagraph,"br","");
		}
		return $nodeWorking;
	}
	///Parameters: DOMNode
	public function addUnorderedList($objParagraph)
	{
		return XmlUtil::CreateChild($objParagraph, "ul", "");
	}

	public function addOptionList($objList)
	{
		return XmlUtil::CreateChild($objList, "li", "");
	}

	//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
	// Form Methods
	//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

	/// <summary>
	/// Add FORM structure into blobkcenter structure
	/// <code>
	///		<body>
	///			<editform>
	///			</editform>
	///		</body>
	/// </code>
	/// </summary>
	/// <param name="objBlockCenter">Blockcenter structure</param>DOMNode
	/// <param name="action">Form Action</param>String
	/// <param name="title">Titile</param>String
	/// <param name="decimalseparator">Decimal separator</param>
	/// <param name="dateformat">Date format: DMY, MDY, YMD</param>
	/// <returns>DOMNode</returns>
	
	/**
	*@return DOMNode
	*@param DOMNode $objBlockCenter Blockcenter structure
	*@param string $action Form Action
	*@param string $title Titile
	*@param string $decimalseparator Decimal separator
	*@param string $dateformat Date format
	*@desc Add FORM structure into blobkcenter structure
	*/
	
	
	public function addForm($objBlockCenter, $action, $title, $formName=null, $jsValidate=null, $decimalSeparator=null, $dateformat = null)
	{
		$nodeWorking = XmlUtil::CreateChild($objBlockCenter, "editform", "");
		XmlUtil::AddAttribute($nodeWorking, "action", $action);
		XmlUtil::AddAttribute($nodeWorking, "title", $title);
		if($formName!=null)
		{
			XmlUtil::AddAttribute($nodeWorking, "name", $formName);
		}
		
		if($jsValidate!=null)
		{			
			//$jsValidate = strtolower((string)$jsValidate);
			//echo "Validate: ".($jsValidate)?"true":"false";
			XmlUtil::AddAttribute($nodeWorking, "jsvalidate", ($jsValidate)?"true":"false");
		}
		if($decimalSeparator!=null)
		{
			XmlUtil::AddAttribute($nodeWorking, "decimalseparator", (string)$decimalSeparator);
		}
		if($dateformat!=null)
		{
			XmlUtil::AddAttribute($nodeWorking, "dateformat", (string)$dateformat);
		}		
		return $nodeWorking;
	}
	/// <summary>
	/// Add a label/caption to Form Structure
	/// </summary>
	/// <param name="objForm">Form Structure</param>DOMNode
	/// <param name="text">Text label</param>string
	
	/**
	*@param DOMNode $objForm Form Structure
	*@param string $text Text label
	*@desc Add a label/caption to Form Structure
	*/
	
	public function addCaption($objForm,$text)
	{
		XmlUtil::CreateChild($objForm, "caption", $text);
	}

	/// <summary>
	/// Add hidden object to Form structure
	/// </summary>
	/// <param name="objForm">Form structure</param>
	/// <param name="name">Name</param>
	/// <param name="value">Value</param>
	/// Parameters: DOMNode $objForm, string $name, string $value
	
	/**
	*@param DOMNode $objForm Form structure
	*@param  string $name Name
	*@param string $value Value
	*@desc Add hidden object to Form structure
	*/
	
	public function addHidden($objForm, $name, $value)
	{
		$nodeWorking = XmlUtil::CreateChild($objForm, "hidden", "");
		XmlUtil::AddAttribute($nodeWorking, "name", $name);
		XmlUtil::AddAttribute($nodeWorking, "value", $value);
	}

	/// <summary>
	/// Add a Text Box Object to Form structure
	/// </summary>
	/// <param name="objForm">Form structure</param>
	/// <param name="caption">Caption</param>
	/// <param name="name">Name</param>
	/// <param name="value">Value</param>
	/// <param name="size">Max size</param>
	/// Parameters: DOMNode $objForm, string $caption, string $name, string $value, int $size
/*
	public function addTextBox($objForm, $caption, $name, $value, $size)
	{
		$nodeWorking = XmlUtil::CreateChild($objForm, "textbox", "");
		XmlUtil::AddAttribute($nodeWorking, "caption", $caption);
		XmlUtil::AddAttribute($nodeWorking, "name", $name);
		XmlUtil::AddAttribute($nodeWorking, "value", $value);
		XmlUtil::AddAttribute($nodeWorking, "size", $size);
	}
*/

	/**
	*@param DOMNode $objForm Form structure
	*@param  string $caption Caption
	*@param string $name name
	*@param string $value Value
	*@param int $size Max size
	*@desc Add a Text Box Object to Form structure
	*/

	public function addTextBox($objForm, $caption, $name, $value, $size, $required=false, $inputtype=0,$maxLength="", $minvalue="", $maxvalue="", $description="", $customjs="")
	{
		$nodeWorking = XmlUtil::CreateChild($objForm, "textbox", "");
		XmlUtil::AddAttribute($nodeWorking, "caption", $caption);
		XmlUtil::AddAttribute($nodeWorking, "name", $name);
		XmlUtil::AddAttribute($nodeWorking, "value", $value);
		XmlUtil::AddAttribute($nodeWorking, "size", $size);
		$this->addJSValidation($nodeWorking, $required, $inputtype,$maxLength, $minvalue, $maxvalue, $description, $customjs);
		
		return $nodeWorking;
		
	} 

	private function addJSValidation($objInput, $required, $inputtype,$maxLength, $minvalue, $maxvalue, $description, $customjs)
	{		
		XmlUtil::AddAttribute($objInput, "required", ($required?"true":"false"));
		XmlUtil::AddAttribute($objInput, "type", (string)$inputtype);
		if ($minvalue != "")
		{
			XmlUtil::AddAttribute($objInput, "minvalue", $minvalue);
		}
		if ($maxvalue != "")
		{
			XmlUtil::AddAttribute($objInput, "maxvalue", $maxvalue);
		}
		if ($description != "")
		{
			XmlUtil::AddAttribute($objInput, "description", $description);
		}
		if ($customjs != "")
		{
			XmlUtil::AddAttribute($objInput, "customjs", $customjs);
		}
		if ($maxLength != "")
		{
			XmlUtil::AddAttribute($objInput, "maxlength", $maxLength);
		}
	}
	
	
	public function addLabelField($objForm, $caption, $value)
	{
		$nodeWorking = XmlUtil::CreateChild($objForm, "label", "");
		XmlUtil::AddAttribute($nodeWorking, "caption", $caption);
		XmlUtil::AddAttribute($nodeWorking, "value", $value);
	}

	/// <summary>
	/// Add a Password Text Box Object to Form structure
	/// </summary>
	/// <param name="objForm">Form structure</param>
	/// <param name="caption">Caption</param>
	/// <param name="name">Name</param>
	/// <param name="size">Max size</param>
	///  Parameters: DOMNode $objForm, string $caption, string $name, int $size
	
	/**
	*@param DOMNode $objForm Form structure
	*@param string $caption Caption
	*@param string $name name
	*@param int $size Max size
	*@desc  Add a Password Text Box Object to Form structure
	*/
	
	public function addPassword($objForm, $caption, $name, $size, $required="", $inputtype="",$maxLength="", $minvalue="", $maxvalue="", $description="", $customjs="")
	{
		$nodeWorking = XmlUtil::CreateChild($objForm, "password", "");
		XmlUtil::AddAttribute($nodeWorking, "caption", $caption);
		XmlUtil::AddAttribute($nodeWorking, "name", $name);
		XmlUtil::AddAttribute($nodeWorking, "size", $size);
		$this->addJSValidation($nodeWorking, $required, $inputtype,$maxLength, $minvalue, $maxvalue, $description, $customjs);
	}

	/// <summary>
	/// Add a Multiline Text Box Object to Form structure
	/// </summary>
	/// <param name="objForm">Form structure</param>
	/// <param name="caption">Caption</param>
	/// <param name="name">Name</param>
	/// <param name="value">Value</param>
	/// <param name="cols">Cols</param>
	/// <param name="rows">Rows</param>
	/// <param name="wrap">SOFT|OFF</param>
	/// Parameters: DOMNode objForm, string caption, string name, string value, int cols, int rows, string wrap
	//TEXTAREA
	
	/**
	*@param DOMNode $objForm Form structure
	*@param string $caption Caption
	*@param string $name name
	*@param string $value Value
	*@param int $cols Cols
	*@param int rows Max Rows
	*@param string wrap SOFT|OFF
	*@desc  Add a Multiline Text Box Object to Form structure
	*/
	
	public function addMemo($objForm, $caption, $name, $value, $cols, $rows, $wrap)
	{

		$nodeWorking = XmlUtil::CreateChild($objForm, "memo", "");
		XmlUtil::AddAttribute($nodeWorking, "caption", $caption);
		XmlUtil::AddAttribute($nodeWorking, "name", $name);
		XmlUtil::AddAttribute($nodeWorking, "cols", $cols);
		XmlUtil::AddAttribute($nodeWorking, "rows", $rows);
		XmlUtil::AddAttribute($nodeWorking, "wrap", $wrap);
		XmlUtil::AddTextNode($nodeWorking, $value);

	}

	/// <summary>
	/// Add a Radio Box Object to Form structure
	/// </summary>
	/// <param name="objForm">Form structure</param>
	/// <param name="caption">Caption</param>
	/// <param name="name">Name</param>
	/// <param name="value">Value</param>
	/// Parameters: DOMNode objForm, string caption, string name, string value
	
	/**
	*@param DOMNode $objForm Form structure
	*@param string $caption Caption
	*@param string $name name
	*@param string $value Value
	*@desc  Add a Radio Box Object to Form structure
	*/
	
	public function addRadioBox($objForm, $caption, $name, $value)
	{
		$nodeWorking = XmlUtil::CreateChild($objForm, "radiobox", "");
		XmlUtil::AddAttribute($nodeWorking, "caption", $caption);
		XmlUtil::AddAttribute($nodeWorking, "name", $name);
		XmlUtil::AddAttribute($nodeWorking, "value", $value);
	}

	/// <summary>
	/// Add a Check Box Object to Form structure
	/// </summary>
	/// <param name="objForm">Form structure</param>
	/// <param name="caption">Caption</param>
	/// <param name="name">Name</param>
	/// <param name="value">Value</param>
	/// Parameters: DOMNode objForm, string caption, string name, string value
	
	/**
	*@param DOMNode $objForm Form structure
	*@param string $caption Caption
	*@param string $name name
	*@param string $value Value
	*@desc  Add a Check Box Object to Form structure
	*/
	
	public function addCheckBox($objForm, $caption, $name, $value, $selected=false)
	{
		$nodeWorking = XmlUtil::CreateChild($objForm, "checkbox", "");
		XmlUtil::AddAttribute($nodeWorking, "caption", $caption);
		XmlUtil::AddAttribute($nodeWorking, "name", $name);
		XmlUtil::AddAttribute($nodeWorking, "value", $value);
		if($selected)
			XmlUtil::AddAttribute($nodeWorking, "selected", "yes");
	}

	/// <summary>
	/// Add a Select object to Form Structure
	/// </summary>
	/// <param name="objForm">Form Structure</param>
	/// <param name="caption">Caption</param>
	/// <param name="name">Select name</param>
	/// <returns>Select object</returns>
	/// Parameters: DOMNode objForm, string caption, string name // Return:DOMNode
	
	/**
	*@return DOMNode 
	*@param DOMNode $objForm Form structure
	*@param string $caption Caption
	*@param string $name name
	*@desc  Add a Select object to Form Structure
	*/
	
	public function addSelect($objForm, $caption, $name, $values=null, $defaultValue="", $required=false, $customJs=null)
	{
		
		$nodeWorking = XmlUtil::CreateChild($objForm, "select", "");
		XmlUtil::AddAttribute($nodeWorking, "caption", $caption);
		XmlUtil::AddAttribute($nodeWorking, "name", $name);
		($required)?$required = "true":$required = "false";	
		XmlUtil::AddAttribute($nodeWorking , "required", $required);
		if ($customJs != null)
		{
			XmlUtil::AddAttribute($nodeWorking, "customjs", $customJs);
		}

		//$nodeWorking = $this->addSelect3($objForm, $caption, $name);
		if ($values != null)
		{
			foreach($values as $chave=>$value)
			{
				$this->addOption($nodeWorking, $value, $value, ($value == $defaultValue));
			}
		}
		return $nodeWorking;
	}	
	
	///////////////////////////////
	//@ESSES TR�S MET�DOS SER�O SUBSTITUIDOS POR ADDSELECT()
	public function addSelect3($objForm, $caption, $name)
	{
		$nodeWorking = XmlUtil::CreateChild($objForm, "select", "");
		XmlUtil::AddAttribute($nodeWorking, "caption", $caption);
		XmlUtil::AddAttribute($nodeWorking, "name", $name);
		return $nodeWorking;
	}
	///Parameters: DOMNode objForm, string caption, string name, string[] values // Returns: DOMNode
	public function addSelect4($objForm, $caption, $name, $values)
	{
		return $this->addSelect5($objForm, $caption, $name, $values, "");
	}
	///Parameters: DOMNode objForm, string caption, string name, string[] values, string defaultValue
	// Returns: DOMNode
	public function addSelect5($objForm, $caption, $name, $values, $defaultValue)
	{
		$nodeWorking = $this->addSelect3($objForm, $caption, $name);
		if ($values != null)
		{
			foreach($values as $chave=>$value)
			{
				$this->addOption($nodeWorking, $value, $value, ($value == $defaultValue));
			}
		}
		return $nodeWorking;
	}	
	//@ESSES TR�S MET�DOS SER�O SUBSTITUIDOS POR ADDSELECT()
	/////////////////////////////////////////////////////////
	
	//SOBRECARGA SUPRIMIDA//
	/// <summary>
	/// Add a option line to a Select Object
	/// </summary>
	/// <param name="objSelect">Select Object</param>
	/// <param name="caption">Caption</param>
	/// <param name="value">Value</param>
	/// Parameters: DOMNode objSelect, string caption, string value, bool selected
	
	/**
	*@param DOMNode $objSelect Select Object
	*@param string $caption Caption
	*@param string $value Value
	*@param bool $selected 
	*@desc  Add a option line to a Select Object
	*/
	
	public function addOption($objSelect, $caption, $value, $selected=false)
	{
		if($selected == null)
		{
			$selected=false;
		}
		$nodeWorking = XmlUtil::CreateChild($objSelect, "option", "");
		XmlUtil::AddAttribute($nodeWorking, "value", $value);
		if ($selected)
		{
			XmlUtil::AddAttribute($nodeWorking, "selected", "yes");
		}
		XmlUtil::AddTextNode($nodeWorking, $caption);
	}

	/// <summary>
	/// Add a Box option to a form Object
	/// </summary>
	/// <param name="objForm">Form Object</param>
	/// <returns>Box Option</returns>
	/// Parameter: DOMNode objForm // return: DOMNode
	
	/**
	*@return DOMNode
	*@param DOMNode $objForm Form Object
	*@desc  Add a Box option to a form Object
	*/
	
	public function addBoxButtons($objForm)
	{
		return XmlUtil::CreateChild($objForm, "buttons", "");
	}

	/// <summary>
	/// Add a Button to a Box Button Object
	/// </summary>
	/// <param name="objBoxButtons">Box Button Object</param>
	/// <param name="name">Name</param>
	/// <param name="caption">Caption</param>
	/// <param name="onclick">Onclick javascript</param>
	/// Parameters: DOMNode objBoxButtons, string name, string caption, string onclick
	
	/**
	*@param DOMNode objBoxButtons Box Button Object
	*@param string $name name
	*@param string $caption Caption
	*@param string $onclick Onclick javascript
	*@desc Add a Button to a Box Button Object
	*/
	
	public function addButton($objBoxButtons, $name, $caption, $onclick )
	{
		$nodeWorking = XmlUtil::CreateChild($objBoxButtons, "button", "");
		XmlUtil::AddAttribute($nodeWorking, "caption", $caption);
		XmlUtil::AddAttribute($nodeWorking, "name", $name);
		XmlUtil::AddAttribute($nodeWorking, "onclick", $onclick);
	}

	/// <summary>
	/// Add a submit button to a Box Button Object
	/// </summary>
	/// <param name="objBoxButtons">Box Button Object</param>
	/// <param name="name">Name</param>
	/// <param name="caption">Caption</param>
	
	/**
	*@param DOMNode objBoxButtons Box Button Object
	*@param string $name name
	*@param string $caption Caption
	*@desc Add a submit button to a Box Button Object
	*/
	
	public function addSubmit($objBoxButtons, $name, $caption)
	{
		$nodeWorking = XmlUtil::CreateChild($objBoxButtons, "submit", "");
		XmlUtil::AddAttribute($nodeWorking, "caption", $caption);
		XmlUtil::AddAttribute($nodeWorking, "name", $name);
	}

	/// <summary>
	/// Add a reset button to a Box Button Object
	/// </summary>
	/// <param name="objBoxButtons">Box Button Object</param>
	/// <param name="name">Name </param>
	/// <param name="caption">Caption</param>
	/// Parameters: DOMNode objBoxButtons, string name, string caption
	
	/**
	*@param DOMNode $objBoxButtons Box Button Object
	*@param string $name name
	*@param string $caption Caption
	*@desc Add a reset button to a Box Button Object
	*/
	
	public function addReset($objBoxButtons, $name, $caption)
	{
		$nodeWorking = XmlUtil::CreateChild($objBoxButtons, "reset", "");
		XmlUtil::AddAttribute($nodeWorking, "caption", $caption);
		XmlUtil::AddAttribute($nodeWorking, "name", $name);
	}
	//Acrescentado bot�o do tipo FILE
	/// <summary>
	/// Add a Box option to a form Object
	/// </summary>
	/// <param name="objForm">Form Object</param>
	/// <returns>Box Option</returns>
	/// Parameter: DOMNode objForm // return: DOMNode
	
	/**
	*@return DOMNode
	*@param DOMNode $objForm Form Object
	*@desc Add a Box option to a form Object
	*/
	
	public function addLineButtons($objForm, $label="")
	{
		$nodeWorking = XmlUtil::CreateChild($objForm, "buttonsline", "");
		XmlUtil::AddAttribute($nodeWorking, "label", $label);
		return $nodeWorking;
	
	}
	/// <summary>
	/// Add a Button to a Box Button Object
	/// </summary>
	/// <param name="objBoxButtons">Box Button Object</param>
	/// <param name="name">Name</param>
	/// <param name="caption">Caption</param>
	/// <param name="onclick">Onclick javascript</param>
	/// Parameters: DOMNode objBoxButtons, string name, string caption, string onclick
	
	/**
	*@return DOMNode
	*@param DOMNode $objBoxButtons Box Button Object
	*@param string $name Name
	*@param string $caption Caption
	*@param string onclick Onclick javascript
	*@desc Add a Button to a Box Button Object
	*/
	
	public function addFileButton($objBoxButtons, $name)
	{
		$nodeWorking = XmlUtil::CreateChild($objBoxButtons, "filebutton", "");
		XmlUtil::AddAttribute($nodeWorking, "caption", $caption);
		XmlUtil::AddAttribute($nodeWorking, "name", $name);

	}

	//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
	// Menu Itens
	//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
	// Parameter: String
	
	/**
	*@param string $title
	*/
	
	public function setMenuInfo($title)
	{
		$nodeWorking = $this->_nodeGroup->getElementsByTagName("title")->item(0);
		if ($nodeWorking != null)
		{
			$nodeWorking->nodeValue = $title;
		}
	}
	/// Parameters: string xmlID, string title, string summary
	
	/**
	*@param string $xmlID
	*@param string $title
	*@param string $summary
	*/
	
	public function addMenuItem($xmlID, $title, $summary)
	{
		$nodeWorking = XmlUtil::CreateChild($this->_nodeGroup, "page", "");
		XmlUtil::CreateChild($nodeWorking, "id", $xmlID);
		XmlUtil::CreateChild($nodeWorking, "title", $title);
		XmlUtil::CreateChild($nodeWorking, "summary", $summary);
	}

	//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
	// Others
	//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
	public function addJavaScript($javascript, $location = 'up')
	{
		$nodeWorking = XmlUtil::CreateChild($this->_nodePage, "script", "");
		XmlUtil::AddAttribute($nodeWorking, "language", "javascript");
		XmlUtil::AddAttribute($nodeWorking, "location", $location);
		XmlUtil::AddTextNode($nodeWorking, $javascript);
	}
	
	public function addJavaScriptSrc($javascriptSrc, $location = 'up')
	{
		$nodeWorking = XmlUtil::CreateChild($this->_nodePage, "script", "");
		XmlUtil::AddAttribute($nodeWorking, "language", "javascript");
		XmlUtil::AddAttribute($nodeWorking, "location", $location);
		XmlUtil::AddAttribute($nodeWorking, "src", $javascriptSrc);
	}
	
	public function addFlash($movie, $width, $height)
	{
		$nodeWorking = XmlUtil::CreateChild($this->_nodePage, "script", "");
		XmlUtil::AddAttribute($nodeWorking, "movie", $movie);
		XmlUtil::AddAttribute($nodeWorking, "width", (string)$width);
		XmlUtil::AddAttribute($nodeWorking, "height", (string)$height);
	}

	
	//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
	// Get XML
	//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

	/// <summary>
	/// Gets the XML string from PageXml object
	/// </summary>
	/// <returns>XML String</returns>

	/**
	*@return string
	*@desc Gets the XML string from PageXml object
	*/
	
	public function XML()
	{
		self::setModified();
		return $this->_xmlDoc->saveXml();
	}

	/// <summary>
	/// Gets the XMLDocument object from PageXml object
	/// </summary>
	/// <returns>XML String</returns>XmlDocument
	
	/**
	*@return string
	*@desc Gets the XMLDocument object from PageXml object
	*/
	
	public function getDomObject()
	{
		return self::makeDomObject();
	}

	/**
	*@desc Generate page, processing yours childs using the parent.
	*@return DOMDocument
	*/
	public function makeDomObject()
	{
		self::setModified();
		return $this->_xmlDoc;
	}
	
	/// <summary>
	/// Gets the DOMNode root node (<page/>) PageXml object
	/// </summary>
	/// <returns>XML String</returns>DOMNode
	
	/**
	*@return string
	*@desc Gets the DOMNode root node (<page/>) PageXml object
	*/
	
	public function getRootNode()
	{
		return $this->_nodePage;
	}


	/// <summary>
	/// Save XML String to file
	/// </summary>
	/// <param name="xmlFile">XMLFilenameProcessor</param>processor.XMLFilenameProcessor
	
	/**
	*@param XMLFilenameProcessor $xmlFile
	*@desc Save XML String to file
	*/
	
	public function SaveTo($xmlFile)
	{
		$xmlFile->getContext()->getXMLDataBase()->saveDocument($xmlFile->FullQualifiedName(), $this->getDomObject());
	}
	//Parameters: DOMNode objParagraph, string sourceXml, XmlException ex
	
	/**
	*@param DOMNode $objParagraph
	*@param string $sourceXml
	*@param XmlException $ex
	*/
	
	public function AddErrorMessage($objParagraph, $sourceXml, $ex)
	{
		$this->addBold($objParagraph, "Error: ".$ex->getMessage());
		//$this->addCode($objParagraph, line + "\n" + compl);
		$this->addHref($objParagraph, "javascript:history.go(-1)", "Go Back", null);
	}
	
	/**
	* @param IXmlnukeDocumentObject $object
	* @param DOMElement $node
	* @return void
	* @desc Add a Xmlnuke Object in Element PageXml or in page. If $node is null the object target will be the page
	*/
	public function addXmlnukeObject($object, $node = null)
	{
		if (is_null($node)) {
			$node = $this->_nodePage;
		}
		$object->generateObject($node);
	}
}

?>