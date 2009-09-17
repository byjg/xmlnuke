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
*Abstract class. Base implementations for all XML tags it can contain another XML tags.
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlnukeCollection
{
	/**
	@var array
	*/
	protected $_items;

	/**
	*@desc XmlnukeCollection Constructor 
	*/
	public function XmlnukeCollection()
	{
		$this->_items = array();
	}
	
	/**
	*@desc Add a child in current DocumentObject
	*@param IXmlnukeDocumentObject $docobj
	*@return void
	*/
	public function addXmlnukeObject($docobj)
	{
		if (is_null($docobj) || !($docobj instanceof IXmlnukeDocumentObject)) {
			throw new XmlNukeObjectException(853, "Object is null or not is IXmlnukeDocumentObject. Found object type: " . get_class($docobj));
		}
		$this->_items[] = $docobj;
	}

	/**
	*@desc Method for process all XMLNukedocumentObjects in array.
	*@param DOMNode $current
	*@return void
	*@internal IXmlnukeDocumentObject $item
	*/
	protected function generatePage($current)
	{
		if (!is_null($this->_items))
		{
			foreach( $this->_items as $item )
			{
				$item->generateObject($current);
			}
		}
	}
}

/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class Menus
{
	public $id;
	public $title;
	public $summary;
	public $icon;
}

class MenuGroup
{
	public $menuTitle;
	/**
	 * Enter description here...
	 *
	 * @var Menus[]
	 */
	public $menus;
}

/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class Script
{
	public $source;
	public $file;
	public $location;
}

/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlnukeDocument extends XmlnukeCollection implements IXmlnukeDocument 
{
	/**
	@var string
	*/
	protected $_pageTitle = "XmlNuke Page";
	/**
	@var string
	*/
	protected $_abstract = "";
	/**
	@var string
	*/
	protected $_groupKeyword;
	/**
	@var string
	*/
	protected $_keyword;
	/**
	@var DateTime
	*/
	protected  $_created;
	/**
	@var MenuGroup
	*/
	protected  $_menuGroup;
	
	/**
	@var string
	*/
	protected  $_scripts;
	
	/**
	 * @var array
	 */
	protected $_metaTag = array();
	
	/**
	 * @var bool
	 */
	protected $_waitLoading = false;
	
	/**
	 * @var bool
	 */
	protected $_disableButtonsOnSubmit = true; 
	
	/**
	*@desc XmlnukeDocument constructor
	*@param string $pageTitle
	*@param string $desc
	*/
	public function XmlnukeDocument($pageTitle = "", $desc = "")
	{
		$this->_created = date("Y-m-d H:m:s");
		parent::XmlnukeCollection();
		$this->_pageTitle = $pageTitle;
		$this->_abstract = $desc;
		
		$this->_keyword = "xmlnuke";
		$this->_groupKeyword = "";
		
		$this->_menuGroup = array();
		$this->addMenuGroup("Menu", "__DEFAULT__");
	}	
	
	/**
	*@desc add a item to menu
	*@param string $title
	*@param string $desc
	*@param string $desc
	*@return void
	*/
	public function addMenuItem($id, $title, $summary, $group = "__DEFAULT__", $icon = "") 
	{ 
		$m = new Menus();
		$m->id= $id; 
		$m->title = $title; 
		$m->summary = $summary; 
		$m->icon = $icon;
		$this->_menuGroup[$group]->menus[] = $m;
	} 
	
	/**
	*@desc set Menu Title
	*@param string 
	*@return void
	*/
	public function setMenuTitle($title, $group = "__DEFAULT__") 
	{ 
		$this->_menuGroup[$group]->menuTitle = $title; 
	} 
	
	public function addMenuGroup($title, $group)
	{
		$menuGroup = new MenuGroup();
		$menuGroup->menuTitle = $title; 
		$menuGroup->menus = array();
		//
		$this->_menuGroup[$group] = $menuGroup;
	}
	
	/**
	 * Add a Meta tag in page/meta
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function addMetaTag($name, $value)
	{
		$this->_metaTag[$name] = $value;
	}

	
	public function setWaitLoading($value)
	{
		$this->_waitLoading = $value;
	}
	public function getWaitLoading()
	{
		return $this->_waitLoading;
	}
	
	
	public function setDisableButtonOnSubmit($value)
	{
		$this->_disableButtonsOnSubmit = $value;
	}
	public function getDisableButtonOnSubmit()
	{
		return $this->_disableButtonsOnSubmit;
	}
	

	/**
	 * Add a JavaScript method to a JavaScript object.
	 * 
	 * Some examples:
	 * 
	 * addJavaScriptMethod("a", "click", "alert('clicked on a hiperlink');");
	 * addJavaScriptMethod("#myID", "blur", "alert('blur a ID object in JavaScript');");
	 * 
	 * @param string $jsObject
	 * @param string $jsMethod
	 * @param string $jsSource
	 * @param string $jsParameters
	 * @return void
	 */
	public function addJavaScriptMethod($jsObject, $jsMethod, $jsSource, $jsParameters = "")
	{
		$jsEventSource = 
			"$(function() { \n" . 
			"	$('$jsObject').$jsMethod(function($jsParameters) { \n" .
			"		$jsSource \n" .
			"	}); \n" .
			"});\n\n";
		$this->addJavaScriptSource($jsEventSource, false);
	}
	
	/**
	 * Add a JavaScript attribute to a JavaScript object.
	 * 
	 * Some examples:
	 * 
	 * addJavaScriptMethod("#myID", "someAttr", array("param"=>"'teste'"));
	 * 
	 * @param string $jsObject
	 * @param string $jsAttribute
	 * @param array $attrParam
	 * @return void
	 */
	public function addJavaScriptAttribute($jsObject, $jsAttrName, $attrParam)
	{
		if (!is_array($attrParam))
		{
			$attrParam = array($attrParam);
		}
		
		$jsEventSource = 
			"$(function() { \n" . 
			"   $('$jsObject').$jsAttrName({ \n";
		
		$first = true;
		foreach ($attrParam as $key=>$value)
		{
			$jsEventSource .= (!$first ? ",\n" : "") . "      " . (!is_numeric($key) ? "$key: " : "" ) . $value;
			$first = false;
		}
		
		$jsEventSource .= 
			"\n   }); \n" .
			"});\n\n";
		
		$this->addJavaScriptSource($jsEventSource, false);
	}
	
	/**
	*@desc Generate page, processing yours childs using the parent.
	*@return DOMDocument
	*/
	public function makeDomObject()
	{
		$created = date("d/M/y h:m:s");
		$createdTimeStamp = microtime(true);
		
		$xmlDoc = XmlUtil::CreateXmlDocument();

		// Create the First first NODE ELEMENT!
		$nodePage = $xmlDoc->createElement("page");
		$xmlDoc->appendChild($nodePage);

		// Create the META node
		$nodeMeta = XmlUtil::CreateChild($nodePage, "meta", "");
		XmlUtil::CreateChild($nodeMeta, "title", $this->_pageTitle);
		XmlUtil::CreateChild($nodeMeta, "abstract", $this->_abstract);
		XmlUtil::CreateChild($nodeMeta, "keyword", $this->_keyword);
		XmlUtil::CreateChild($nodeMeta, "groupkeyword", $this->_groupKeyword);
		foreach ($this->_metaTag as $key=>$value)
		{
			XmlUtil::CreateChild($nodeMeta, $key, $value);
		}
		
		// Create MENU (if exists some elements in menu).
		foreach ($this->_menuGroup as $key=>$menuGroup) 
		{
			if (sizeof($menuGroup->menus) > 0)
			{
				$nodeGroup = XmlUtil::CreateChild($nodePage, "group", "");
				XmlUtil::CreateChild($nodeGroup, "id", $key);
				XmlUtil::CreateChild($nodeGroup, "title", $menuGroup->menuTitle);
				XmlUtil::CreateChild($nodeGroup, "keyword", "all");
				
				foreach($menuGroup->menus as $item) 
				{ 
					$nodeWorking = XmlUtil::CreateChild($nodeGroup, "page", "");
					XmlUtil::CreateChild($nodeWorking, "id", $item->id);
					XmlUtil::CreateChild($nodeWorking, "title", $item->title);
					XmlUtil::CreateChild($nodeWorking, "summary", $item->summary);
					if ($item->icon != "")
					{
						XmlUtil::CreateChild($nodeWorking, "icon", $item->icon);
					}
				} 
			}
		}

		// Add Custom JS
		if (!$this->_disableButtonsOnSubmit)
		{
			$this->addJavaScriptSource("var XMLNUKE_DISABLEBUTTON = false;\n", false);
		}
		if ($this->_waitLoading)
		{
			$this->addJavaScriptSource("var XMLNUKE_WAITLOADING = true;\n", false);
		}
		
		// Generate Scripts
		if(!is_null($this->_scripts))
		{
			foreach($this->_scripts as $script) 
			{		
				$nodeWorking = XmlUtil::CreateChild($nodePage, "script", "");
				XmlUtil::AddAttribute($nodeWorking, "language", "javascript");
				if(!is_null($script->source))
					XmlUtil::AddTextNode($nodeWorking, $script->source, true);
				if(!is_null($script->file))
					XmlUtil::AddAttribute($nodeWorking, "src", $script->file);
				
				XmlUtil::AddAttribute($nodeWorking, "location", $script->location);
			}
		}
		
		// Process ALL XmlnukeDocumentObject existing in Collection.
		//----------------------------------------------------------
		parent::generatePage($nodePage);
		//----------------------------------------------------------

		// Finalize the Create Page Execution
		XmlUtil::CreateChild($nodeMeta, "created", $created);
		XmlUtil::CreateChild($nodeMeta, "modified", date("d/M/y h:m:s"));
		$elapsed = microtime(true)-$createdTimeStamp;
		XmlUtil::CreateChild($nodeMeta, "timeelapsed", intval($elapsed/3600) . ":" . intval($elapsed/60)%60 . ":" . $elapsed%60 . "." . substr(intval((($elapsed - intval($elapsed))*1000))/1000, 2) );
		XmlUtil::CreateChild($nodeMeta, "timeelapsedsec", $elapsed );
		
		return $xmlDoc;
	}
	
	/**
	*@desc DEPRECATED - For compatibility reason.
	*@return IXmlnukeDocument 
	*/
	public function generatePage()
	{
		return $this;
	}

	/**
	*@desc Set the xml metadata title
	*@param string $value
	*@return void
	*/
	public function setPageTitle($value)
	{
		$this->_pageTitle = $value;
	}
	
	/**
	*@desc Get the xml metadata title
	*@return string
	*/
	public function getPageTitle()
	{
		return $this->_pageTitle;
	}
	
	/**
	*@desc Set the xml metadata abstract
	*@param string $value
	*@return void
	*/
	public function setAbstract($value)
	{
		$this->_abstract = $value;
	}
	
	/**
	*@desc Get the xml metadata abstract
	*@return string
	*/
	public function getAbstract()
	{
		return $this->_abstract;
	}

	/**
	*@desc Set the xml metadata Keyword (used to list menus)
	*@param string $value
	*@return void
	*/
	public function setKeyword($value)
	{
		$this->_keyword = $value;
	}
	
	/**
	*@desc Get the xml metadata groupkeyword (used to list menus)
	*@return string
	*/
	public function getKeyword()
	{
		return $this->_keyword;
	}

	/**
	*@desc Set the xml metadata groupkeyword (used to list menus)
	*@param string $value
	*@return void
	*/
	public function setGroupKeyword($value)
	{
		$this->_groupKeyword = $value;
	}
	
	/**
	*@desc Get the xml metadata groupkeyword (used to list menus)
	*@return string
	*/
	public function getGroupKeyword()
	{
		return $this->_groupKeyword;
	}
	
	/**
	*@desc Get the xml metadata datetime created
	*@return string
	*/
	public function getCreated()
	{
		return $this->_created;
	}
	
		
	/**
	*@desc add a javaScript Code
	*@param string $source
	*@return void
	*/
	public function addJavaScriptSource($source, $compact = false, $location = "up")
	{		
		$s = new Script();
		if ($compact)
		{
			$s->source= $this->CompactJs($source); 
		}
		else 
		{
			$s->source= $source; 
		}
		$s->file = null; 
		$s->location = $location; 
		$this->_scripts[] = $s; 
	}	

	/**
	*@desc add a javaScript Code
	*@param string $reference JavaScript file name
	*@return void
	*/
	public function addJavaScriptReference($reference, $location = "up")
	{		
		$s = new Script();
		$s->source= null; 
		$s->file = $reference; 
		$s->location = $location; 
		$this->_scripts[] = $s; 
	}	
	
	/**
	 * Based on 
	 *
	 * @Author: Hannes Dorn
     * @Company: IBIT.at
     * @Homepage: http://www.ibit.at
     * @Email: hannes.dorn@ibit.at
     * @Comment: Original compact PHP code. Changes by João Gilberto Magalhães (ByJG) to Work on JavaScript.
	 * @param string $sText
	 * @return unknown
	 */
	protected function CompactJs( $sText )
	{
	    $sBuffer = "";
	    $i = 0;
	    $iStop = strlen($sText);
	
	    // Compact and Copy PHP Source Code.
	    $sChar = '';
	    $sLast = '';
	    $sWanted = '';
	    $fEscape = false;
	    for( $i = $i; $i < $iStop; $i++ )
	    {
	        $sLast = $sChar;
	        $sChar = substr( $sText, $i, 1 );
	
	        // \ in a string marks possible an escape sequence
	        if ( $sChar == '\\' )
	            // are we in a string?
	            if ( $sWanted == '"' || $sWanted == "'" )
	                // if we are not in an escape sequence, turn it on
	                // if we are in an escape sequence, turn it off
	                $fEscape = !$fEscape;
	
	        // " marks start or end of a string
	        if ( $sChar == '"' && !$fEscape )
	            if ( $sWanted == '' )
	                $sWanted = '"';
	            else
	                if ( $sWanted == '"' )
	                    $sWanted = '';
	
	        // ' marks start or end of a string
	        if ( $sChar == "'" && !$fEscape )
	            if ( $sWanted == '' )
	                $sWanted = "'";
	            else
	                if ( $sWanted == "'" )
	                    $sWanted = '';
	
	        // // marks start of a comment
	        if ( $sChar == '/' && $sWanted == '' )
	            if ( substr( $sText, $i + 1, 1 ) == '/' )
	            {
	                $sWanted = "\n";
	                $i++;
	                continue;
	            }
	
	        // \n marks possible end of comment
	        if ( $sChar == "\n" && $sWanted == "\n" )
	        {
	            $sWanted = '';
	            continue;
	        }
	
	        // /* marks start of a comment
	        if ( $sChar == '/' && $sWanted == '' )
	            if ( substr( $sText, $i + 1, 1 ) == '*' )
	            {
	                $sWanted = "*/";
	                $i++;
	                continue;
	            }
	
	        // */ marks possible end of comment
	        if ( $sChar == '*' && $sWanted == '*/' )
	            if ( substr( $sText, $i + 1, 1 ) == '/' )
	            {
	                $sWanted = '';
	                $i++;
	                continue;
	            }
	
	        // if we have a tab or a crlf replace it with a blank and continue if we had one recently
	        if ( ( $sChar == "\t" || $sChar == "\n" || $sChar == "\r" ) && $sWanted == '' )
	        {
	            $sChar = ' ';
	            if ( $sLast == ' ' )
	                continue;
	        }
	
	        // skip blanks only if previous char was a blank or nothing
	        if ( $sChar == ' ' && ( $sLast == ' ' || $sLast == '' ) && $sWanted == '' )
	            continue;
	
	        // add char to buffer if we are not inside a comment
	        if ( $sWanted == '' || $sWanted == '"' || $sWanted == "'" )
	            $sBuffer .= $sChar;
	
	        // if we had an escape sequence and the actual char isn't the escape char, cancel escape sequence...
	        // since we are only interested in escape sequences of \' and \".
	        if ( $fEscape && $sChar != '\\' )
	            $fEscape = false;
	    }
	
	    // Copy Rest
	    $sBuffer .= substr( $sText, $iStop );
	
	    return( $sBuffer );
	}	
}

/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
interface IXmlnukeDocumentObject
{
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current);
}


/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
interface IXmlnukeDocument
{
	/**
	* @return DOMDocument
	*/
	public function makeDomObject();
}
	
/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlnukeDocumentObject implements IXmlnukeDocumentObject
{
	public function XmlnukeDocumentObject(){}
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current){}
}

?>
