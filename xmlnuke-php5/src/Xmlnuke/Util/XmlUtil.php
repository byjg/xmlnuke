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

namespace Xmlnuke\Util;

define('XMLUTIL_OPT_DONT_PRESERVE_WHITESPACE', 0x01);
define('XMLUTIL_OPT_FORMAT_OUTPUT', 0x02);
define('XMLUTIL_OPT_DONT_FIX_AMPERSAND', 0x04);

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use InvalidArgumentException;
use SimpleXMLElement;
use Xmlnuke\Core\Exception\NotFoundException;
use Xmlnuke\Core\Exception\XmlUtilException;
use Xmlnuke\Core\Processor\FilenameProcessor;

/**
* Generic functions to manipulate XML nodes.
* Note: This classes didn't inherits from \DOMDocument or \DOMNode
*/
class XmlUtil
{
	/**
	* XML document version
	* @var string
	*/
	const XML_VERSION = "1.0";
	/**
	* XML document encoding
	* @var string
	*/
	const XML_ENCODING = "utf-8";
	
	public static $XMLNSPrefix = array();

	/**
	* Create an empty XmlDocument object with some default parameters
	*
	* @return DOMDocument object
	*/
	public static function CreateXmlDocument($docOptions = 0)
	{
		$xmldoc = new DOMDocument(self::XML_VERSION , self::XML_ENCODING );
		$xmldoc->preserveWhiteSpace = ($docOptions & XMLUTIL_OPT_DONT_PRESERVE_WHITESPACE) != XMLUTIL_OPT_DONT_PRESERVE_WHITESPACE;
		if (($docOptions & XMLUTIL_OPT_FORMAT_OUTPUT) == XMLUTIL_OPT_FORMAT_OUTPUT)
		{
			$xmldoc->preserveWhiteSpace = false;
			$xmldoc->formatOutput = true;
		}
		XmlUtil::$XMLNSPrefix[spl_object_hash($xmldoc)] = array();
		return $xmldoc;
	}

	/**
	* Create a XmlDocument object from a file saved on disk.
	* @param string $filename
	* @return DOMDocument
	*/
	public static function CreateXmlDocumentFromFile($filename, $docOptions = XMLUTIL_OPT_DONT_FIX_AMPERSAND)
	{
		if (!FileUtil::Exists($filename)) {
			throw new NotFoundException("Xml document $filename not found.", 250);
		}
		$xml = FileUtil::QuickFileRead($filename);
		$xmldoc = self::CreateXmlDocumentFromStr($xml, true, $docOptions);
		return $xmldoc;
	}

	/**
	* Create XML \DOMDocument from a string
	* @param string $xml - XML string document
	* @return DOMDocument
	*/
	public static function CreateXmlDocumentFromStr($xml, $checkUTF8 = true, $docOptions = XMLUTIL_OPT_DONT_FIX_AMPERSAND)
	{
		$xmldoc = self::CreateXmlDocument($docOptions);
		if ($checkUTF8)	$xml = FileUtil::fixUTF8($xml);
		$xml = XmlUtil::FixXMLHeader($xml);
		if (($docOptions & XMLUTIL_OPT_DONT_FIX_AMPERSAND) != XMLUTIL_OPT_DONT_FIX_AMPERSAND)
			$xml = str_replace("&amp;", "&", $xml);

		$xmldoc->loadXML($xml);

		XmlUtil::extractNameSpaces($xmldoc);
		return $xmldoc;
	}

	/**
	* Create a \DOMDocumentFragment from a node
	* @param DOMNode $node
	* @return DOMDocument
	*/
	public static function CreateDocumentFromNode($node, $docOptions = 0)
	{
		$xmldoc = self::CreateXmlDocument($docOptions);
		XmlUtil::$XMLNSPrefix[spl_object_hash($xmldoc)] = array();
		$root = $xmldoc->importNode($node, true);
		$xmldoc->appendChild($root);
		return $xmldoc;
	}
	
	protected static function extractNameSpaces($nodeOrDoc)
	{
		$doc = XmlUtil::getOwnerDocument($nodeOrDoc);
		
		$hash = spl_object_hash($doc);
		$root = $doc->documentElement;

		#-- 
		$xpath = new DOMXPath($doc);
		foreach( $xpath->query('namespace::*', $root) as $node ) 
		{
			XmlUtil::$XMLNSPrefix[$hash][$node->prefix] = $node->nodeValue;
		}
	}

	/**
	* Adjust xml string to the proper format
	* @param string $string - XML string document
	* @return string - Return the string converted
	*/
	public static function FixXMLHeader($string)
	{		
		if(strpos($string, "<?xml") !== false)
		{
			$xmltagend = strpos($string, "?>");
			if ($xmltagend !== false)
			{
				$xmltagend += 2;
				$xmlheader = substr($string, 0, $xmltagend);
			}
			else
			{
				throw new XmlUtilException("XML header bad formatted.", 251);
			}
			
			// Complete header elements
			$count = 0;
			$xmlheader = preg_replace("/version=([\"'][\w\d\-\.]+[\"'])/", "version=\"" . self::XML_VERSION . "\"", $xmlheader, 1, $count);
			if ($count == 0)
			{
				$xmlheader = substr($xmlheader, 0, 6)  . "version=\"" . self::XML_VERSION . "\" " . substr($xmlheader, 6);
			}
			$count = 0;
			$xmlheader = preg_replace("/encoding=([\"'][\w\d\-\.]+[\"'])/", "encoding=\"" . self::XML_ENCODING . "\"", $xmlheader, 1, $count);
			if ($count == 0)
			{
				$xmlheader = substr($xmlheader, 0, 6)  . "encoding=\"" . self::XML_ENCODING . "\" " . substr($xmlheader, 6);
			}
			
			// Fix header position (first version, after encoding)
			$xmlheader = preg_replace(
				"/<\?([\w\W]*)\s+(encoding=([\"'][\w\d\-\.]+[\"']))\s+(version=([\"'][\w\d\-\.]+[\"']))\s*\?>/", 
				"<?\\1 \\4 \\2?>", $xmlheader, 1, $count);

			return $xmlheader . substr($string, $xmltagend);		
		}
		else
		{
			$xmlheader = '<?xml version="' . self::XML_VERSION  . '" encoding="' . self::XML_ENCODING  .'"?>';
			return $xmlheader . $string;
		}

	}

	/**
	 *
	 * @param DOMDocument $document
	 * @param string $filename
	 * @throws XmlUtilException
	 */
	public static function SaveXmlDocument($document, $filename)
	{
		if (!($document instanceof DOMDocument))
		{
			throw new XmlUtilException("Object isn't a \DOMDocument.", 255); // Document não é um documento XML
		}
		else
		{
			$ret = $document->save($filename);
			if ($ret === false)
			{
				throw new XmlUtilException("Cannot save XML Document in $filename.", 256); // Não foi possível gravar o arquivo: PERMISSÂO ou CAMINHO não existe;
			}
		}
	}


	/**
	 * Get document without xml parameters
	 *
	 * @param DOMDocument $xml
	 * @return string
	 */
	public static function GetFormattedDocument($xml)
	{
		$document = $xml->saveXML();
		$i = strpos($document, "&#");
		while ($i!=0)
		{
			$char = substr($document, $i, 5);
			$document = substr($document, 0, $i) . chr(hexdec($char)) . substr($document, $i+6);
			$i = strpos($document, "&#");
		}
		return $document;
	}
	
	
	/**
	 *
	 * @param type $nodeOrDoc 
	 */
	public static function AddNamespaceToDocument($nodeOrDoc, $prefix, $uri)
	{
		$doc = XmlUtil::getOwnerDocument($nodeOrDoc);
		
		if ($doc == null)
			throw new XmlUtilException("Node or document is invalid.");
		
		$hash = spl_object_hash($doc);
		$root = $doc->documentElement;

		if ($root == null)
			throw new XmlUtilException("Node or document is invalid. Cannot retrieve 'documentElement'.");
		
		$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,"xmlns:$prefix", $uri);
		XmlUtil::$XMLNSPrefix[$hash][$prefix] = $uri;
	}

	/**
	* Add node to specific XmlNode from file existing on disk
	*
	* @param DOMNode $rootNode XmlNode receives node
	* @param FilenameProcessor $filename File to import node
	* @param string $nodetoadd Node to be added
	*/
	public static function AddNodeFromFile($rootNode, $filename, $nodetoadd)
	{
		if ($rootNode == null)
		{
			return;
		}
		if (!$filename->getContext()->getXMLDataBase()->existsDocument($filename->FullQualifiedName()))
		{
			return;
		}

		try
		{
			// \DOMDocument
			$source = $filename->getContext()->getXMLDataBase()->getDocument($filename->FullQualifiedName(),null);

			$nodes = $source->getElementsByTagName($nodetoadd)->item(0)->childNodes;

			foreach ($nodes as $node)
			{
				$newNode = $rootNode->ownerDocument->importNode($node, true);
				$rootNode->appendChild($newNode);
			}
		}
		catch (\Exception $ex)
		{
			throw $ex;
		}
	}

	/**
	* Attention: NODE MUST BE AN ELEMENT NODE!!!
	*
	* @param DOMElement $source
	* @param DOMElement $nodeToAdd
	*/
	public static function AddNodeFromNode($source, $nodeToAdd)
	{
		if ($nodeToAdd->hasChildNodes())
		{
			$nodeList = $nodeToAdd->childNodes; // It is necessary because Zend Core For Oracle didn't support
			// access the property Directly.
			foreach ($nodeList as $node)
			{
				$owner = XmlUtil::getOwnerDocument($source);
				$newNode = $owner->importNode($node,TRUE);
				$source->appendChild($newNode);
			}
		}
	}

	/**
	* Append child node from specific node and add text
	*
	* @param DOMNode $rootNode Parent node
	* @param string $nodeName Node to add string
	* @param string $nodeText Text to add string
	* @return DOMElement
	*/
	public static function CreateChild($rootNode, $nodeName, $nodeText="", $uri="")
	{
		if (empty($nodeName))
			throw new XmlUtilException("Node name must be a string.");
		
		$nodeworking = XmlUtil::createChildNode($rootNode, $nodeName, $uri);
		self::AddTextNode($nodeworking, $nodeText);
		$rootNode->appendChild($nodeworking);
		return $nodeworking;
	}
	
	/**
	* Create child node on the top from specific node and add text
	*
	* @param DOMNode $rootNode Parent node
	* @param string $nodeName Node to add string
	* @param string $nodeText Text to add string
	* @return DOMElement
	*/
	public static function CreateChildBefore($rootNode, $nodeName, $nodeText, $position = 0)
	{
		return self::CreateChildBeforeNode($nodeName, $nodeText, $rootNode->childNodes->item($position));
	}

	public static function CreateChildBeforeNode($nodeName, $nodeText, $node)
	{
		$rootNode = $node->parentNode;
		$nodeworking = XmlUtil::createChildNode($rootNode, $nodeName);
		self::AddTextNode($nodeworking, $nodeText);
		$rootNode->insertBefore($nodeworking, $node);
		return $nodeworking;
	}
	
	/**
	* Add text to node
	*
	* @param DOMNode $rootNode Parent node
	* @param string $text Text to add String
	* @param bool $escapeChars (True create CData instead Text node)
	*/
	public static function AddTextNode($rootNode, $text, $escapeChars = false)
	{
		if (!empty($text) || is_numeric($text))
		{
			$owner = XmlUtil::getOwnerDocument($rootNode);
			if ($escapeChars)
			{
				$nodeworkingText = $owner->createCDATASection($text);
			}
			else 
			{
				$nodeworkingText = $owner->createTextNode($text);
			}
			$rootNode->AppendChild($nodeworkingText);
		}
	}

	/**
	* Add a attribute to specific node
	*
	* @param DOMElement $rootNode Node to receive attribute
	* @param string $name Attribute name string
	* @param string $value Attribute value string
	* @return DOMElement
	*/
	public static function AddAttribute($rootNode, $name, $value)
	{
		XmlUtil::checkIfPrefixWasDefined($rootNode, $name);
		
		$owner = XmlUtil::getOwnerDocument($rootNode);
		$attrNode = $owner->createAttribute($name);
		$attrNode->value = $value;
		$rootNode->setAttributeNode($attrNode);
		return $rootNode;
	}
	
	/**
	 * Returns a \DOMNodeList from a relative xPath from other \DOMNode
	 *
	 * @param node $pNode
	 * @param string $xPath
	 * @param array $arNamespace
	 * @return DOMNodeList
	 */
	public static function selectNodes($pNode, $xPath, $arNamespace = null) // <- Retorna N&#65533;!
	{
		if (substr($xPath, 0, 1) == "/")
		{
			$xPath = substr($xPath, 1);
		}

		$owner = XmlUtil::getOwnerDocument($pNode);
		$xp = new DOMXPath($owner);
		XmlUtil::registerNamespaceForFilter($xp, $arNamespace);
		$rNodeList = $xp->query($xPath, $pNode);

		return $rNodeList;
	}

	/**
	 * Returns a \DOMElement from a relative xPath from other \DOMNode
	 * 
	 * @param DOMElement $pNode
	 * @param string $xPath - xPath string format
	 * @param array $arNamespace
	 * @return DOMElement
	 */
	public static function selectSingleNode($pNode, $xPath, $arNamespace = null) // <- Retorna
	{
		while ($xPath[0] == "/") {
			$xPath = substr($xPath, 1);
		}
		$rNode = null;
		if($pNode->nodeType != XML_DOCUMENT_NODE)
		{
			$owner = XmlUtil::getOwnerDocument($pNode);
			$xp = new DOMXPath($owner);
			XmlUtil::registerNamespaceForFilter($xp, $arNamespace);
			$rNodeList = $xp->query("$xPath", $pNode);
		}
		else
		{
			$xp = new DOMXPath($pNode);
			XmlUtil::registerNamespaceForFilter($xp, $arNamespace);
			$rNodeList = $xp->query("//$xPath");
		}
		$rNode = $rNodeList->item(0);
		return $rNode;
	}
	
	/**
	 *
	 * @param DOMXPath $xpath
	 * @param array $arNamespace 
	 */
	public static function registerNamespaceForFilter($xpath, $arNamespace)
	{
		if (($arNamespace != null) && (is_array($arNamespace)))
		{
			foreach ($arNamespace as $prefix=>$uri)
			{
				$xpath->registerNamespace($prefix, $uri);
			}
		}
	}

	/**
	* Concat a xml string in the node
	* @param DOMNode $node
	* @param string $xmlstring
	* @return DOMNode
	*/
	public static function innerXML($node, $xmlstring)
	{
		$xmlstring = str_replace("<br>", "<br/>", $xmlstring);
		$len = strlen($xmlstring);
		$endText = "";
		$close = strrpos($xmlstring, '>');
		if ($close !== false && $close < $len-1)
		{
			$endText = substr($xmlstring, $close+1);
			$xmlstring = substr($xmlstring, 0, $close+1);
		}
		$open = strpos($xmlstring, '<');
		if($open === false)
		{
			$node->nodeValue .= $xmlstring;
		}
		else
		{
			if ($open > 0) {
				$text = substr($xmlstring, 0, $open);
				$xmlstring = substr($xmlstring, $open);
				$node->nodeValue .= $text;
			}
			$dom = XmlUtil::getOwnerDocument($node);
			$xmlstring = "<rootxml>$xmlstring</rootxml>";
			$sxe = @simplexml_load_string($xmlstring);
			if ($sxe === false)
			{
				throw new XmlUtilException("Cannot load XML string.", 252);
			}
			$dom_sxe = dom_import_simplexml($sxe);
			if (!$dom_sxe)
			{
				throw new XmlUtilException("XML Parsing error.", 253);
			}
			$dom_sxe = $dom->importNode($dom_sxe, true);
			$childs = $dom_sxe->childNodes->length;
			for ($i=0; $i<$childs; $i++)
			{
				$node->appendChild($dom_sxe->childNodes->item($i)->cloneNode(true));
			}
		}
		if (!empty($endText) && $endText != "")
		{
			$textNode = $dom->createTextNode($endText);
			$node->appendChild($textNode);
		}
		return $node->firstChild;
	}

	/**
	* Return the tree nodes in a simple text
	* @param DOMNode $node
	* @return DOMNode
	*/
	public static function innerText($node)
	{
		$doc = XmlUtil::CreateDocumentFromNode($node);
		return self::CopyChildNodesFromNodeToString($doc);
	}

	/**
	* Return the tree nodes in a simple text
	* @param DOMNode $node
	* @return DOMNode
	*/
	public static function CopyChildNodesFromNodeToString($node)
	{
		$xmlstring = "<rootxml></rootxml>";
		$doc = self::CreateXmlDocumentFromStr($xmlstring);
		$string = '';
		$root = $doc->firstChild;
		$childlist = $node->firstChild->childNodes; // It is necessary because Zend Core For Oracle didn't support
		// access the property Directly.
		foreach ($childlist as $child)
		{
			$cloned = $doc->importNode($child, true);
			$root->appendChild($cloned);
		}
		$string = $doc->saveXML();
		$string = str_replace('<?xml version="' . self::XML_VERSION . '" encoding="' . self::XML_ENCODING . '"?>', '', $string);
		$string = str_replace('<rootxml>', '', $string);
		$string = str_replace('</rootxml>', '', $string);
		return $string;
	}

	/**
	* Return the part node in xml document
	* @param DOMNode $node
	* @return string
	*/
	public static function SaveXmlNodeToString($node)
	{
		$doc = XmlUtil::getOwnerDocument($node);
		$string = $doc->saveXML($node);
		return $string;
	}

	/**
	 * Convert <br/> to \n
	 *
	 * @param string $str
	 */
	public static function br2nl($str)
	{
		return str_replace("<br />", "\n", $str);
	}

	/**
	 * Assist you to Debug XMLs string documents. Echo in out buffer.
	 *
	 * @param string $val
	 */
	public static function showXml($val)
	{
		print "<pre>" . htmlentities($val) . "</pre>";
	}
	
	/**
	 * Remove a specific node
	 *
	 * @param DOMNode $node
	 */
	public static function removeNode($node)
	{
		$nodeParent = $node->parentNode;
		$nodeParent->removeChild($node);
	}
	
	/**
	 * Remove a node specified by your tag name. You must pass a \DOMDocument ($node->ownerDocument);
	 *
	 * @param DOMDocument $domdocument
	 * @param string $tagname
	 * @return bool 
	 */
	public static function removeTagName($domdocument, $tagname)
	{
		$nodeLista = $domdocument->getElementsByTagName($tagname);
		if ($nodeLista->length > 0)
		{
			$node = $nodeLista->item(0);
			XmlUtil::removeNode($node);
			return true;
		}
		else 
		{
			return false;
		}
	}

	public static function xml2Array($arr, $func = "") 
	{
		if ($arr instanceof SimpleXMLElement)
		{
			return XmlUtil::xml2Array((array)$arr, $func);
		}
		
		if (($arr instanceof DOMElement) || ($arr instanceof DOMDocument))
		{
			return XmlUtil::xml2Array((array)simplexml_import_dom($arr), $func);
		}
		
		$newArr = array(); 
		if (!empty($arr)) 
		{ 
			foreach($arr AS $key => $value) 
			{ 
				$newArr[$key] = 
					(is_array($value) || ($value instanceof DOMElement) || ($value instanceof DOMDocument) || ($value instanceof SimpleXMLElement) ? XmlUtil::xml2Array($value, $func) : (
							!empty($func) ? $func($value) : $value
						)
					); 
			} 
		} 
		
		return $newArr; 
	}

	protected static function mapArray(&$value, $key)
	{
		//echo "Key: " . $key . "\n";

		if ($value instanceof SimpleXMLElement)
		{
			$x = array();
			foreach($value->children() as $k => $v)
			{
				$text = "".$v;
				if ($text != "")
				{
					$arText = array($text);
				}
				else
				{
					$arText = array();
				}
				$x[$k][] = (array)$v + $arText;
			}
			$x = (array)$value->attributes() + $x;


			$value = $x;
			//$value = (array)$value;
		}


		/*
		if (($key == "select") && is_array($value) && array_key_exists("option", $value) && is_array($value["option"]))
		{
			$arr = array();
			foreach ($value["option"] as $k => $item)
			{
					$id = array_key_exists("@attributes", $item) ? $item["@attributes"]["value"] : "";
					$value = $item[0];
					$arr[] = array("id"=>$id, "value"=>$value);
			}
			$value = $arr;
		}
		*/

		// Fix empty arrays or with one element only.
		if (is_array($value))
		{
			if (count($value) == 0)
				$value = "";
			elseif (count($value) == 1 && array_key_exists(0, $value))
				$value = $value[0];
		}
		
		// If still as array, process it
		if (is_array($value))
		{
			// Transform attributes
			if (array_key_exists("@attributes", $value))
			{
				$attributes = array();
				foreach ($value["@attributes"] as $k => $v)
				{
					$attributes["$k"] = $v;
				}
				$value = $attributes + $value;
				unset($value["@attributes"]);
			}

			// Fix empty arrays or with one element only.
			if (count($value) == 0)
			{
				$value = "";
			}
			else if (array_key_exists(0, $value) && count($value) == 1)
			{
				$value = $value[0];
			}
			else if (array_key_exists(0, $value) && !array_key_exists(1, $value))
			{
				$value["_text"] = $value[0];
				unset($value[0]);
			}

			// If still an array, walk. 
			if (is_array($value))
				array_walk($value, "Xmlnuke\Util\XmlUtil::mapArray");
		}
	}

	/**
	 *
	 * @param DOMNode $domnode
	 * @param type $jsonFunction
	 * @return type
	 */
	public static function xml2json($domnode, $jsonFunction = "")
	{
		if (!($domnode instanceof DOMNode))
			throw new InvalidArgumentException("xml2json requires a \DOMNode descendant");

		$xml = simplexml_import_dom($domnode);
		
		$pre = $pos = "";
		if (!empty($jsonFunction))
		{
			$pre = "(";
			$pos = ")";
		}

		if ($xml->getName() == "xmlnuke")
			$array = (array)$xml->children();
		else
			$array = (array)$xml;

		array_walk($array, "Xmlnuke\Util\XmlUtil::mapArray");

		return $jsonFunction . $pre . json_encode($array) . $pos;
	}

	/**
	 *
	 * @param DOMNode $node
	 * @return DOMDocument
	 * @throws XmlUtilException
	 */
	protected static function getOwnerDocument( $node )
	{
		if (!($node instanceof DOMNode))
		{
			throw new XmlUtilException("Object isn't a \DOMNode. Found object class type: " . get_class($node), 257);
		}

		if ($node instanceof DOMDocument)
			return $node;
		else
			return $node->ownerDocument;
	}

	/**
	 *
	 * @param DOMNode $node
	 * @param string $name
	 * @param string $uri
	 * @return type
	 * @throws XmlUtilException
	 */
	protected static function createChildNode( $node, $name, $uri="" )
	{
		if ($uri == "")
			XmlUtil::checkIfPrefixWasDefined($node, $name);

		$owner = self::getOwnerDocument($node);

		if ($uri == "")
		{
			$newnode = $owner->createElement(preg_replace('/[^\w:]/', '_', $name));
		}
		else
		{
			$newnode = $owner->createElementNS($uri, $name);
			if ($owner == $node)
			{
				$tok = strtok($name, ":");
				if ($tok != $name)
					XmlUtil::$XMLNSPrefix[spl_object_hash($owner)][$tok] = $uri;
			}
		}

		if($newnode === false)
		{
			throw new XmlUtilException("Failed to create \DOMElement.", 258);
		}
		return $newnode;
	}

	/**
	 *
	 * @param type $node
	 * @param type $name
	 * @throws \Exception
	 */
	protected static function checkIfPrefixWasDefined( $node, $name )
	{
		$owner = self::getOwnerDocument($node);
		$hash = spl_object_hash($owner);

		$prefix = strtok($name, ":");
		if (($prefix != $name) && !array_key_exists($prefix, XmlUtil::$XMLNSPrefix[$hash]))
		{
			throw new XmlUtilException("You cannot create the node/attribute $name without define the URI. Try to use XmlUtil::AddNamespaceToDocument.");
		}
	}

}
?>
