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
 * This class call all of other xmlnuke classes and return the XML/XSL processed. 
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Engine;

class XmlNukeEngine
{
	const OUTPUT_TRANSFORMED_DOC = "-";
	const OUTPUT_XML = "xml";
	const OUTPUT_JSON = "json";
	
	/**
	 * Context
	 *
	 * @var Context
	 */
	private $_context = null;

	/**
	 * @var bool
	 */
	protected $_outputResult = "";
	/**
	 * @var string
	 */
	protected $_extractNodes = "";
	/**
	 * @var string
	 */
	protected $_extractNodesRoot = "xmlnuke";
	
	protected $_extraParams = array();

	/**
	 * Known $extraParams:
	 *     root_node = XML Root Node
	 *     json_funcion = return a JSON function instead a single JSON
	 * 
	 * @param Context $context
	 * @param string $outputResult
	 * @param string $extractNodes
	 * @param string $extractNodesRoot
	 */
	public function __construct(
			$context, 
			$outputResult = XmlNukeEngine::OUTPUT_TRANSFORMED_DOC, 
			$extractNodes = "", 
			$extraParams = array()
	)
	{
		$this->_context = $context;
		if (is_bool($outputResult))
		{
			$outputResult = ($outputResult ? XmlNukeEngine::OUTPUT_TRANSFORMED_DOC : XmlNukeEngine::OUTPUT_XML);
		}
		$this->_outputResult = $outputResult;
		$this->_extractNodes = $extractNodes;
		
		if (!is_array($extraParams))
			throw new InvalidArgumentException("Engine extra parameters must be an array");
		else
			$this->_extraParams = $extraParams;
		
		if (array_key_exists("root_node", $this->_extraParams) && $this->_extraParams["root_node"] != null)
			$this->_extractNodesRoot = $this->_extraParams["root_node"];
	}

	/**
	*@desc Transform XML/XSL documents from the current XMLNuke Context.
	*@return DOMDocument - Return the XHTML result
	*/
	public function TransformDocumentNoArgs()
	{
		// Creating FileNames will be used in this functions.
		$xmlCacheFile = new XMLCacheFilenameProcessor($this->_context->getXml());
		$cacheName = $xmlCacheFile->FullQualifiedNameAndPath();

		$result = $this->_context->getXSLCacheEngine()->get($cacheName, 7200);

		// Check if file cache already exists
		// If exists read it from there;
		if (($this->_outputResult == XmlNukeEngine::OUTPUT_TRANSFORMED_DOC) && ($result !== false))
		{
			return $result;
		}
		// If not exists process XML/XSL file now;
		else
		{
			// Creating FileNames will be used in this functions.
			$xmlFile = new XMLFilenameProcessor($this->_context->getXml());
			// Transform Document
			$result = $this->TransformDocumentFromDOM($this->getXmlDocument($xmlFile));

			// Save cache file - NOCACHE: Doesn't Save; Otherwise: Allways save
			if (!$this->_context->getNoCache() && ($this->_outputResult == XmlNukeEngine::OUTPUT_TRANSFORMED_DOC))
			{
				$this->_context->getXSLCacheEngine()->set($cacheName, $result);
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
		$ttl = $module->useCache();

		$cacheProc = new XMLCacheFilenameProcessor($module->getCacheId());
		$cacheName = $cacheProc->FullQualifiedNameAndPath();

		$cacheEngine = $module->getCacheEngine();
		$result = $cacheEngine->get($cacheName, $ttl);

		$getFromCache = ($ttl !== false)
				&& ($this->_outputResult == XmlNukeEngine::OUTPUT_TRANSFORMED_DOC)
				&& ($result !== false);
			;

		$saveToCache = (($ttl !== false) && ($this->_outputResult == XmlNukeEngine::OUTPUT_TRANSFORMED_DOC));

		if (!$getFromCache)
		{
			if ($saveToCache)
				$cacheEngine->lock($cacheName);

			//IXmlnukeDocument
			$px = $module->CreatePage();
			if (is_null($px) || !($px instanceof IXmlnukeDocument)) {
				$cacheEngine->unlock($cacheName);
				throw new EngineException("The method CreatePage must return a IXmlnukeDocument", 756);
			}
			
			//DOMNode
			try
			{
				$xmlDoc = $px->makeDomObject();
				$nodePage = $xmlDoc->getElementsByTagName("page")->item(0);

				if ($nodePage != null)
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
					$xslFile = new XSLFilenameProcessor("admin" . FileUtil::Slash() . "admin_page");
					$xslFile->setFilenameLocation(ForceFilenameLocation::PathFromRoot);
					//Pendente
					$xslFile->UseFileFromAnyLanguage();
					$result = $this->TransformDocument($xmlDoc, $xslFile);
				}
			}
			catch (Exception $ex)
			{
				$cacheEngine->unlock($cacheName);
				throw $ex;
			}

			$cacheEngine->unlock($cacheName);
			
			if ($saveToCache)
				$cacheEngine->set($cacheName, $result, $ttl);
			
			return $result;
		}
		else
		{
			return $result;
		}
	}

	
	public function TransformDocumentRemote($url)
	{
		$cachename = str_replace(".", "_", "REMOTE-" . UsersBase::getSHAPassword($url));
		$cacheFile = new XMLCacheFilenameProcessor($cachename);

		$cacheEngine = $this->_context->getXSLCacheEngine();
		$file = $cacheFile->FullQualifiedNameAndPath();

		$result = $cacheEngine->get($file, 60);
		if ($result !== false)
		{
			return $result;
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
			
			$cacheEngine->set($file, $result);

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
		$allFile = new XMLFilenameProcessor("_all");
		$indexFile = new XMLFilenameProcessor("index");

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
		$xslFile = new XSLFilenameProcessor($this->_context->getXsl());
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
		$pattern = "/(?P<plugin>((\w+)\.)+\w+)\[(?P<param>([#']?[\w]+[#']?\s*,?\s*)+)\]/";
		$xmlRoot = $xml->documentElement;
		$xmlRootAttributes = $xmlRoot->attributes;
		if ($xmlRootAttributes != null)
		{
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
					$match = preg_match_all($pattern, $attr->value, $matches);
					for($iCount=0;$iCount<$match;$iCount++)
					{
						$param = explode(",", $matches["param"][$iCount]);
						for ($i=0;$i<=4;$i++)
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
						$plugin = PluginFactory::LoadPlugin($matches["plugin"][$iCount], "", $param[0], $param[1], $param[2], $param[3], $param[4]);
						if (!($plugin instanceof IXmlnukeDocumentObject))
						{
							throw new InvalidArgumentException("The attribute in XMLNuke need to implement IXmlnukeDocumentObject interface");
						}
						$plugin->generateObject($xmlRoot);
					}
				}
			}
		}
		
		// Check if there is no XSL template
		if ($this->_outputResult != XmlNukeEngine::OUTPUT_TRANSFORMED_DOC)
		{
			if ($this->_extractNodes == "")
			{
				$outDocument = $xml;
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
				$outDocument = $retDocument;
			}

			if ($this->_outputResult == XmlNukeEngine::OUTPUT_JSON)
			{
				return XmlUtil::xml2json($outDocument, $this->_extraParams["json_function"]);
			}
			else // Default XML.
			{
				return $outDocument->saveXML();
			}
		}

		$this->_context->setXsl($xslFile->ToString());
		// Set up a transform object with the XSLT file
		//XslTransform xslTran = new XslTransform();
		$xslTran = new XSLTProcessor();
		$snippetProcessor = new SnippetProcessor($xslFile);
		//Uri
		try
		{
			$uri = $snippetProcessor->getUriFromXsl($xslFile, $this->_context);
		}
		catch (XMLNukeException $ex)
		{
			throw new EngineException("Cannot load XSL file. The following error occured: ". $ex->getMessage(), 751);
		}
		//Process smipets and put teh xsl StyleShet		
		
		try 
		{
			$xsl = $snippetProcessor->IncludeSnippet($uri);
		}
		catch (XMLNukeException $ex)
		{
			throw new EngineException("Cannot load XSL cache file. The following error occured: ". $ex->getMessage(), 752);
		}
		$xsl = FileUtil::CheckUTF8Encode($xsl);
		$xslDom = new DOMDocument();
		@$xslDom->loadXML($xsl);
		PHPWarning::LoadXml("TransformDocument", $xsl);
		$xslTran->importStyleSheet($xslDom);

		// Create Argument List
		$xslTran->setParameter("", "xml", $this->_context->getXml());
		$xslTran->setParameter("", "xsl", $this->_context->getXsl());
		$xslTran->setParameter("", "site", $this->_context->getSite());
		$xslTran->setParameter("", "lang", $this->_context->Language()->getName());
		$xslTran->setParameter("", "module", $this->_context->getModule());
		$xslTran->setParameter("", "transformdate", date("Y-m-d H:i:s") );
		$xslTran->setParameter("", "urlbase", $this->_context->ContextValue("xmlnuke.URLBASE"));
		$xslTran->setParameter("", "engine", "PHP");
		
		//Transform and output		
		$xtw = $xslTran->transformToXML($xml);	
		$xhtml = new DOMDocument();		
		@$xhtml->loadXML($xtw);
		PHPWarning::LoadXml("TransformDocument", $xtw);

		// Reload XHTML result to process PARAM and HREFs
		$paramProcessor = new ParamProcessor();
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
		$arrCt = $this->_context->getSuggestedContentType();
		if ($arrCt["content-type"] == "text/html")
		{
			return FileUtil::CheckUTF8Encode(strtr($xhtml->saveHTML(), array("></br>"=>"/>")));
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
			$xmlFileNotFound = new XMLFilenameProcessor("notfound");
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
