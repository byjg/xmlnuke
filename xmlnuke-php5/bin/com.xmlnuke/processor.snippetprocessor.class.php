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
*/
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
	public function SnippetProcessor($context, $file)
	{
		$this->_context = $context;
		$this->_file = $file;
	}

	
	protected $_fileCacheName = "";
	protected $_fileCacheHandle = null;
	protected $_fileCacheContent = "";
	
	protected function OpenCache($filename)
	{
		$this->_fileCacheName = $filename;
		$this->_fileCacheContent = "";
		$this->_fileCacheHandle = null;
		
		if (!$this->_context->getNoCache())
		{
			try
			{
				$this->_fileCacheHandle = FileUtilKernel::OpenFile($this->_fileCacheName, "w+");
			}
			catch (Exception $ex)
			{
				echo "<br/><b>Warning:</b> I could not write to cache on file '" . basename($this->_fileCacheName) . "'. Switching to nocache=true mode. <br/>";
				$this->_fileCacheHandle = null;
			}
		}
		//else
		//{
		//	echo "<br/><b>No Cache</b><br/>";
		//}
	}
	protected function WriteToCache($content)
	{
		if ($this->_fileCacheHandle)
		{
			FileUtilKernel::WriteFile($this->_fileCacheHandle, $content);
		}
		else
		{
			$this->_fileCacheContent .= $content;
		}
	}
	protected function CloseCache()
	{
		if ($this->_fileCacheHandle)
		{
			FileUtilKernel::CloseFile($this->_fileCacheHandle);
			return FileUtil::QuickFileRead($this->_fileCacheName);
		}
		else
		{
			return $this->_fileCacheContent;
		}
	}
	
	/**
	*@param string $xslPath
	*@return string 
	*@desc Return the XSL with snippet to/from cache.
	*/
	public function IncludeSnippet($xslPath)
	{
		$xslCache = new XSLCacheFilenameProcessor($this->_file->ToString(), $this->_context);
		// Create a new stream representing the file to be written to,
		// and write the stream cache the stream
		// from the external location to the file (only if doesnt exist)
		if (!FileUtil::Exists($xslCache->FullQualifiedNameAndPath()) || $this->_context->getNoCache() || $this->_context->getReset())
		{
			$lines = file($xslPath);
			try
			{
				$this->OpenCache($xslCache->FullQualifiedNameAndPath());
				foreach($lines as $line)
				{
					$iStart = strpos($line,"<xmlnuke-");
					while ($iStart!==false)
					{
						$iEnd = strpos($line,">",$iStart + 1);
						$snippetFile = substr($line, $iStart + 9, $iEnd - $iStart - 10);
						$snippet = new SnippetFilenameProcessor(trim($snippetFile), $this->_context);						
						$fStreamSnippet = FileUtilKernel::OpenFile ($snippet->FullQualifiedNameAndPath(), "r");
						$sReadSnippet = FileUtilKernel::ReadFile($fStreamSnippet, filesize($snippet->FullQualifiedNameAndPath()));
						FileUtilKernel::CloseFile($fStreamSnippet);
						$line = substr($line,0,$iStart). self::LF . $sReadSnippet . substr($line,$iEnd+ 1);
						$iStart = strpos($line,"<xmlnuke-");
					}
					$this->WriteToCache($line);
				}
				return $this->CloseCache();
			}
			catch (Exception $ex)
			{
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
			return FileUtil::QuickFileRead($xslCache->FullQualifiedNameAndPath());
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
				throw new EngineException(754, "XSL document \"" . $xslFile->FullQualifiedName() . "\" not found in local site or shared locations.");
			}
		}
		return FileUtil::getUriFromFile($xslFile->FullQualifiedNameAndPath());
	}
}


?>
