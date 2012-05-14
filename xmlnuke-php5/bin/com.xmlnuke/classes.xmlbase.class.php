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

class XMLTransform
{
	const ALL = "";
	const IXMLNukeDocumentObject = "1";
	const Model = "2";
}

/**
 * Implements a collection of Xmlnuke Xml Objects. 
 * 
 * @package xmlnuke
 */
class XmlnukeCollection
{
	/**
	 * @var array
	 */
	protected $_items;

	protected $_xmlTransform = XMLTransform::ALL;
	protected $_configTransform = "xmlnuke";
	
	/**
	 * @desc XmlnukeCollection Constructor 
	 */
	public function __construct()
	{
		$this->_items = array();
	}
	
	/**
	 * @desc Add a child in current DocumentObject
	 * @param IXmlnukeDocumentObject $docobj
	 * @return void
 	 */
	public function addXmlnukeObject($docobj)
	{
		if (is_null($docobj))
		{
			throw new XmlNukeObjectException(853, "Parameter is null");
		}
		else if (is_string($docobj))
		{
			$docobj = new XmlnukeText($docobj);
		}
		else if ($docobj == $this)
		{
			throw new XmlNukeObjectException(853, "You are adding to the document a instance from yourself");
		}
		else if (!($docobj instanceof IXmlnukeDocumentObject) && !is_object($docobj))
		{
			throw new XmlNukeObjectException(853, "Object is not a IXmlnukeDocumentObject or Class Model. ");
		}
		$this->_items[] = $docobj;
	}

	/**
	 * @desc Method for process all XMLNukedocumentObjects in array.
	 * @param DOMNode $current
	 * @return void
	 * @internal IXmlnukeDocumentObject $item
	 */
	protected function generatePage($current)
	{
		if (!is_null($this->_items))
		{
			foreach( $this->_items as $item )
			{
				# Prepare
				if ($item instanceof XmlnukeCollection)
				{
					$item->setXMLTransform($this->_xmlTransform);
					$item->setConfigTransform($this->_configTransform);
				}
				
				# Transform
				if (($item instanceof IXmlnukeDocumentObject) && ($this->_xmlTransform != XMLTransform::Model))
					$item->generateObject($current);
				elseif (($item instanceof XmlnukeCollection) && ($this->_xmlTransform != XMLTransform::IXMLNukeDocumentObject))
					$item->generatePage($current);
				elseif ($this->_xmlTransform != XMLTransform::IXMLNukeDocumentObject)
					XmlnukeCollection::CreateObjectFromModel($current, $item, $this->_configTransform);
			}
		}
	}

	
	/**
	 *
	 * @param type $current
	 * @param type $model
	 * @return DOMNode
	 */
	protected static function CreateObjectFromModel($current, $model, $config)
	{
		
		$class = new ReflectionClass(get_class($model));
		preg_match_all('/@(?<param>\S+)\s*(?<value>\S+)?\n/', $class->getDocComment(), $aux);
		$classAttributes = XmlnukeCollection::adjustParams($aux);

		#------------
		# Define Class Attributes
		$_name = $classAttributes["$config:nodename"] != "" ? $classAttributes["$config:nodename"] : get_class($model);
		$_getter = $classAttributes["$config:getter"] != "" ? $classAttributes["$config:getter"] : "get";
		$_propertyPattern = $classAttributes["$config:propertypattern"] != "" ? eval($classAttributes["$config:propertypattern"]) : array('/(\w*)/', '$1');
		$_writeEmpty = $classAttributes["$config:writeempty"] == "true";
		$_docType = $classAttributes["$config:doctype"] != "" ? strtolower($classAttributes["$config:doctype"]) : "xml";
		$_rdfType = XmlnukeCollection::replaceVars($model, $_name, $classAttributes["$config:rdftype"] != "" ? $classAttributes["$config:rdftype"] : "{HOST}/rdf/class/{CLASS}");
		$_rdfAbout = XmlnukeCollection::replaceVars($model, $_name, $classAttributes["$config:rdfabout"] != "" ? $classAttributes["$config:rdfabout"] : "{HOST}/rdf/instance/{CLASS}/{GetID()}");
		$_defaultPrefix = $classAttributes["$config:defaultprefix"] != "" ? $classAttributes["$config:defaultprefix"] . ":" : "";
		$_isRDF = ($_docType == "rdf");
		$_namespace = $classAttributes["$config:namespace"];
		if (!is_array($_namespace) && !empty($_namespace)) $_namespace = array($_namespace);
		
		$nodeRefs = array();
		
		#-----------
		# Setup NameSpaces
		if (is_array($_namespace))
		{
			foreach ($_namespace as $value) 
			{
				$prefix = strtok($value, "!");
				$uri = str_replace($prefix . "!", "", $value);
				XmlUtil::AddNamespaceToDocument($current, $prefix, $uri);
			}
		}
		
		#------------
		# Create Class Node
		if (!$_isRDF)
			$node = XmlUtil::CreateChild($current, $_name);
		else
		{
			XmlUtil::AddNamespaceToDocument($current, "rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
			$node = XmlUtil::CreateChild($current, "rdf:description");
			XmlUtil::AddAttribute($node, "rdf:about", $_rdfAbout);
			$nodeType = XmlUtil::CreateChild($node, "rdf:type");
			XmlUtil::AddAttribute($nodeType, "rdf:resource", $_rdfType);
		}				
				
		#------------
		# Get all properties
		$properties = $class->getProperties( ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PUBLIC );

		if (!is_null($properties))
		{
			foreach ($properties as $prop)
			{
				$propName = $prop->getName();
				$propAttributes = array();

				if ($propName == "_propertyPattern") continue;
				
				# Determine where it located the Property Value --> Getter or inside the property
				if ($prop->isPublic())
				{
					preg_match_all('/@(?<param>\S+)\s*(?<value>\S+)?\n/', $prop->getDocComment(), $aux);
					$propAttributes = XmlnukeCollection::adjustParams($aux);
					$propValue = $prop->getValue($model);
				}
				else
				{
					// Remove Prefix "_" from Property Name to find a value
					if ($propName[0] == "_")
						$propName = substr($propName, 1);

					$methodName = $_getter . ucfirst(preg_replace($_propertyPattern[0], $_propertyPattern[1], $propName));
					if ($class->hasMethod($methodName))
					{
						$method = $class->getMethod($methodName);						
						preg_match_all('/@(?<param>\S+)\s*(?<value>\S+)?\n/', $method->getDocComment(), $aux);
						$propAttributes = XmlnukeCollection::adjustParams($aux);
						$propValue = $method->invoke($model, "");
					}
					else
						continue;
				}
				
				# Define Properties
				$_ignore = array_key_exists("$config:ignore", $propAttributes);
				$_propName = $propAttributes["$config:nodename"] != "" ? $propAttributes["$config:nodename"] : $propName;
				if (strpos($_propName, ":") === false) $_propName = $_defaultPrefix . $_propName;
				$_attributeOf = $_isRDF ? "" : $propAttributes["$config:isattributeof"];
				$_isClassAttr = $_isRDF ? false : array_key_exists("$config:isclassattribute", $propAttributes);
				
				if ($_ignore) continue;
		
				# Process the Property Value
				$used = null;
				if (is_object($propValue))
				{
					$used = XmlnukeCollection::CreateObjectFromModel($node, $propValue, $config);
				}
				elseif (is_array ($propValue))
				{
					$used = XmlUtil::CreateChild($node, $_propName);
					foreach ($propValue as $key=>$value)
					{
						if (is_object($value))
							XmlnukeCollection::CreateObjectFromModel($used, $value, $config);
						else
						{
							if (is_numeric($key))
								$key = "item";
							XmlUtil::CreateChild ($used, $key, $value);
						}
					}
				}
				else if (($propValue != "") || ($_writeEmpty))
				{
					if ($_isClassAttr)
						XmlUtil::AddAttribute ($node, $_propName, $propValue);
					elseif (($_attributeOf != "") && (array_key_exists($_attributeOf, $nodeRefs)))
						XmlUtil::AddAttribute ($nodeRefs[$_attributeOf], $_propName, $propValue);
					else
						$used = XmlUtil::CreateChild($node, $_propName, $propValue);
				}
				
				# Save Reference for "isAttributeOf" attribute.
				if ($used != null)
				{
					$nodeRefs[$propName] = $used;
				}
			}
		}
		
		return $node;
	}
	
	protected static function replaceVars($model, $name, $text)
	{
		$context = Context::getInstance();
		
		# Host
		$host = $context->UrlBase() != "" ? $context->UrlBase() : ($context->Value("SERVER_PORT") == 443 ? "https://" : "http://") . $context->Value("HTTP_HOST");
		
		# Replace Part One
		$text = preg_replace(array("/\{[hH][oO][sS][tT]\}/", "/\{[cC][lL][aA][sS][sS]\}/"), array($host, $name), $text);

		if(preg_match('/(\{(\S+)\})/', $text, &$matches))
		{
			$class = new ReflectionClass(get_class($model));
			$method = str_replace("()", "", $matches[2]);
			$value = spl_object_hash($model);
			if ($class->hasMethod($method))
			{
				try
				{
					$value = $model->$method();
				}
				catch (Exception $ex)
				{
					$value = "***$value***";
				}
			}
			$text = preg_replace('/(\{(\S+)\})/', $value, $text);
		}
		
		return $text;
		
	}
	
	protected static function adjustParams($arr)
	{
		$count = count($arr[0]);
		$result = array();
		
		for ($i=0;$i<$count;$i++)
		{
			$key = strtolower($arr["param"][$i]);
			$value = $arr["value"][$i];
			
			if (!array_key_exists($key, $result))
				$result[$key] = $value;
			elseif (is_array($result[$key]))
				$result[$key][] = $value;
			else
				$result[$key] = array($result[$key], $value);
		}
		
		return $result;
	}
	
	/**
	 * Define WHAT objects the system will process.
	 * @param XMLTransform $method 
	 */
	function setXMLTransform($method)
	{
		$this->_xmlTransform = $method;
	}
	
	/**
	 * Define WHAT prefix in comment will be used
	 * @param string $value 
	 */
	function setConfigTransform($value)
	{
		$this->_xmlTransform = $value;
	}
	
}

/**
 * @package xmlnuke
 */
class Menus
{
	public $id;
	public $title;
	public $summary;
	public $icon;
}

/**
 * @package xmlnuke
 */
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
 * @package xmlnuke
 */
class Script
{
	public $source;
	public $file;
	public $location;
}

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
	 * Returns a IXmlnukeDocument. 
	 * 
	 * In the newer versions you can simply return the object
	 * 
	 * @deprecated since version 3.0
	 * @package xmlnuke
	 * @return IXmlnukeDocument
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

/**
 * @package xmlnuke
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
 * @package xmlnuke
 */
interface IXmlnukeDocument
{
	/**
	* @return DOMDocument
	*/
	public function makeDomObject();
}
	
/**
 * @package xmlnuke
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
