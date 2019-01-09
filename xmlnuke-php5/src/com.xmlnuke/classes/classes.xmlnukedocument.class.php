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

use ByJG\Util\XmlUtil;

/**
 * Implements a XMLNuke Document. 
 * 
 * Any module in XMLNuke must return a IXmlnukeDocument object. This class is a concrete implementaion of the interface. 
 * 
 * You can implement your own document, like HumanML for example and use this in your module. 
 * 
 * @package xmlnuke
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
	public function __construct($pageTitle = "", $desc = "")
	{
		$this->_created = date("Y-m-d H:m:s");
		parent::__construct();
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
		
		$xmlDoc = XmlUtil::createXmlDocument();

		// Create the First first NODE ELEMENT!
		$nodePage = $xmlDoc->createElement("page");
		$xmlDoc->appendChild($nodePage);

		// Create the META node
		$nodeMeta = XmlUtil::createChild($nodePage, "meta", "");
		XmlUtil::createChild($nodeMeta, "title", $this->_pageTitle);
		XmlUtil::createChild($nodeMeta, "abstract", $this->_abstract);
		XmlUtil::createChild($nodeMeta, "keyword", $this->_keyword);
		XmlUtil::createChild($nodeMeta, "groupkeyword", $this->_groupKeyword);
		foreach ($this->_metaTag as $key=>$value)
		{
			XmlUtil::createChild($nodeMeta, $key, $value);
		}
		
		// Create MENU (if exists some elements in menu).
		foreach ($this->_menuGroup as $key=>$menuGroup) 
		{
			if (sizeof($menuGroup->menus) > 0)
			{
				$nodeGroup = XmlUtil::createChild($nodePage, "group", "");
				XmlUtil::createChild($nodeGroup, "id", $key);
				XmlUtil::createChild($nodeGroup, "title", $menuGroup->menuTitle);
				XmlUtil::createChild($nodeGroup, "keyword", "all");

				foreach($menuGroup->menus as $item)
				{ 
					$nodeWorking = XmlUtil::createChild($nodeGroup, "page", "");
					XmlUtil::createChild($nodeWorking, "id", $item->id);
					XmlUtil::createChild($nodeWorking, "title", $item->title);
					XmlUtil::createChild($nodeWorking, "summary", $item->summary);
					if ($item->icon != "")
					{
						XmlUtil::createChild($nodeWorking, "icon", $item->icon);
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
				$nodeWorking = XmlUtil::createChild($nodePage, "script", "");
				XmlUtil::addAttribute($nodeWorking, "language", "javascript");
				if(!is_null($script->source))
					XmlUtil::addTextNode($nodeWorking, $script->source, true);
				if(!is_null($script->file))
					XmlUtil::addAttribute($nodeWorking, "src", $script->file);
				
				XmlUtil::addAttribute($nodeWorking, "location", $script->location);
			}
		}
		
		// Process ALL XmlnukeDocumentObject existing in Collection.
		//----------------------------------------------------------
		parent::generatePage($nodePage);
		//----------------------------------------------------------

		// Finalize the Create Page Execution
		XmlUtil::createChild($nodeMeta, "created", $created);
		XmlUtil::createChild($nodeMeta, "modified", date("d/M/y h:m:s"));
		$elapsed = microtime(true)-$createdTimeStamp;
		XmlUtil::createChild($nodeMeta, "timeelapsed", intval($elapsed/3600) . ":" . intval($elapsed/60)%60 . ":" . $elapsed%60 . "." . substr(intval((($elapsed - intval($elapsed))*1000))/1000, 2) );
		XmlUtil::createChild($nodeMeta, "timeelapsedsec", $elapsed );
		
		return $xmlDoc;
	}
	
	/**
	 * Returns a IXmlnukeDocument. 
	 * 
	 * In the newer versions you can simply return the object
	 * 
	 * @deprecated since version 3.0
	 * @package xmlnuke
	 * @return IXmlnukeDocument
	 */
	public function generatePage($obj = null)
	{
		if ($obj != null)
			throw  new InvalidArgumentException("You do not need pass an argument for XmlnukeDocument generatePage()");

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
	 * A 3rd party implementation for compact a javascript. 
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

?>
