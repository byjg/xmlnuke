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
*This class is a XmlUrlResolver descendant. It process the XSL file and add the proper snippets. 
*If this XSL file already process use from cache.
 * @package xmlnuke
*/
namespace Xmlnuke\Core\Processor;

use Exception;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Exception\EngineException;
use Xmlnuke\Core\Exception\SnippetNotFoundException;
use Xmlnuke\Util\FileUtil;

class SnippetProcessor
{
	/**
	*@var Context
	*/
	private $_context;
	/**
	*@var XSLFilenameProcessor
	*/
	private $_file;
	/**
	*end of line delimiters
	*@var string
	*/
	const LF = "\n";

	/**
	*@param Context $context
	*@param XSLFilenameProcessor $file
	*@return void 
	*@desc SnippetProcessor constructor
	*/
	public function __construct($file)
	{
		$this->_context = Context::getInstance();
		$this->_file = $file;
	}

	/**
	*@param string $xslPath
	*@return string 
	*@desc Return the XSL with snippet to/from cache.
	*/
	public function IncludeSnippet($xslPath)
	{
		$xslName = $this->_file->ToString() . '.' . strtolower($this->_context->Language()->getName()) . '.xsl';

		$cacheEngine = $this->_context->getXSLCacheEngine();
		$result = $cacheEngine->get($xslName, 7200);

		// Create a new stream representing the file to be written to,
		// and write the stream cache the stream
		// from the external location to the file (only if doesnt exist)
		if ($result === false)
		{
			$content = "";

			$content = file_get_contents($xslPath);
			try
			{
				$cacheEngine->lock($xslName);

				$iStart = strpos($content,"<xmlnuke-");
				while ($iStart!==false)
				{
					$iEnd = strpos($content,">",$iStart + 1);
					$snippetFile = substr($content, $iStart + 9, $iEnd - $iStart - 10);
					$snippet = new SnippetFilenameProcessor(trim($snippetFile));
					if (!FileUtil::Exists($snippet))
						throw new SnippetNotFoundException("Snippet " . $snippet->FullQualifiedNameAndPath () . " not found" );

					$sReadSnippet = file_get_contents($snippet->FullQualifiedNameAndPath());
					
					$content = substr($content,0,$iStart). self::LF . $sReadSnippet . substr($content,$iEnd+ 1);
					$iStart = strpos($content,"<xmlnuke-");
				}

				$cacheEngine->unlock($xslName);

				$cacheEngine->set($xslName, $content);

				return $content;
			}
			catch (Exception $ex)
			{
				$cacheEngine->unlock($xslName);
				if (FileUtil::Exists($xslCache->FullQualifiedNameAndPath()))
				{
					FileUtil::DeleteFile($xslCache);
				}
				throw $ex;
			}
		}
		else
		{
			// Already in Cache
			return $result;
		}
	}
	
	/**
	*@param XSLFilenameProcessor $xslFile
	*@param Context $context
	*@return Uri 
	*@desc 
	*/
	public static function getUriFromXsl($xslFile, $context)
	{
		//System.Uri uri = snippetProcessor.ResolveUri(null, xslFile.FullQualifiedNameAndPath());

		if (!$xslFile->Exists())
		{
			if (!$xslFile->UseFileFromAnyLanguage())
			{
				throw new EngineException("XSL document \"" . $xslFile->FullQualifiedName() . "\" not found in local site or shared locations.", 754);
			}
		}
		return FileUtil::getUriFromFile($xslFile->FullQualifiedNameAndPath());
	}
}


?>
