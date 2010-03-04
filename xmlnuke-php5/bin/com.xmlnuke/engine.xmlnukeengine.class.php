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

/// <summary>
/// XmlNukeEngine class use a Facade Design Pattern. This class call all of other xmlnuke classes and return the XML/XSL processed
/// </summary>
class XmlNukeEngine
{
	/**
	 * Context
	 *
	 * @var Context
	 */
	private $_context = null;

	/**
	 * @var bool
	 */
	protected $_applyXslTemplate = true;
	/**
	 * @var string
	 */
	protected $_extractNodes = "";
	/**
	 * @var string
	 */
	protected $_extractNodesRoot = "xmlnuke";
	
	/**
	*@desc ParamProcessor constructor.
	*@param Context $context
	*@return void
	*/
	public function XmlNukeEngine($context, $applyXslTemplate = true, $extractNodes = "", $extractNodesRoot = "xmlnuke")
	{
		$this->_context = $context;
		$this->_applyXslTemplate = $applyXslTemplate;
		$this->_extractNodes = $extractNodes;
		$this->_extractNodesRoot = $extractNodesRoot;
	}

	/**
	*@desc Transform XML/XSL documents from the current XMLNuke Context.
	*@return DOMDocument - Return the XHTML result
	*/
	public function TransformDocumentNoArgs()
	{
		// Creating FileNames will be used in this functions.
		$xmlCacheFile = new XMLCacheFilenameProcessor($this->_context->getXml(), $this->_context);

		// Check if file cache already exists
		// If exists read it from there;
		if (FileUtil::Exists($xmlCacheFile->FullQualifiedNameAndPath()) && !$this->_context->getNoCache() && !$this->_context->getReset() && $this->_applyXslTemplate)
		{
			return FileUtil::QuickFileRead($xmlCacheFile->FullQualifiedNameAndPath());
		}
		// If not exists process XML/XSL file now;
		else
		{
			// Creating FileNames will be used in this functions.
			$xmlFile = new XMLFilenameProcessor($this->_context->getXml(), $this->_context);
			// Transform Document
			$result = $this->TransformDocumentFromDOM($this->getXmlDocument($xmlFile));

			// Save cache file - NOCACHE: Doesn't Save; Otherwise: Allways save
			if (!$this->_context->getNoCache() && $this->_applyXslTemplate)
			{
				try
				{
					FileUtil::QuickFileWrite($xmlCacheFile->FullQualifiedNameAndPath(), $result);
				}
				catch (Exception $ex)
				{
					echo "<br/><b>Warning:</b> I could not write to cache on file '" . basename($xmlCacheFile->FullQualifiedNameAndPath()) . "'. Switching to nocache=true mode. <br/>";					
				}
			}

			return $result;
		}
	}

	/**
	*@desc Transform XML/XSL documents from the user module process result.
	*@param IModule $module User module interface
	*@return DOMDocument - Return the XHTML result
	*/
	public function TransformDocumentFromModule($module)
	{
		$useCache = $module->useCache() && !$this->_context->getReset();
		if (!$useCache || !$module->hasInCache() || !$this->_applyXslTemplate)
		{
			//IXmlnukeDocument
			$px = $module->CreatePage();
			if (is_null($px) || !($px instanceof IXmlnukeDocument)) {
				throw new EngineException(756, "The method CreatePage must return a IXmlnukeDocument");
			}
			
			//DOMNode
			$xmlDoc = $px->makeDomObject();
			$nodePage = $xmlDoc->getElementsByTagName("page")->item(0);

			$this->addXMLDefault($nodePage);

			if (!($module->isAdmin()))
			{
				if (strpos($this->_context->getXsl(), "admin_page"))
				{
					$this->_context->setXsl($this->_context->ContextValue("xmlnuke.DEFAULTPAGE"));
				}
				$result = $this->TransformDocumentFromDOM($xmlDoc);
			}
			else
			{
				$xslFile = new XSLFilenameProcessor("admin" . FileUtil::Slash() . "admin_page", $this->_context);
				$xslFile->setFilenameLocation(ForceFilenameLocation::PathFromRoot);
				//Pendente
				$xslFile->UseFileFromAnyLanguage();
				$result = $this->TransformDocument($xmlDoc, $xslFile);
			}
			
			if ($useCache && $this->_applyXslTemplate)
			{
				$module->saveToCache($result);
			}
			
			return $result;
		}
		else
		{
			return $module->getFromCache();
		}
	}

	
	public function TransformDocumentRemote($url)
	{
		$cachename = str_replace(".", "_", "REMOTE-" . UsersBase::getSHAPassword($url));
		$cacheFile = new XMLCacheFilenameProcessor($cachename, $this->_context);
		
		$file = $cacheFile->FullQualifiedNameAndPath();
		if (file_exists($file))
		{
			$horaMod = filemtime($file);
			$tempo = intval((time()-$horaMod)/60);
			//Debug::PrintValue($tempo);
			if ($tempo > 30)
			{
				FileUtil::DeleteFileString($file);
			}
		}

		if (file_exists($file) && ($this->_context->getReset()==""))
		{
			return FileUtil::QuickFileRead($file);
		}
		else 
		{
			$xmlDoc = FileUtil::GetRemoteXMLDocument($url);
			
			$result = $this->TransformDocumentFromDOM($xmlDoc);

			$search = array ("'&(amp|#38);gt;'i",
			                 "'&(amp|#38);lt;'i"
			                 );
			
			$replace = array (">",
							  "<"
							  );

			$result = preg_replace($search, $replace, $result);
			
			FileUtil::QuickFileWrite($file, $result);

			return $result;		
		}
	}
	
	
	/**
	*@desc Get a xml node element to return ajax component
	*@param IModule $module User module interface
	*@param string $element Element name
	*@return DOMDocument - Return the XHTML result
	*/
	public function getDocumentElement($module, $element = "", $id = "")
	{
		$px = $module->CreatePage();
		if (is_null($px) || !($px instanceof PageXml))
		{
			return "<message>The return value of your CreatePage method is not a PageXml Class.</message>";
		}
		if (empty($element)) 
		{
			return XmlUtil::SaveXmlNodeToString($px->getRootNode());
		}
		
		//DOMNode
		$nodePage = $px->getRootNode();
		if ($element == "") {
			$element = "blockcenter";
		}
		$findedElements = XmlUtil::selectSingleNode($nodePage, $element);
		return XmlUtil::SaveXmlNodeToString($findedElements);
	}

	/**
	*@desc Private method used to add accessories XML (_all and index) into current XML file. Runtime only.
	*@param DOMNode $nodePage
	*@return void
	*/
	private function addXMLDefault($nodePage)
	{
		// Creating FileNames will be used in this functions.
		$allFile = new XMLFilenameProcessor("_all", $this->_context);
		$indexFile = new XMLFilenameProcessor("index", $this->_context);

		XmlUtil::AddNodeFromFile($nodePage, $allFile, "page");
		XmlUtil::AddNodeFromFile($nodePage, $indexFile, "xmlindex");

	}

	/**
	*@desc Transform XML/XSL documents from custom XmlDocument.
	*@param DOMDocument $xml
	*@return DOMDocument - Return the XHTML result
	*/
	public function TransformDocumentFromDOM($xml)
	{
		// Creating FileNames will be used in this functions.
		$xslFile = new XSLFilenameProcessor($this->_context->getXsl(), $this->_context);
		return $this->TransformDocument($xml, $xslFile);
	}

	/**
	*@desc Transform an XMLDocument object with an XSLFile
	*@param DOMDocument $xml
	*@param XSLFilenameProcessor $xslFile XSL File
	*@return string - The transformation string
	*/
	public function TransformDocument($xml, $xslFile)
	{
		// Add a custom XML based on attribute xmlobjet inside root
		// Example:
		// <page include="base.namespace, file.php" xmlobject="plugin.name[param1, param2]">
		$pattern = "/(?<plugin>((\w+)\.)+\w+)\[(?<param>([#']?[\w]+[#']?\s*,?\s*)+)\]/";
		$xmlRoot = $xml->documentElement;
		$xmlRootAttributes = $xmlRoot->attributes;
		foreach ($xmlRootAttributes as $attr)
		{
			if ($attr->nodeName == "include")
			{
				$param = explode(",", $attr->nodeValue);
				if (count($param) == 1)
				{
					ModuleFactory::IncludePhp(trim($param[0]));
				}
				else
				{
					ModuleFactory::IncludePhp(trim($param[0]), trim($param[1]));
				}
			}
			elseif ($attr->nodeName == "xmlobject")
			{
				$match = preg_match($pattern, $attr->value, $matches);
				if ($match)
				{
					$param = explode(",", $matches["param"]);
					for ($i=0;$i<=3;$i++)
					{
						if (count($param) < $i+1)
						{
							$param[] = null;
						}
						elseif ($param[$i] == "#CONTEXT#")
						{
							$param[$i] = $this->_context;
						}
						else
						{
							$param[$i] = trim($param[$i]);
						}
					}
					$plugin = PluginFactory::LoadPlugin($matches["plugin"], "", $param[0], $param[1], $param[2], $param[3]);
					if (!($plugin instanceof IXmlnukeDocumentObject))
					{
						throw new Exception("The attribute in XMLNuke need to implement IXmlnukeDocumentObject interface");
					}
					$plugin->generateObject($xmlRoot);
				}
			}
		}

		// Check if there is no XSL template
		if (!$this->_applyXslTemplate)
		{
			if ($this->_extractNodes == "")
			{
				return $xml->saveXML();
			}
			else 
			{
				$nodes = XmlUtil::selectNodes($xml->documentElement, "/".$this->_extractNodes);
				$retDocument = XmlUtil::CreateXmlDocumentFromStr("<".$this->_extractNodesRoot."/>", false);
				$nodeRoot = $retDocument->documentElement;
				XmlUtil::AddAttribute($nodeRoot, "xpath", $this->_extractNodes);
				XmlUtil::AddAttribute($nodeRoot, "site", $this->_context->getSite());
				foreach ($nodes as $node) 
				{
					$nodeToAdd = XmlUtil::CreateChild($nodeRoot, $node->nodeName, "");
					$attributes = $node->attributes;
					foreach ($attributes as $value) 
					{
						XmlUtil::AddAttribute($nodeToAdd, $value->nodeName, $value->nodeValue);
					}
					XmlUtil::AddNodeFromNode($nodeToAdd, $node);
				}
				return $retDocument->saveXML();
			}
		}

		$this->_context->setXsl($xslFile->ToString());
		// Set up a transform object with the XSLT file
		//XslTransform xslTran = new XslTransform();
		$xslTran = new XSLTProcessor();
		$snippetProcessor = new SnippetProcessor($this->_context, $xslFile);
		//Uri
		try
		{
			$uri = $snippetProcessor->getUriFromXsl($xslFile, $this->_context);
		}
		catch (XMLNukeException $ex)
		{
			throw new EngineException(751, "Not able to load XSL file. The following error occured: ". $ex->getMessage());
		}
		//Process smipets and put teh xsl StyleShet		
		
		try 
		{
			$xsl = $snippetProcessor->IncludeSnippet($uri);
		}
		catch (XMLNukeException $ex)
		{
			throw new EngineException(752, "Not able to load XSL cache file. The following error occured: ". $ex->getMessage());
		}
		$xsl = FileUtil::CheckUTF8Encode($xsl);
		$xslTran->importStyleSheet(DOMDocument::loadXML($xsl));

		// Create Argument List
		$xslTran->setParameter("", "xml", $this->_context->getXml());
		$xslTran->setParameter("", "xsl", $this->_context->getXsl());
		$xslTran->setParameter("", "site", $this->_context->getSite());
		$xslTran->setParameter("", "lang", $this->_context->Language()->getName());
		$xslTran->setParameter("", "transformdate", date("Y-m-d H:i:s") );
		$xslTran->setParameter("", "urlbase", $this->_context->ContextValue("xmlnuke.URLBASE"));
		$xslTran->setParameter("", "engine", "PHP");
		
		//Transform and output		
		$xtw = $xslTran->transformToXML($xml);		
		$xhtml = new DOMDocument();		
		$xhtml->loadXML($xtw);

		// Reload XHTML result to process PARAM and HREFs
		$paramProcessor = new ParamProcessor($this->_context);
		$paramProcessor->AdjustToFullLink($xhtml, "A", "HREF");
		$paramProcessor->AdjustToFullLink($xhtml, "FORM", "ACTION");
		$paramProcessor->AdjustToFullLink($xhtml, "AREA", "HREF");
		$paramProcessor->AdjustToFullLink($xhtml, "LINK", "HREF");
		if ($this->_context->ContextValue("xmlnuke.ENABLEPARAMPROCESSOR"))
		{
			$paramProcessor->ProcessParameters($xhtml);
		}


		// ATENCAO: O codigo gerado pelo saveXML faz com que elementos vazios sejam
		//      comprimidos. Exemplo: <table />
		//      para o HTML isso eh ruim. Logo o metodo deve ser saveHTML que deixa o tag
		//      assim: <table></table>
		if ($this->_context->getSuggestedContentType() == "text/html")
		{
			return FileUtil::CheckUTF8Encode($xhtml->saveHTML());
		}
		else 
		{
			return FileUtil::CheckUTF8Encode($xhtml->saveXML());
		}
	}

	/**
	*@desc Get a XMLDocument from a XMLFile
	*@param XMLFilenameProcessor $xmlFile XML File
	*@return DOMDocument
	*/
	public function getXmlDocument( $xmlFile )
	{		
		$this->_context->setXml($xmlFile->ToString());

		// Load XMLDocument and add ALL and INDEX nodes
		$xmlDoc =  new DOMDocument;
		try
		{
			if (!($xmlFile->getFilenameLocation() == ForceFilenameLocation::PathFromRoot)) // Get From Repository...
			{
				$xmlDoc = $this->_context->getXMLDataBase()->getDocument($xmlFile->FullQualifiedName(),null);
			}
			else
			{
				$xmlDoc = XmlUtil::CreateXmlDocumentFromFile($xmlFile->FullQualifiedNameAndPath());
			}
		}
		catch (Exception $ex)
		{
			$xmlFileNotFound = new XMLFilenameProcessor("notfound", $this->_context);
			if ($this->_context->getXMLDataBase()->existsDocument($xmlFileNotFound->FullQualifiedName()))
			{
				$xmlDoc = $this->_context->getXMLDataBase()->getDocument($xmlFileNotFound->FullQualifiedName(),null);
			}
			else
			{
				throw $ex;
			}
		}
		$xmlRootNode = $xmlDoc->getElementsByTagName("page")->item(0);

		if ($xmlRootNode != null) //Index.<lang>.xml doensnt have node PAGE
		{
			$this->addXMLDefault($xmlRootNode);
		}

		return $xmlDoc;
	}

}
?>
