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
		$xslCache = new XSLCacheFilenameProcessor($this->_file->ToString());
		$xslName = $xslCache->FullQualifiedNameAndPath();


		$cacheEngine = $this->_context->getXSLCacheEngine();
		$result = $cacheEngine->get($xslName, 7200);

		// Create a new stream representing the file to be written to,
		// and write the stream cache the stream
		// from the external location to the file (only if doesnt exist)
		if ($result === false)
		{
			$content = "";

			$lines = file($xslPath);
			try
			{
				$cacheEngine->lock($xslName);

				foreach($lines as $line)
				{
					$iStart = strpos($line,"<xmlnuke-");
					while ($iStart!==false)
					{
						$iEnd = strpos($line,">",$iStart + 1);
						$snippetFile = substr($line, $iStart + 9, $iEnd - $iStart - 10);
						$snippet = new SnippetFilenameProcessor(trim($snippetFile));
						if (!FileUtil::Exists($snippet))
							throw new NotFoundException("Snippet " . $snippet->FullQualifiedNameAndPath () . " not found" );
						$fStreamSnippet = FileUtil::OpenFile ($snippet->FullQualifiedNameAndPath(), "r");
						$sReadSnippet = FileUtil::ReadFile($fStreamSnippet, filesize($snippet->FullQualifiedNameAndPath()));
						FileUtil::CloseFile($fStreamSnippet);
						$line = substr($line,0,$iStart). self::LF . $sReadSnippet . substr($line,$iEnd+ 1);
						$iStart = strpos($line,"<xmlnuke-");
					}
					$content .= $line;
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
