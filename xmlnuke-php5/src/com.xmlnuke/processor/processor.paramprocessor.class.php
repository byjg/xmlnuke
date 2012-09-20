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
*ParamProcessor can process the XSL transform result (or xhtml cache) and replace the [PARAM:...] 
*and Adjust Links to Full XMLNuke link (when is possible).
*<P><b>Only uses this class after XML/XSL Transform and with XHTML files</b></P>
 * @package xmlnuke
*/
class ParamProcessor
{
	/**
	*@var Context
	*/
	private $_context;
	
	/**
	*@param Context $context
	*@return void
	*@desc ParamProcessor constructor.
	*/
	public function __construct()
	{
		$this->_context = Context::getInstance();
	}

	/**
	*@param DOMDocument $xmlDom - XmlDocument to be parsed
	*@param string $tagName - Tag to be looked for
	*@param string $attribute - Attribute within tag to be looked for
	*@return void
	*@desc Process XHTML files and look for HREF attributes and change "engine:xmlnuke" and "module:..." 
	*contents to FULL QUALIFIED VIRTUAL PATH and XMLNUke's context params
	*/
	public function AdjustToFullLink($xmlDom,$tagName,$attribute)
	{
		$xpath = new DOMXPath($xmlDom);
		XmlUtil::registerNamespaceForFilter($xpath, array('x' => 'http://www.w3.org/1999/xhtml'));
		$nodeList = $xpath->query("//".strtolower($tagName)." | //".strtoupper($tagName) . " | //x:".strtolower($tagName)." | //x:".strtoupper($tagName));

		foreach ($nodeList as $node)
		{
			$strAtributeUp = strtoupper($attribute);
			$strAtributeLow = strtolower($attribute);
                            
				if ($node->hasAttribute($strAtributeUp))
			{
				$node->setAttribute($strAtributeUp, $this->GetFullLink($node->getAttribute($strAtributeUp)));
			}
			if ($node->hasAttribute($strAtributeLow))
			{
				$node->setAttribute($strAtributeLow, $this->GetFullLink($node->getAttribute($strAtributeLow)));
			}

		}
	}
	
	/**
	*@param string $strQueryString
	*@param string $strKeyPair
	*@return string - Return the value if found or an empty string if not found
	*@desc Extract from a Query String like (key1=value1&amp;key2=value2&amp;...) the value from a key supplied.
	*/
	private function ExtractPairQueryString($strQueryString, $strKeyPair)
	{
		$iPos = strpos($strQueryString,"?".$strKeyPair."=");
		if ($iPos === false)
		{
			$iPos = strpos($strQueryString,"&".$strKeyPair."=");
			if ($iPos === false)
			{
				return "";
			}
		}
		$iPos++;

		$strQueryString = substr($strQueryString, $iPos + strlen($strKeyPair) + 1);
		$iPos = strpos($strQueryString,"&");
		if ($iPos !== false)
		{
			$strQueryString = substr($strQueryString,0,$iPos);
		}
		else
		{
			$iPos = strpos($strQueryString,"\"");
			if ($iPos !== false)
			{
				$strQueryString = substr($strQueryString,0,$iPos);
			}
		}
		return $strQueryString;
	}
	
	/**
	*@param string $strHref
	*@return string - Return the new string if exists engine:xmlnuke or module:... Otherwise returns the original value
	*@desc Replace a HREF value with XMLNuke context values.
	*/
	public function GetFullLink($strHref)
	{
		$arResult = array();
		$result = "";
		
		$pattern = "(?:(?P<protocol>module|admin|engine):)?(?P<host>[\w\d\-\.]*)(?P<port>:\d*)?(?:\?(?P<param>(([\w\d\W]*=[\w\d\W]*)(?:&(?:amp;)?)?)*))?";
		preg_match_all("/$pattern/", $strHref, $arResult);
		
		$sep = "?";
		
		switch ($arResult["protocol"][0])
		{
			case "engine":
				if ($arResult["host"][0] == "xmlnuke")
					$result = $this->_context->UrlXmlNukeEngine() . $arResult["port"][0];
				else
					$result = "Unknow Engine " . $arResult["host"][0]; 
				break;
			
			case "module":
				$result = $this->_context->UrlModule() . $arResult["port"][0] . "?module=" . $arResult["host"][0];
				$sep = "&";
				break;
				
			case "admin":
				if ($arResult["host"] == "engine")
					$result = $this->_context->UrlXmlNukeAdmin() . $arResult["port"][0];
				else
				{
					$result = $this->_context->UrlModule() . $arResult["port"][0] . "?module=" . (strpos ($arResult["host"][0], ".") === false ? "admin." : "") . $arResult["host"][0];
					$sep = "&";
				}
				break;
				
			default:
				return $strHref;
		}

		$arParam = array();
		$xmlnukeParam = array();
		
		$fullLink = ($this->_context->ContextValue("xmlnuke.USEFULLPARAMETER") == "true");
		
		if ($fullLink || $this->_context->getSite()!= $this->_context->ContextValue("xmlnuke.DEFAULTSITE"))
		{
			$xmlnukeParam["site"] = $this->_context->getSite();
		}
		if ($fullLink || $this->_context->getXsl()!= $this->_context->ContextValue("xmlnuke.DEFAULTPAGE"))
		{
			$xmlnukeParam["xsl"] = ($this->_context->getXsl() == "index" ? $this->_context->ContextValue("xmlnuke.DEFAULTPAGE") : $this->_context->getXsl());
		}
		if ($fullLink)
		{
			$xmlnukeParam["xml"] = ($this->_context->getXml());
		}
		if ($fullLink || (array_key_exists("lang", $_REQUEST) && $_REQUEST["lang"] == strtolower($this->_context->Language()->getName())))
		{
			$xmlnukeParam["lang"] = strtolower($this->_context->Language()->getName());
		}

		
		$paramsTmp = explode("&", str_replace("&amp;", "&", $arResult["param"][0]));
		foreach($paramsTmp as $value)
		{
			$arTmp = explode("=", $value);
			
			switch ($arTmp[0])
			{
				case "site":
				case "xml":
				case "xsl":
				case "lang":
					$xmlnukeParam[$arTmp[0]] = $arTmp[1];
					break;
					
				default:
					if ($value != "")
						$arParam[] = $value;
			}
		}
		$strParam = implode("&", $arParam);
		
		$arParam2 = array();
		foreach ($xmlnukeParam as $key=>$value)
		{
			$arParam2[] = $key . "=" . $value;
		}
		$strParam2 = implode("&", $arParam2);
		
		$paramsFinal = $strParam2 . (!empty ($strParam2) && !empty($strParam) ? "&" : "") . $strParam;
		
		return $this->_context->VirtualPathAbsolute($result . ($paramsFinal != "" ? $sep . $paramsFinal : ""));	
	}

	/**
	*@param DOMDocument $xmlDom
	*@return void
	*@desc Process XHTML file and replace the tags [param:...] to XMLNuke context values
	*/
	public function ProcessParameters($xmlDom)
	{
		
		$nodeRoot = $xmlDom->documentElement;

		if ($nodeRoot != null)
		{
			if ($nodeRoot->hasChildNodes())
			{
				$nodeWorking = $nodeRoot->firstChild;
				while ($nodeWorking != null)
				{
					$this->ProcessChildren($nodeWorking, 0);
					$nodeWorking = $nodeWorking->nextSibling;
				}
			}
		}
	}

	/**
	*@param DOMNode $node
	*@param int $depth
	*@return void
	*@desc 
	*/
	private function ProcessChildren($node, $depth)
	{
		// "TEXTAREA" and "PRE" nodes doesnt process PARAM names!
		if ( ($node->parentNode->nodeName == "textarea") || ($node->parentNode->nodeName == "pre") )
		{
			return;
		}

		if (($node->nodeType == XML_ELEMENT_NODE ))
		{

			$attribs = $node->attributes;
			if ($attribs != null)
			{
				$i=0;
				while ($attribs->item($i)!=null)
				{
					$result = $this->CheckParameters($attribs->item($i)->nodeValue);

					if ($result != $attribs->item($i)->nodeValue)
					{
						$attribs->item($i)->nodeValue = $result; // str_replace("&amp;amp;", "&amp;", str_replace("&", "&amp;", $result));
					}

					$i++;

				}

			}
		}
		elseif ($node->nodeType == XML_TEXT_NODE)
		{

			$result = $this->CheckParameters($node->nodeValue);

			if (true || $result != $node->nodeValue)
			{
				// If test below is True RESULT contains HTML TAGS. These tags need be processed
				// Otherwise, go ahead!
				if (strpos($result,"<")!==false)
				{
					try
					{
						if (strpos($result, "<![CDATA[") === false)
						{
							$result = "<![CDATA[" . $result . "]]>";
						}

						//$result = str_replace("&amp;","&",$result);
					
						$nodeToProc = XmlUtil::CreateXmlDocumentFromStr("<root>$result</root>", false)->documentElement;
						if ($node->nodeType == XML_TEXT_NODE)
						{
							$node->nodeValue = "";
							XmlUtil::AddNodeFromNode($node->parentNode, $nodeToProc);
						}
						else
						{
							XmlUtil::AddNodeFromNode($node, $nodeToProc);
						}
					}
					catch (Exception $ex)
					{
						// Nothing to do. Text isn't a valid XML Node.
						// Alternativaly you can disable ParamProcessor feature.
					}
				}
				else
				{
					$result = str_replace("&amp;","&",$result);
					$node->nodeValue = $result;
				}

			}

		}
		if (($node->nodeType == XML_ELEMENT_NODE ) || ($node->nodeType == XML_TEXT_NODE))
		{
			//DOMNode nodeworking;
			if ($node->hasChildNodes())
			{
				$nodeworking = $node->firstChild;
				while ($nodeworking != null)
				{
					$this->ProcessChildren($nodeworking, $depth + 1);
					$nodeworking = $nodeworking->nextSibling;
				}
			}
		}

	}

	/**
	*@param string $param
	*@return string
	*@desc Process XHTML file and replace the tags [param:...] to XMLNuke context values
	*/
	private function CheckParameters($param)
	{
		if ($param == null)
		{
			return "";
		}
		$iStart = strpos($param,"[param:");
		if ($iStart !== false)
		{
			$iEnd;
			while ($iStart !== false)
			{
				$iEnd = strpos($param,"]",$iStart+1);
				$paramDesc = substr($param,$iStart + 7, $iEnd - $iStart - 7);
				$param = substr($param, 0, $iStart). str_replace("&", "&amp;", $this->_context->ContextValue($paramDesc)) . substr($param,$iEnd+ 1);
				$iStart = strpos($param,"[param:");
				
			}
		}
		return $param;
	}

}

?>
