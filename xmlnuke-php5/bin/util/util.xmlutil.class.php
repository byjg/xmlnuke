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
* Generic functions to manipulate XML nodes.
* Note: This classes didn't inherits from DOMDocument or DOMNode
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

	/**
	* Create an empty XmlDocument object with some default parameters
	*
	* @return DOMDocument object
	*/
	public static function CreateXmlDocument()
	{
		$xmldoc = new DOMDocument(self::XML_VERSION , self::XML_ENCODING );
		return $xmldoc;
	}

	/**
	* Create a XmlDocument object from a file saved on disk.
	* @param string $filename
	* @return DOMDocument
	*/
	public static function CreateXmlDocumentFromFile($filename)
	{
		if (!FileUtil::Exists($filename)) {
			throw new XmlUtilException(250, "Don't possible to create XML Document.");
		}
		$xml = FileUtil::QuickFileRead($filename);
		$xml = str_replace("&amp;", "&",$xml);
		$xmldoc = self::CreateXmlDocumentFromStr($xml);
		return $xmldoc;
	}

	/**
	* Create XML DOMDocument from a string
	* @param string $xml - XML string document
	* @return DOMDocument
	*/
	public static function CreateXmlDocumentFromStr($xml, $checkUTF8 = true)
	{
		$xmldoc = self::CreateXmlDocument();
		if ($checkUTF8)	$xml = FileUtil::CheckUTF8Encode($xml);
		$xml = self::FixXMLHeader($xml);
		$xml = str_replace("&", "&amp;",$xml);
		XmlUtilKernel::LoadXMLDocument($xmldoc, $xml);
		return $xmldoc;
	}

	/**
	* Create a DOMDocumentFragment from a node
	* @param DOMNode $node
	* @return DOMDocument
	*/
	public static function CreateDocumentFromNode($node)
	{
		$xmldoc = self::CreateXmlDocument();
		$root = $xmldoc->importNode($node, true);
		$xmldoc->appendChild($root);
		return $xmldoc;
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
				throw new XmlUtilException(251, "Header bad formatted.");
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
	* @param DOMDocument $xmldoc
	* @param string $filename - File name to save.
	* @return void
	*/
	public static function SaveXmlDocument($xmldoc, $filename)
	{
		XmlUtilKernel::SaveXMLDocument($xmldoc, $filename);
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
			//DOMDocument
			$source = $filename->getContext()->getXMLDataBase()->getDocument($filename->FullQualifiedName(),null);

			$nodes = $source->getElementsByTagName($nodetoadd)->item(0)->childNodes;

			foreach ($nodes as $node)
			{
				$newNode = $rootNode->ownerDocument->importNode($node, true);
				$rootNode->appendChild($newNode);
			}
		}
		catch (Exception $ex)
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
				$owner = XmlUtilKernel::getOwnerDocument($source);
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
	public static function CreateChild($rootNode, $nodeName, $nodeText="")
	{
		$nodeworking = XmlUtilKernel::createChildNode($rootNode, $nodeName);
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
		$nodeworking = XmlUtilKernel::createChildNode($rootNode, $nodeName);
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
			$owner = XmlUtilKernel::getOwnerDocument($rootNode);
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
		$owner = XmlUtilKernel::getOwnerDocument($rootNode);
		$attrNode = $owner->createAttribute($name);
		$attrNode->value = $value;
		$rootNode->setAttributeNode($attrNode);
		return $rootNode;
	}
	
	/**
	 * Returns a DOMNodeList from a relative xPath from other DOMNode
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

		$owner = XmlUtilKernel::getOwnerDocument($pNode);
		$xp = new DOMXPath($owner);
		XmlUtil::registerNamespace($xp, $arNamespace);
		$rNodeList = $xp->query($xPath, $pNode);

		return $rNodeList;
	}

	/**
	 * Returns a DOMElement from a relative xPath from other DOMNode
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
			$owner = XmlUtilKernel::getOwnerDocument($pNode);
			$xp = new DOMXPath($owner);
			XmlUtil::registerNamespace($xp, $arNamespace);
			$rNodeList = $xp->query("$xPath", $pNode);
		}
		else
		{
			$xp = new DOMXPath($pNode);
			XmlUtil::registerNamespace($xp, $arNamespace);
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
	public static function registerNamespace($xpath, $arNamespace)
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
			$dom = XmlUtilKernel::getOwnerDocument($node);
			$xmlstring = "<rootxml>$xmlstring</rootxml>";
			$sxe = @simplexml_load_string($xmlstring);
			if ($sxe === false)
			{
				throw new XmlUtilException(252, "Don't possible to load XML string.");
			}
			$dom_sxe = dom_import_simplexml($sxe);
			if (!$dom_sxe)
			{
				throw new XmlUtilException(253, "XML Parsing error.");
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
		$doc = XmlUtilKernel::getOwnerDocument($node);
		$string = $doc->saveXML($node);
		return "<rootxml>$string</rootxml>";
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
	 * Remove a node specified by your tag name. You must pass a DOMDocument ($node->ownerDocument);
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
}
?>
