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

class PersistUtil
{

	/**
	*@var string
	*/
	private $_lang;
	/**
	*@var string
	*/
	private $_repositoryDir;
	/**
	 * @var bool
	 */
	private $_hashedDir = true;

	/// <summary>
	/// Default constructor - initializes all fields to default values
	/// </summary>
	//Parameters : String
	public function PersistUtil($repositoryDir, $lang, $createdir = false)
	{
		$this->_repositoryDir = $repositoryDir;
		$this->_lang = $lang;
		if ($createdir)
		{
			FileUtil::ForceDirectories($repositoryDir);
		}
	}
	
	public function setHashedDir($value)
	{
		$this->_hashedDir = $value;
	}

	//Parameter: String
	public function getFullFileName($documentName)
	{
		if ($this->_hashedDir)
		{
			return 
				$this->_repositoryDir.
				$this->_lang.
				self::getSlash().$documentName[0].
				self::getSlash().$documentName[1].
				self::getSlash().$documentName;
		}
		else 
		{
			return 
				$this->_repositoryDir.
				$this->_lang.
				self::getSlash().$documentName;
		}
	}
	//Parameter: String
	public function existsDocument($documentName)
	{
		return file_exists($this->getFullFileName($documentName));
	}
	//Parameter: String
	public function getName($document)
	{
		$i=strpos($document,'#');

		if ($i!== false)
		{
			return substr($document, 0, $i);

		}
		else
		{
			return $document;
		}
	}
	//Parameter: String
	public function getXPath($document)
	{
		$i=strpos($document,'#');

		if ($i!== false)
		{
			return substr($document, $i+1);
		}
		else
		{
			return "";
		}
	}
	//Parameter: String
	public function getPathFromFile($filename)
	{
		$i = strrpos($filename, self::getSlash());

		if ($i!== false)
		{
			return substr($filename, 0, $i);
		}
		else
		{
			return $filename;
		}
	}
	//Parameter: String
	public function getNameFromFile($filename)
	{
		$i = strrpos($filename, self::getSlash());

		if ($i!== false)
		{

			return substr($filename, $i+1);
		}
		else
		{
			return $filename;
		}
	}
	//Parameters : string
	public function getDocument($document, $rootNode)
	{
		$documentName = self::getName($document);
		$xpath = self::getXPath($document);

		if (!self::existsDocument($documentName))
		{
			throw new KernelException(600, "Document " . $document . " doesn't exists in repository");
		}

		$documentName = self::getFullFileName($documentName);
		
		if ($xpath == "")
		{
			$doc = XmlUtil::CreateXmlDocumentFromFile($documentName);
		}
		else
		{
			$doc = XmlUtil::CreateXmlDocumentFromStr("< $rootNode />", false);
			$source = XmlUtil::CreateXmlDocumentFromFile($documentName);
			
			$DocXpath = new DOMXPath($source);
			$nodes = $DocXpath->query($xpath);

			foreach ($nodes as $node)
			{
				$newNode = $doc->importNode($node);

				$doc->documentElement->appendChild($newNode);
			}

		}

		return $doc;
	}

	//SOBRECARGA SUPRIMIDA
	//Parameters: string $directory, BTree $btree, bool $saveDocs, string $filemask)
	public function importDocuments($directory, $btree, $saveDocs, $filemask = null)
	{
		if ($filemask == null)
		{
			$filemask = ".xml";
		}

		$files = FileUtil::RetrieveFilesFromFolder($directory, $filemask);
		
		if($files != null)
		{
			foreach($files as $file)
			{
				$doc = XmlUtil::CreateXmlDocumentFromFile($file);
				if ($saveDocs)
				{				
					$btree = self::saveDocument(self::getNameFromFile($file), $xmldoc, $btree);
				}
				else
				{
					$btree = BTreeUtil::navigateNodes($doc->documentElement, self::getNameFromFile($file)."#/", $btree);
				}
			}

		}

		if (strpos($directory, ".svn")===false)
		{
			$directories = FileUtil::RetrieveSubFolders($directory);
			if($directories!= null)
			{
				foreach ($directories as $dir)
				{
					$btree = self::importDocuments($dir, $btree, $saveDocs, $filemask);
				}
			}
		}

		return $btree;

	}

	//Parameters : string $documentName, DOMDocument $xml, BTree $btree
	public function saveDocument($documentName, $xml, $btree)
	{	
		$btree = BTreeUtil::navigateNodes($xml->documentElement, $documentName."#/", $btree);
		$documentName = self::getFullFileName($documentName);
		FileUtil::ForceDirectories(self::getPathFromFile($documentName));
		$xml->normalize();
		XmlUtil::SaveXmlDocument($xml, $documentName);
		return $btree;
	}

	public static function getSlash()
	{
		return self::isWindowsOS() ? "\\" : "/";
	}

	public static function isWindowsOS()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
			return true;
		} else {
			return false;
		}
	}
}
?>
