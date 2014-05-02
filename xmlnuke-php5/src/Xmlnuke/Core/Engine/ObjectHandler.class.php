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
namespace Xmlnuke\Core\Engine;

use DOMNode;
use Exception;
use ReflectionClass;
use ReflectionProperty;
use stdClass;
use Xmlnuke\Core\AnyDataset\IIterator;
use Xmlnuke\Core\Classes\BaseSingleton;
use Xmlnuke\Core\Classes\XmlnukeCollection;
use Xmlnuke\Util\XmlUtil;


class ObjectHandler 
{
	const ClassRefl = "ClassRefl";
	const ClassName = "ClassName";
	const ClassGetter = "ClassGetter";
	const ClassPropertyPattern = "ClassPropertyPattern";
	const ClassWriteEmpty = "ClassWriteEmpty";
	const ClassDocType = "ClassDocType";
	const ClassRdfType = "ClassRdfType";
	const ClassRdfAbout = "ClassRdfAbout";
	const ClassDefaultPrefix = "ClassDefaultPrefix";
	const ClassIsRDF = "ClassIsRDF";
	const ClassIgnoreAllClass = "ClassIgnoreAllClass";
	const ClassNamespace = "ClassNamespace";
	const ClassDontCreateClassNode = "ClassDontCreateClassNode";

	const NodeRefs = "NodeRefs";

	const PropIgnore = "PropIgnore";
	const PropName = "PropName";
	const PropAttributeOf = "PropAttributeOf";
	const PropIsBlankNode = "PropIsBlankNode";
	const PropIsResourceUri = "PropIsResourceUri";
	const PropIsClassAttr = "PropIsClassAttr";
	const PropDontCreateNode = "PropDontCreateNode";
	const PropForceName = "PropForceName";
	const PropValue = 'PropValue';

	protected $_model = null;

	protected $_config = "xmlnuke";

	protected $_forcePropName;

	protected $_current;

	protected $_node = null;


	public function __construct($current, $model, $config, $forcePropName = "")
	{
		if (is_array($model))
			$this->_model = (object) $model;
		else if (is_object($model))
			$this->_model = $model;
		else
			throw new \InvalidArgumentException('The model is not an object or an array');

		$this->_current = $current;
		$this->_config = $config;
		$this->_forcePropName = $forcePropName;
	}


	public function CreateObjectFromModel()
	{
		if ($this->_model instanceof IIterator)
		{
			foreach ($this->_model as $singleRow)
			{
				XmlUtil::AddNodeFromNode($this->_current, $singleRow->getDomObject());
			}
			return $this->_current;
		}

		$classMeta = $this->getClassInfo();

		if ($classMeta[ObjectHandler::ClassIgnoreAllClass])
			return $this->_current;


		# Get the node names of this Class
		$node = $this->createClassNode($classMeta);

		
		#------------
		# Get all properties
		if ($this->_model instanceof stdClass)
			$properties = get_object_vars ($this->_model);
		else
			$properties = $classMeta[ObjectHandler::ClassRefl]->getProperties( ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PUBLIC );

		$this->createPropertyNodes($node, $properties, $classMeta);

		return $node;
	}

	/**
	 *
	 * @param stdClass $this->_model
	 * @param type $this->_config
	 * @param type $this->_forcePropName
	 * @return type
	 */
	protected function getClassInfo()
	{
		$classMeta = array();

		if (!$this->_model instanceof stdClass)
		{
			$class = new ReflectionClass($this->_model);
			preg_match_all('/@(?P<param>\S+)\s*(?P<value>\S+)?\r?\n/', $class->getDocComment(), $aux);
			$classAttributes = $this->adjustParams($aux);

			$classMeta[ObjectHandler::ClassRefl] = $class;
		}
		else
		{
			$classMeta[ObjectHandler::ClassRefl] = null;
			$classAttributes = array();
		}

		#------------
		# Define Class Attributes
		$classMeta[ObjectHandler::ClassName] = ($this->_forcePropName != "" ? $this->_forcePropName : (isset($classAttributes["$this->_config:nodename"]) ? $classAttributes["$this->_config:nodename"] : get_class($this->_model)));
		$classMeta[ObjectHandler::ClassGetter] = isset($classAttributes["$this->_config:getter"]) ? $classAttributes["$this->_config:getter"] : "get";
		$classMeta[ObjectHandler::ClassPropertyPattern] = isset($classAttributes["$this->_config:propertypattern"]) ? eval($classAttributes["$this->_config:propertypattern"]) : array('/([^a-zA-Z0-9])/', '');
		$classMeta[ObjectHandler::ClassWriteEmpty] = (isset($classAttributes["$this->_config:writeempty"]) ? $classAttributes["$this->_config:writeempty"] : "false") == "true";
		$classMeta[ObjectHandler::ClassDocType] = isset($classAttributes["$this->_config:doctype"]) ? strtolower($classAttributes["$this->_config:doctype"]) : "xml";
		$classMeta[ObjectHandler::ClassRdfType] = $this->replaceVars($classMeta[ObjectHandler::ClassName], isset($classAttributes["$this->_config:rdftype"]) ? $classAttributes["$this->_config:rdftype"] : "{HOST}/rdf/class/{CLASS}");
		$classMeta[ObjectHandler::ClassRdfAbout] = $this->replaceVars($classMeta[ObjectHandler::ClassName], isset($classAttributes["$this->_config:rdfabout"]) ? $classAttributes["$this->_config:rdfabout"] : "{HOST}/rdf/instance/{CLASS}/{GetID()}");
		$classMeta[ObjectHandler::ClassDefaultPrefix] = isset($classAttributes["$this->_config:defaultprefix"]) ? $classAttributes["$this->_config:defaultprefix"] . ":" : "";
		$classMeta[ObjectHandler::ClassIsRDF] = ($classMeta[ObjectHandler::ClassDocType] == "rdf");
		$classMeta[ObjectHandler::ClassIgnoreAllClass] = array_key_exists("$this->_config:ignore", $classAttributes);
		$classMeta[ObjectHandler::ClassNamespace] = isset($classAttributes["$this->_config:namespace"]) ? $classAttributes["$this->_config:namespace"] : "";
		$classMeta[ObjectHandler::ClassDontCreateClassNode] = array_key_exists("$this->_config:dontcreatenode", $classAttributes);
		if (!is_array($classMeta[ObjectHandler::ClassNamespace]) && !empty($classMeta[ObjectHandler::ClassNamespace])) $classMeta[ObjectHandler::ClassNamespace] = array($classMeta[ObjectHandler::ClassNamespace]);

		#----------
		# Node References
		$classMeta[ObjectHandler::NodeRefs] = array();

		return $classMeta;
	}

	/**
	 *
	 * @param type $classMeta
	 * @param type $prop
	 * @param type $keyProp
	 * @param type $this->_config
	 * @return null
	 */
	protected function getPropInfo($classMeta, $prop, $keyProp)
	{
		$propMeta = array();

		$propName = ($prop instanceof ReflectionProperty ? $prop->getName() : $keyProp);
		$propAttributes = array();

		# Does nothing here
		if ($propName == "_propertyPattern")
			return null;

		# Determine where it located the Property Value --> Getter or inside the property
		if (!($prop instanceof ReflectionProperty) || $prop->isPublic())
		{
			preg_match_all('/@(?<param>\S+)\s*(?<value>\S+)?\n/', ($prop instanceof ReflectionProperty ? $prop->getDocComment() : ""), $aux);
			$propAttributes = $this->adjustParams($aux);
			$propMeta[ObjectHandler::PropValue] = ($prop instanceof ReflectionProperty ? $prop->getValue($this->_model) : $prop);
		}
		else
		{
			// Remove Prefix "_" from Property Name to find a value
			if ($propName[0] == "_")
				$propName = substr($propName, 1);

			$methodName = $classMeta[ObjectHandler::ClassGetter] . ucfirst(preg_replace($classMeta[ObjectHandler::ClassPropertyPattern][0], $classMeta[ObjectHandler::ClassPropertyPattern][1], $propName));
			if ($classMeta[ObjectHandler::ClassRefl]->hasMethod($methodName))
			{
				$method = $classMeta[ObjectHandler::ClassRefl]->getMethod($methodName);
				preg_match_all('/@(?<param>\S+)\s*(?<value>\S+)?\r?\n/', $method->getDocComment(), $aux);
				$propAttributes = $this->adjustParams($aux);
				$propMeta[ObjectHandler::PropValue] = $method->invoke($this->_model, "");
			}
			else
				return null;
		}


		$propMeta[ObjectHandler::PropIgnore] = array_key_exists("$this->_config:ignore", $propAttributes);
		$propMeta[ObjectHandler::PropName] = isset($propAttributes["$this->_config:nodename"]) ? $propAttributes["$this->_config:nodename"] : $propName;
		if (strpos($propMeta[ObjectHandler::PropName], ":") === false) $propMeta[ObjectHandler::PropName] = $classMeta[ObjectHandler::ClassDefaultPrefix] . $propMeta[ObjectHandler::PropName];
		$propMeta[ObjectHandler::PropAttributeOf] = $classMeta[ObjectHandler::ClassIsRDF] ? "" : (isset($propAttributes["$this->_config:isattributeof"]) ? $propAttributes["$this->_config:isattributeof"] : "");
		$propMeta[ObjectHandler::PropIsBlankNode] = $classMeta[ObjectHandler::ClassIsRDF] ? (isset($propAttributes["$this->_config:isblanknode"]) ? $propAttributes["$this->_config:isblanknode"] : "") : "";
		$propMeta[ObjectHandler::PropIsResourceUri] = $classMeta[ObjectHandler::ClassIsRDF] && array_key_exists("$this->_config:isresourceuri", $propAttributes); // Valid Only Inside BlankNode
		$propMeta[ObjectHandler::PropIsClassAttr] = $classMeta[ObjectHandler::ClassIsRDF] ? false : array_key_exists("$this->_config:isclassattribute", $propAttributes);
		$propMeta[ObjectHandler::PropDontCreateNode] = array_key_exists("$this->_config:dontcreatenode", $propAttributes);
		$propMeta[ObjectHandler::PropForceName] = isset($propAttributes["$this->_config:dontcreatenode"]) ? $propAttributes["$this->_config:dontcreatenode"] : "";

		return $propMeta;
	}


	protected function createClassNode($classMeta)
	{
		#-----------
		# Setup NameSpaces
		if (is_array($classMeta[ObjectHandler::ClassNamespace]))
		{
			foreach ($classMeta[ObjectHandler::ClassNamespace] as $value)
			{
				$prefix = strtok($value, "!");
				$uri = str_replace($prefix . "!", "", $value);
				XmlUtil::AddNamespaceToDocument($this->_current, $prefix, $this->replaceVars($classMeta[ObjectHandler::ClassName], $uri));
			}
		}

		#------------
		# Create Class Node
		if ($classMeta[ObjectHandler::ClassDontCreateClassNode] || $this->_model instanceof stdClass)
			$node = $this->_current;
		else
		{
			if (!$classMeta[ObjectHandler::ClassIsRDF])
				$node = XmlUtil::CreateChild($this->_current, $classMeta[ObjectHandler::ClassName]);
			else
			{
				XmlUtil::AddNamespaceToDocument($this->_current, "rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
				$node = XmlUtil::CreateChild($this->_current, "rdf:Description");
				XmlUtil::AddAttribute($node, "rdf:about", $classMeta[ObjectHandler::ClassRdfAbout]);
				$nodeType = XmlUtil::CreateChild($node, "rdf:type");
				XmlUtil::AddAttribute($nodeType, "rdf:resource", $classMeta[ObjectHandler::ClassRdfType]);
			}
		}

		return $node;
	}

	protected function createPropertyNodes($node, $properties, $classMeta)
	{
		if (!is_null($properties))
		{
			foreach ($properties as $keyProp => $prop)
			{
				# Define Properties
				$propMeta = $this->getPropInfo($classMeta, $prop, $keyProp);

				if ($propMeta[ObjectHandler::PropIgnore]) continue;

				# Process the Property Value
				$used = null;

				# ------------------------------------------------
				# Value is a OBJECT?
				if (is_object($propMeta[ObjectHandler::PropValue]))
				{
					if ($propMeta[ObjectHandler::PropDontCreateNode])
						$nodeUsed = $node;
					else
						$nodeUsed = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PropName]);

					$objHandler = new ObjectHandler($nodeUsed, $propMeta[ObjectHandler::PropValue], $this->_config, $propMeta[ObjectHandler::PropForceName]);
					$used = $objHandler->CreateObjectFromModel();
				}

				# ------------------------------------------------
				# Value is an ARRAY?
				elseif (is_array ($propMeta[ObjectHandler::PropValue]))
				{
					// Check if the array is associative or dont.
					$isAssoc = (bool)count(array_filter(array_keys($propMeta[ObjectHandler::PropValue]), 'is_string'));
					$hasScalar = (bool)count(array_filter(array_values($propMeta[ObjectHandler::PropValue]), function($val) {
						return !(is_object($val) || is_array($val));
					}));

					if ($propMeta[ObjectHandler::PropDontCreateNode] || (!$isAssoc && $hasScalar))
						$nodeUsed = $node;
					else
					{
						$nodeUsed = $used = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PropName]);
					}


					foreach ($propMeta[ObjectHandler::PropValue] as $keyAr=>$valAr)
					{
						if (
							(!$isAssoc && $hasScalar)   # Is not an associative array and have scalar numbers in it.
								|| !(is_object($valAr) || is_array($valAr))    # The value is not an object and not is array
								|| (is_string($keyAr) && (is_object($valAr) || is_array($valAr))) # The key is string (associative array) and
																								  # the valluris a object or array
						)
						{
							$obj = new \stdClass;
							$obj->{(is_string($keyAr) ? $keyAr : $propMeta[ObjectHandler::PropName])} = $valAr;
						}
						else
						{
							$obj = $valAr;
						}

						$objHandler = new ObjectHandler($nodeUsed, $obj, $this->_config,  $propMeta[ObjectHandler::PropForceName] );
						$objHandler->CreateObjectFromModel();
					}
				}

				# ------------------------------------------------
				# Value is a Single Value?
				else if (!empty($propMeta[ObjectHandler::PropValue])				// Some values are empty for PHP but need to be considered
							|| ($propMeta[ObjectHandler::PropValue] === 0)
							|| ($propMeta[ObjectHandler::PropValue] === false)
							|| ($propMeta[ObjectHandler::PropValue] === '0')
							|| ($classMeta[ObjectHandler::ClassWriteEmpty])
				)
				{
					if ($propMeta[ObjectHandler::PropIsClassAttr])
					{
						XmlUtil::AddAttribute ($node, $propMeta[ObjectHandler::PropName], $propMeta[ObjectHandler::PropValue]);
					}
					elseif ($propMeta[ObjectHandler::PropIsBlankNode] != "")
					{
						if (!array_key_exists($propMeta[ObjectHandler::PropIsBlankNode], $classMeta[ObjectHandler::NodeRefs]))
						{
							$classMeta[ObjectHandler::NodeRefs][$propMeta[ObjectHandler::PropIsBlankNode]] = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PropIsBlankNode]);
							XmlUtil::AddAttribute($classMeta[ObjectHandler::NodeRefs][$propMeta[ObjectHandler::PropIsBlankNode]], "rdf:parseType", "Resource");
						}

						if ($propMeta[ObjectHandler::PropIsResourceUri])
						{
							$blankNodeType = XmlUtil::CreateChild($classMeta[ObjectHandler::NodeRefs][$propMeta[ObjectHandler::PropIsBlankNode]], "rdf:type");
							XmlUtil::AddAttribute($blankNodeType, "rdf:resource", $propMeta[ObjectHandler::PropValue]);
						}
						else
						{
							XmlUtil::CreateChild($classMeta[ObjectHandler::NodeRefs][$propMeta[ObjectHandler::PropIsBlankNode]], $propMeta[ObjectHandler::PropName], $propMeta[ObjectHandler::PropValue]);
						}
					}
					elseif (($propMeta[ObjectHandler::PropAttributeOf] != "") && (array_key_exists($propMeta[ObjectHandler::PropAttributeOf], $classMeta[ObjectHandler::NodeRefs])))
					{
						XmlUtil::AddAttribute ($classMeta[ObjectHandler::NodeRefs][$propMeta[ObjectHandler::PropAttributeOf]], $propMeta[ObjectHandler::PropName], $propMeta[ObjectHandler::PropValue]);
					}
					elseif ((preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $propMeta[ObjectHandler::PropValue])) && $classMeta[ObjectHandler::ClassIsRDF])
					{
						$used = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PropName]);
						XmlUtil::AddAttribute($used, "rdf:resource", $propMeta[ObjectHandler::PropValue]);
					}
					else
					{
						$used = XmlUtil::CreateChild($node, $propMeta[ObjectHandler::PropName], $propMeta[ObjectHandler::PropValue]);
					}
				}

				# Save Reference for "isAttributeOf" attribute.
				if ($used != null)
				{
					$classMeta[ObjectHandler::NodeRefs][$propMeta[ObjectHandler::PropName]] = $used;
				}
			}
		}

	}

	// TODO: Adcionar o objecto Reflection
	protected function replaceVars($name, $text)
	{
		$context = Context::getInstance();

		# Host
		$host = $context->UrlBase() != "" ? $context->UrlBase() : ($context->get("SERVER_PORT") == 443 ? "https://" : "http://") . $context->get("HTTP_HOST");

		# Replace Part One
		$text = preg_replace(array("/\{[hH][oO][sS][tT]\}/", "/\{[cC][lL][aA][sS][sS]\}/"), array($host, $name), $text);

		if(preg_match('/(\{(\S+)\})/', $text, $matches))
		{
			$class = new ReflectionClass(get_class($this->_model));
			$method = str_replace("()", "", $matches[2]);
			$value = spl_object_hash($this->_model);
			if ($class->hasMethod($method))
			{
				try
				{
					$value = $this->_model->$method();
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

	protected function adjustParams($arr)
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


}

