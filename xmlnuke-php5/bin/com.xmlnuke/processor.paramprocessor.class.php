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
	public function ParamProcessor($context)
	{
		$this->_context = $context;
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
		XmlUtil::registerNamespace($xpath, array('x' => 'http://www.w3.org/1999/xhtml'));
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
		$sResult = $strHref;
		$admin = false;
		$iPosScript = strpos($strHref,"engine:xmlnuke");
		if ($iPosScript!==false)
		{        
			$sResult = substr($sResult,0,$iPosScript).$this->_context->UrlXmlNukeEngine().substr($sResult, strlen("engine:xmlnuke")+$iPosScript);
		}
		else
		{
			$iPosScript = strpos($strHref,"module:");
			if ($iPosScript!==false)
			{
				$sResult = substr($sResult,0,$iPosScript).$this->_context->UrlModule()."?module=".str_replace("?","&",substr($sResult,strlen("module:")+$iPosScript));
				
			}
			else
			{
				//Falta testar o admin
				$iPosScript = strpos($strHref,"admin:");
				if ($iPosScript!==false)
				{
					$admin = true;
					$namespacedef = "admin.";				
					
					if (strpos($strHref,":engine")!==false)
					{
						$sResult = $this->_context->UrlXmlNukeAdmin().substr($sResult,strlen("admin:engine")+$iPosScript);
						
					}
					else
					{
						if (strpos($strHref,".")!== false)
						{
							$namespacedef = "";
						}
						$sResult = substr($sResult, 0, $iPosScript).$this->_context->UrlModule()."?module=".$namespacedef.str_replace("?","&",substr($sResult,strlen("admin:")+$iPosScript));
					}
				}
				else
				{
					return $strHref;
				}
			}
		}

		$iPosQuestion = strpos($sResult,"?");
		$XML = $this->ExtractPairQueryString($sResult, "xml");
		$XSL = $this->ExtractPairQueryString($sResult, "xsl");
		$SITE = $this->ExtractPairQueryString($sResult, "site");
		$LANG = $this->ExtractPairQueryString($sResult, "lang");
		$fullLink = ($this->_context->ContextValue("xmlnuke.USEFULLPARAMETER") == "true");
		if ($iPosQuestion!==false)
		{
			if ( (($SITE == "") && $fullLink) || (!$fullLink && ($SITE=="") && ($this->_context->getSite()!= $this->_context->ContextValue("xmlnuke.DEFAULTSITE"))) )
			{
				$sResult = $sResult."&site=".$this->_context->getSite();
			}
			if ( (($XSL == "") && !$admin && $fullLink) || (!$fullLink && ($XSL=="") && ($this->_context->getXsl()!= $this->_context->ContextValue("xmlnuke.DEFAULTPAGE"))) )
			{
				$sResult = $sResult."&xsl=".($this->_context->getXsl() == "index" ? $this->_context->ContextValue("xmlnuke.DEFAULTPAGE") : $this->_context->getXsl());
			}
			if ($XML == "" && $fullLink)
			{
				$sResult = $sResult."&xml=".$this->_context->getXml();
			}
			if ( ($LANG == "" && $fullLink)  || (!$fullLink && ($LANG=="") && (strpos("!".$this->_context->ContextValue("HTTP_ACCEPT_LANGUAGE"), "!".$this->_context->Language()->getName()) === false) ) )
			{
				$sResult = $sResult."&lang=".strtolower($this->_context->Language()->getName());
			}
		}
		return $this->_context->VirtualPathAbsolute($sResult);
	}

	/**
	*@param DOMDocument $xmlDom
	*@return void
	*@desc Process XHTML file and replace the tags [param:...] to XMLNuke context values
	*/
	public function ProcessParameters($xmlDom)
	{
//		echo "<br><pre>".htmlentities($xmlDom->saveXML())."</pre><br>";
		
		$nodeRoot = $xmlDom->documentElement;

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

					if ($result != "")
					{
						//Todos os atributos que possuem link devem passar por esse tratamento
						if(strtolower($attribs->item($i)->nodeName) == "href"||
							strtolower($attribs->item($i)->nodeName) == "action"||
								strtolower($attribs->item($i)->nodeName) == "onclick")
							 
						{
							$result = str_replace("&amp;","&",$result);
							$attribs->item($i)->nodeValue = htmlentities($result);
						}
						else 
						{
							$attribs->item($i)->nodeValue = $result;
						}							
						
					}

					$i++;

				}

			}
		}
		elseif ($node->nodeType == XML_TEXT_NODE)
		{
			
			$result = $this->CheckParameters($node->nodeValue);

			if ($result != "")
			{
				// If test below is True RESULT contains HTML TAGS. These tags need be processed
				// Otherwise, go ahead!
				if (strpos($result,"<")!==false)
				{
					$result = str_replace("&amp;","&",$result);
					
					try
					{
						$nodeToProc = XmlUtil::CreateXmlDocumentFromStr("<root>" . $result . "</root>", false)->documentElement;
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
				$param = substr($param, 0, $iStart).$this->_context->ContextValue($paramDesc).substr($param,$iEnd+ 1);
				$iStart = strpos($param,"[param:");
				
			}
		}
		return $param;
	}

}



interface IProcessParameter
{
	public function getParameter();
}

?>
