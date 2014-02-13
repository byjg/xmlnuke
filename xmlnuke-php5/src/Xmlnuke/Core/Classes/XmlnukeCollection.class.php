<?php

/*
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
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
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 */

/**
 * Implements a collection of Xmlnuke Xml Objects.
 *
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Classes;

use DOMNode;
use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Enum\XMLTransform;
use Xmlnuke\Util\XmlUtil;

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
			throw new InvalidArgumentException("Parameter is null", 853);
		}
		else if (is_string($docobj))
		{
			$docobj = new XmlnukeText($docobj);
		}
		else if ($docobj == $this)
		{
			throw new InvalidArgumentException("You are adding to the document a instance from yourself", 853);
		}
		else if (!($docobj instanceof IXmlnukeDocumentObject) && !is_object($docobj))
		{
			throw new InvalidArgumentException("Object is not a IXmlnukeDocumentObject or Class Model. ", 853);
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
				if ($item instanceof \Xmlnuke\Core\AnyDataset\IIterator)
				{
					foreach ($item as $singleRow)
					{
						XmlUtil::AddNodeFromNode($current, $singleRow->getDomObject());
					}
				}
				elseif ($item instanceof \Xmlnuke\Core\Locale\LanguageCollection)
				{
					$keys = $item->getCollection();
					$l10n = XmlUtil::CreateChild($current, "l10n");
					foreach ($keys as $key=>$value)
					{
						XmlUtil::CreateChild($l10n, $key, $value);
					}
				}
				elseif (($item instanceof IXmlnukeDocumentObject) && ($this->_xmlTransform != XMLTransform::Model))
				{
					$item->generateObject($current);
				}
				elseif (($item instanceof XmlnukeCollection) && ($this->_xmlTransform != XMLTransform::IXMLNukeDocumentObject))
				{
					$item->generatePage($current);
				}
				elseif (!($item instanceof IXmlnukeDocumentObject) && ($this->_xmlTransform != XMLTransform::IXMLNukeDocumentObject))
				{
					XmlnukeCollection::CreateObjectFromModel($current, $item, $this->_configTransform);
				}
			}
		}
	}


	/**
	 *
	 * @param type $current
	 * @param type $model
	 * @return DOMNode
	 */
	protected static function CreateObjectFromModel($current, $model, $config, $forcePropName = "")
	{
		if ($model instanceof \Xmlnuke\Core\AnyDataset\IIterator)
		{
			foreach ($model as $singleRow)
			{
				XmlUtil::AddNodeFromNode($current, $singleRow->getDomObject());
			}
			return $current;
		}

		$class = new ReflectionClass($model);
		preg_match_all('/@(?P<param>\S+)\s*(?P<value>\S+)?\r?\n/', $class->getDocComment(), $aux);
		$classAttributes = XmlnukeCollection::adjustParams($aux);

		#------------
		# Define Class Attributes
		$_name = ($forcePropName != "" ? $forcePropName : (isset($classAttributes["$config:nodename"]) ? $classAttributes["$config:nodename"] : get_class($model)));
		$_getter = isset($classAttributes["$config:getter"]) ? $classAttributes["$config:getter"] : "get";
		$_propertyPattern = isset($classAttributes["$config:propertypattern"]) ? eval($classAttributes["$config:propertypattern"]) : array('/([^a-zA-Z0-9])/', '');
		$_writeEmpty = (isset($classAttributes["$config:writeempty"]) ? $classAttributes["$config:writeempty"] : "false") == "true";
		$_docType = isset($classAttributes["$config:doctype"]) ? strtolower($classAttributes["$config:doctype"]) : "xml";
		$_rdfType = XmlnukeCollection::replaceVars($model, $_name, isset($classAttributes["$config:rdftype"]) ? $classAttributes["$config:rdftype"] : "{HOST}/rdf/class/{CLASS}");
		$_rdfAbout = XmlnukeCollection::replaceVars($model, $_name, isset($classAttributes["$config:rdfabout"]) ? $classAttributes["$config:rdfabout"] : "{HOST}/rdf/instance/{CLASS}/{GetID()}");
		$_defaultPrefix = isset($classAttributes["$config:defaultprefix"]) ? $classAttributes["$config:defaultprefix"] . ":" : "";
		$_isRDF = ($_docType == "rdf");
		$_ignoreAllClass = array_key_exists("$config:ignore", $classAttributes);
		$_namespace = isset($classAttributes["$config:namespace"]) ? $classAttributes["$config:namespace"] : "";
		$_dontCreateClassNode = array_key_exists("$config:dontcreatenode", $classAttributes);
		if (!is_array($_namespace) && !empty($_namespace)) $_namespace = array($_namespace);

		if ($_ignoreAllClass)
			return $current;

		$nodeRefs = array();

		#-----------
		# Setup NameSpaces
		if (is_array($_namespace))
		{
			foreach ($_namespace as $value)
			{
				$prefix = strtok($value, "!");
				$uri = str_replace($prefix . "!", "", $value);
				XmlUtil::AddNamespaceToDocument($current, $prefix, XmlnukeCollection::replaceVars($model, $_name, $uri));
			}
		}

		#------------
		# Create Class Node
		if ($_dontCreateClassNode || $model instanceof \stdClass)
			$node = $current;
		else
		{
			if (!$_isRDF)
				$node = XmlUtil::CreateChild($current, $_name);
			else
			{
				XmlUtil::AddNamespaceToDocument($current, "rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
				$node = XmlUtil::CreateChild($current, "rdf:Description");
				XmlUtil::AddAttribute($node, "rdf:about", $_rdfAbout);
				$nodeType = XmlUtil::CreateChild($node, "rdf:type");
				XmlUtil::AddAttribute($nodeType, "rdf:resource", $_rdfType);
			}
		}

		#------------
		# Get all properties
		if ($model instanceof \stdClass)
			$properties = get_object_vars ($model);
		else
			$properties = $class->getProperties( ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PUBLIC );

		if (!is_null($properties))
		{
			foreach ($properties as $keyProp => $prop)
			{
				$propName = ($prop instanceof ReflectionProperty ? $prop->getName() : $keyProp);
				$propAttributes = array();

				if ($propName == "_propertyPattern") continue;

				# Determine where it located the Property Value --> Getter or inside the property
				if (!($prop instanceof ReflectionProperty) || $prop->isPublic())
				{
					preg_match_all('/@(?<param>\S+)\s*(?<value>\S+)?\n/', ($prop instanceof ReflectionProperty ? $prop->getDocComment() : ""), $aux);
					$propAttributes = XmlnukeCollection::adjustParams($aux);
					$propValue = ($prop instanceof ReflectionProperty ? $prop->getValue($model) : $prop);
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
						preg_match_all('/@(?<param>\S+)\s*(?<value>\S+)?\r?\n/', $method->getDocComment(), $aux);
						$propAttributes = XmlnukeCollection::adjustParams($aux);
						$propValue = $method->invoke($model, "");
					}
					else
						continue;
				}

				# Define Properties
				$_ignore = array_key_exists("$config:ignore", $propAttributes);
				$_propName = isset($propAttributes["$config:nodename"]) ? $propAttributes["$config:nodename"] : $propName;
				if (strpos($_propName, ":") === false) $_propName = $_defaultPrefix . $_propName;
				$_attributeOf = $_isRDF ? "" : (isset($propAttributes["$config:isattributeof"]) ? $propAttributes["$config:isattributeof"] : "");
				$_isBlankNode = $_isRDF ? (isset($propAttributes["$config:isblanknode"]) ? $propAttributes["$config:isblanknode"] : "") : "";
				$_isResourceUri = $_isRDF && array_key_exists("$config:isresourceuri", $propAttributes); // Valid Only Inside BlankNode
				$_isClassAttr = $_isRDF ? false : array_key_exists("$config:isclassattribute", $propAttributes);
				$_dontCreatePropNode = array_key_exists("$config:dontcreatenode", $propAttributes);

				if ($_ignore) continue;

				# Process the Property Value
				$used = null;
				if (is_object($propValue))
				{
					if ($_dontCreatePropNode)
						$nodeUsed = $node;
					else
						$nodeUsed = XmlUtil::CreateChild($node, $_propName);

					$forceName = isset($propAttributes["$config:dontcreatenode"]) ? $propAttributes["$config:dontcreatenode"] : "";
					$used = XmlnukeCollection::CreateObjectFromModel($nodeUsed, $propValue, $config, $forceName);
				}
				elseif (is_array ($propValue))
				{
					if ($_dontCreatePropNode)
						$nodeUsed = $node;
					else
						$nodeUsed = $used = XmlUtil::CreateChild($node, $_propName);

					$forceName = isset($propAttributes["$config:dontcreatenode"]) ? $propAttributes["$config:dontcreatenode"] : "";
					foreach ($propValue as $key=>$value)
					{
						if (is_object($value))
							XmlnukeCollection::CreateObjectFromModel($nodeUsed, $value, $config, $forceName);
						else
						{
							if (is_numeric($key))
								$key = $forceName != "" ? $forceName : "item";
							XmlUtil::CreateChild ($nodeUsed, $key, $value);
						}
					}
				}
				else if (($propValue != "") || ($_writeEmpty))
				{
					if ($_isClassAttr)
						XmlUtil::AddAttribute ($node, $_propName, $propValue);
					elseif ($_isBlankNode != "")
					{
						if (!array_key_exists($_isBlankNode, $nodeRefs))
						{
							$nodeRefs[$_isBlankNode] = XmlUtil::CreateChild($node, $_isBlankNode);
							XmlUtil::AddAttribute($nodeRefs[$_isBlankNode], "rdf:parseType", "Resource");
						}

						if ($_isResourceUri)
						{
							$blankNodeType = XmlUtil::CreateChild($nodeRefs[$_isBlankNode], "rdf:type");
							XmlUtil::AddAttribute($blankNodeType, "rdf:resource", $propValue);
						}
						else
						{
							XmlUtil::CreateChild($nodeRefs[$_isBlankNode], $_propName, $propValue);
						}
					}
					elseif (($_attributeOf != "") && (array_key_exists($_attributeOf, $nodeRefs)))
						XmlUtil::AddAttribute ($nodeRefs[$_attributeOf], $_propName, $propValue);
					elseif ((preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $propValue)) && $_isRDF)
					{
						$used = XmlUtil::CreateChild($node, $_propName);
						XmlUtil::AddAttribute($used, "rdf:resource", $propValue);
					}
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
		$host = $context->UrlBase() != "" ? $context->UrlBase() : ($context->get("SERVER_PORT") == 443 ? "https://" : "http://") . $context->get("HTTP_HOST");

		# Replace Part One
		$text = preg_replace(array("/\{[hH][oO][sS][tT]\}/", "/\{[cC][lL][aA][sS][sS]\}/"), array($host, $name), $text);

		if(preg_match('/(\{(\S+)\})/', $text, $matches))
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
		$this->_configTransform = $value;
	}

}

?>