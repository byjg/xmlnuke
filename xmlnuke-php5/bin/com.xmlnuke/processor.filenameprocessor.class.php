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

class ForceFilenameLocation
{
	const UseWhereExists = "UseWhereExists";
	const PathFromRoot = "PathFromRoot";
	const SharedPath = "SharedPath";
	const PrivatePath = "PrivatePath";
	const DefinePath = "DefinePath";
}

/**
 *FilenameProcessor is the class who process the Single argument filename (example: home or page)
 *and get directory and localized informations about this file from FilenameType and XmlNukeContext.
 */
abstract class FilenameProcessor
{
	/**
	 *@var Context
	 */
	protected $_context = null;
	/**
	 *@var string
	 */
	protected $_singlename = "";
	/**
	 *@var bool
	 */
	protected $_pathfromroot = false;
	/**
	 *@var string
	 */
	protected $_languageid = null;//string
	/**
	*@var ForceFilenameLocation
	*/
	protected $_filenameLocation = ForceFilenameLocation::UseWhereExists;
	/**
	 * @var String
	 */
	protected $_path;

	/**
	 *@param string $singlename
	 *@param Context $context
	 *@return string
	 *@desc Constructor
	 */
	public function __construct($singlename, $context)
	{
		if ($context instanceof Context)
		{
			if (strpos($singlename, "..")===false)
			{
				$this->_singlename = $singlename;
				$this->_context = $context;
				$this->_languageid = strtolower($this->_context->Language()->getName());
				$this->_filenameLocation = ForceFilenameLocation::UseWhereExists;
			}
			else
			{
				throw new Exception("Invalid file name");
			}
		}
		else
		{
			throw new Exception("You must pass a valid Xmlnuke Context to FilenameProcessor");
		}
	}

	/**
	 *@return string
	 *@desc
	 */
	public function ToString()
	{
		return $this->_singlename;
	}

	/**
	 *@param string $name
	 *@param string $languageId
	 *@return string Return the SingleName plus languageId
	 *@desc Add the XmlNuke context language to the SingleName
	 */
	protected function addLanguage($name, $languageId)
	{
		return $name.".".$languageId;
	}


	/**
	 *@param string $name
	 *@return string
	 *@desc Remove language information from a filename
	 */
	public function removeLanguage($name)
	{
		$i = strrpos($name,$this->Extension());
		if ($i !== false)
		{
			$name = substr($name,0,$i);
		}
		$i = strrpos($name,".");
		if ($i !== false)
		{
			$name = substr($name,0,$i);
		}
		return $name;
	}

	/**
	 *@param
	 *@return Extension
	 *@desc Return the proper extension to the file. Uses the FilenameType enum.
	 */
	public abstract function Extension();

	/**
	 *@return string
	 *@desc Return the Path Suggested from XMLNuke Context. This path uses the FilenameType enum.
	 */
	public function PathSuggested()
	{
		$path = $this->PrivatePath();

		switch ($this->_filenameLocation)
		{
			case ForceFilenameLocation::PathFromRoot:
				{
					$path = $this->_context->SharedRootPath();
					break;
				}
			case ForceFilenameLocation::UseWhereExists:
				{
					// Test:
					// - If File in PrivatePath exists (this is the first option!!)
					// - If file in SharedPath exists (this is the second option!!)
					// - Otherwise references
					if (FileUtil::Exists($this->PrivatePath() . $this->FullQualifiedName()))
					{
						$path = $this->PrivatePath();
					}
					else if (FileUtil::Exists($this->SharedPath() . $this->FullQualifiedName()))
					{
						$path = $this->SharedPath();
					}
					else
					{
						$path = $this->PrivatePath();
					}
					break;
				}
			case ForceFilenameLocation::PrivatePath:
				{
					$path = $this->PrivatePath();
					break;
				}
			case ForceFilenameLocation::SharedPath:
				{
					$path = $this->SharedPath();
					//				Debug::PrintValue("Shared: ".$path);
					break;
				}
			case ForceFilenameLocation::DefinePath:
				{
					$path = $this->_path;
					//				Debug::PrintValue("Define: ".$path);
					break;
				}
		}
		return $path;
	}

	public abstract function SharedPath();

	public abstract function PrivatePath();

	/**
	 *@param
	 *@return string
	 *@desc Return the Path, FileName with LanguageID and Extension
	 */
	public function FullQualifiedNameAndPath()
	{
		return $this->PathSuggested().$this->FullQualifiedName();
	}

	/**
	 *@param
	 *@return string
	 *@desc Return the FileName with LanguageID and Extension
	 */
	public function FullQualifiedName()
	{
		return $this->virtualFullName().$this->Extension();
	}

	/**
	 *@param string $xml
	 *@param string $xsl
	 *@param string $languageId
	 *@return string
	 *@desc Return the FileName with specific single name, LanguageID and Extension
	 */
	public abstract function FullName($xml, $xsl, $languageId);

	/**
	 *@param
	 *@return string
	 *@desc Return the FileName with LanguageID from XMLNuke context
	 */
	public function virtualFullName()
	{
		return $this->FullName($this->_singlename, $this->_context->getXsl(), $this->_languageid);
	}

	/**
	 *@param
	 *@return string
	 *@desc Get the property PathFromRoot. Affects the property PathSuggested.
	 *If true get the path from ROOTDIR config, otherwise get the suggested path. Default: FALSE.
	 */
	public function getFilenameLocation()
	{
		return $this->_filenameLocation;
	}

	/**
	 * Sets the property PathFromRoot. Affects the property PathSuggested.
	 * If true get the path from ROOTDIR config, otherwise get the suggested path. Default: FALSE.
	 *
	 * @param ForceFilenameLocation $location
	 * @param String $path
	 * @return string
	 */
	public function setFilenameLocation($location, $path = "")
	{
		$this->_filenameLocation = $location;
		$this->_path = $path;
	}

	/**
	 *@param
	 *@return Context
	 *@desc
	 */
	public function getContext()
	{
		return $this->_context;
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function getLanguageId()
	{
		return $this->_languageid;
	}

	/**
	 *@param string $value
	 *@return void
	 *@desc
	 */
	public function setLanguageId($value)
	{
		$this->_languageid = strtolower($value);
	}
	/**
	 *@param string $filename
	 *@return void
	 *@desc
	 */
	public static function StripLanguageInfo($filename)
	{
		$i = strrpos($filename,".");
		$filename = substr($filename,0,$i);
		$i = strrpos($filename,".");
		return substr($filename,0,$i);
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function Exists()
	{
		return FileUtil::Exists($this->FullQualifiedNameAndPath());
	}

	/**
	 *@param
	 *@return bool
	 *@desc
	 */
	public function UseFileFromAnyLanguage()
	{
		$langAvail = $this->_context->LanguagesAvailable();
		$langAvail["en-us"] = "English (Default)";
		if (!$this->exists())
		{
			foreach(array_keys($langAvail) as $key)
			{
				$this->setLanguageId($key);
				if ($this->Exists())
				{
					break;
				}
			}
		}
		return $this->exists();
	}

}

/**
 *XMLFilenameProcessor class
 */
class XMLFilenameProcessor extends FilenameProcessor
{
	/**
	 *@param string $singlename
	 *@param Context $context
	 *@return string
	 *@desc
	 */
	public function __construct($singlename, $context )
	{
		parent::__construct($singlename, $context);
		// The function to manipulate HASHED XML files is in BTREEUTILS...
		// So nothing to change HERE (instead XMLCacheFileName and XSLCacheFileName).
		$this->_filenameLocation = ForceFilenameLocation::PrivatePath;
	}

	/**
	 *@param
	 *@return string
	 *@desc Implementing
	 */
	public function Extension()
	{
		return ".xml";
	}

	/**
	 *@param
	 *@return string
	 *@desc Implementing
	 */
	public function SharedPath()
	{
		return $this->PrivatePath();
	}

	/**
	 *@param
	 *@return string
	 *@desc Implementing
	 */
	public function PrivatePath()
	{
		return $this->_context->XmlPath();
	}

	/**
	 *@param string $xml
	 *@param string $xsl
	 *@param string $languageId
	 *@return string
	 *@desc
	 */
	public function FullName($xml, $xsl, $languageId)
	{
		return $this->addLanguage($xml,$languageId);
	}
}

class XSLFilenameProcessor extends FilenameProcessor
{
	/**
	 *@param string $singlename
	 *@param Context $context
	 *@return string
	 *@desc
	 */
	public function __construct($singlename,$context)
	{
		parent::__construct($singlename, $context);
	}

	/**
	 *@param
	 *@return string
	 *@desc Implementing
	 */
	public function Extension()
	{
		return ".xsl";
	}

	/**
	 *@param
	 *@return string
	 *@desc Implementing
	 */
	public function SharedPath()
	{
		return $this->_context->SharedRootPath() . "xsl" . FileUtil::Slash();
	}

	/**
	 *@param
	 *@return string
	 *@desc Implementing
	 */
	public function PrivatePath()
	{
		return $this->_context->XslPath();
	}

	/**
	 *@param
	 *@return string
	 *@desc Implementing
	 */
	public function FullName($xml, $xsl, $languageId)
	{
		return parent::addLanguage($xsl,$languageId);
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function virtualFullName()
	{
		return $this->FullName("", $this->_singlename, $this->_languageid );
	}
}

class XMLCacheFilenameProcessor extends FilenameProcessor
{
	/**
	 *@param string $singlename
	 *@param Context $context
	 *@return string
	 *@desc
	 */
	public function __construct($singlename,$context)
	{
		$singlename = $singlename;

		parent::__construct($singlename, $context);
		if ($this->_context->CacheHashedDir())
		{
			$this->setFilenameLocation(ForceFilenameLocation::DefinePath, $this->_context->CachePath() . $singlename[0] . FileUtil::Slash() . $singlename[1] . FileUtil::Slash());
			if (!FileUtil::Exists($this->PathSuggested()))
			{
				FileUtil::ForceDirectories($this->PathSuggested());
			}
		}
		else
		{
			$this->_filenameLocation = ForceFilenameLocation::PrivatePath;
		}
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function Extension()
	{
		return ".php.cache.html";
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function SharedPath()
	{
		return $this->PrivatePath();
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function PrivatePath()
	{
		return $this->_context->CachePath();
	}

	/**
	 *@param string $xml
	 *@param string $xsl
	 *@param string $languageId
	 *@return string
	 *@desc
	 */
	public function FullName($xml, $xsl, $languageId)
	{
		return str_replace(FileUtil::Slash(), "#", $this->_context->getSite() . "." . $xsl . "." . $languageId . "." . $xml);
	}
}

class XSLCacheFilenameProcessor extends FilenameProcessor
{
	/**
	 *@param string $singlename
	 *@param Context $context
	 *@return string
	 *@desc
	 */
	public function __construct($singlename,$context)
	{
		parent::__construct($singlename, $context);
		if ($this->_context->CacheHashedDir())
		{
			$this->setFilenameLocation(ForceFilenameLocation::DefinePath, $this->_context->CachePath() . $singlename[0] . FileUtil::Slash() . $singlename[1] . FileUtil::Slash());
			if (!FileUtil::Exists($this->PathSuggested()))
			{
				FileUtil::ForceDirectories($this->PathSuggested());
			}
		}
		else
		{
			$this->_filenameLocation = ForceFilenameLocation::PrivatePath;
		}
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function Extension()
	{
		return ".php.cache.xsl";
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function SharedPath()
	{
		return $this->PrivatePath();
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function PrivatePath()
	{
		return $this->_context->CachePath();
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function FullName($xml, $xsl, $languageId)
	{
		return str_replace(FileUtil::Slash(), "#", $this->_context->getSite() . "." . $xsl . "." . $languageId);
	}
}

class OfflineFilenameProcessor extends FilenameProcessor
{
	/**
	 *@param string $singlename
	 *@param Context $context
	 *@return string
	 *@desc
	 */
	public function __construct($singlename,$context)
	{
		parent::__construct($singlename,$context);
		$this->_filenameLocation = ForceFilenameLocation::PrivatePath;
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function Extension()
	{
		return ".offline.html";
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function SharedPath()
	{
		return $this->PrivatePath();
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function PrivatePath()
	{
		return $this->_context->OfflinePath();
	}

	/**
	 *@param string $xml
	 *@param string $xsl
	 *@param string $languageId
	 *@return string
	 *@desc
	 */
	public function FullName($xml, $xsl, $languageId)
	{
		return $xml.".".$this->addLanguage($xsl,$languageId);
	}
}

abstract class AnydatasetBaseFilenameProcessor extends FilenameProcessor
{
	/**
	 *@param string $singlename
	 *@param Context $context
	 *@return string
	 *@desc
	 */
	public function __construct($singlename,$context)
	{
		parent::__construct($singlename,$context);
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function Extension()
	{
		return ".anydata.xml";
	}

	/**
	 *@param string $xml
	 *@param string $xsl
	 *@param string $languageId
	 *@return string
	 *@desc
	 */
	public function FullName($xml, $xsl, $languageId)
	{
		return $xml;
	}
}


class AnydatasetFilenameProcessor extends AnydatasetBaseFilenameProcessor
{
	/**
	 *@param string $singlename
	 *@param Context $context
	 *@return void
	 *@desc
	 */
	public function __construct($singlename,$context)
	{
		parent::__construct($singlename,$context);
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function SharedPath()
	{
		return $this->_context->SharedRootPath() . "anydataset" . FileUtil::Slash();
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function PrivatePath()
	{
		return $this->_context->CurrentSitePath() . "anydataset" . FileUtil::Slash();
	}
}


class AnydatasetSetupFilenameProcessor extends AnydatasetBaseFilenameProcessor
{

	/**
	 *@param string $singlename
	 *@param Context $context
	 *@return void
	 *@desc
	 */
	public function __construct($singlename, $context)
	{
		parent::__construct($singlename,$context);
		$this->_filenameLocation = ForceFilenameLocation::SharedPath;
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function SharedPath()
	{
		return $this->_context->SharedRootPath() . "setup" . FileUtil::Slash();
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function PrivatePath()
	{
		return $this->SharedPath();
	}

}


class AnydatasetBackupFilenameProcessor extends AnydatasetBaseFilenameProcessor
{
	/**
	 * Constructor Method
	 *
	 * @param string $singlename
	 * @param Context $context
	 */
	public function __construct($singlename, $context)
	{
		parent::__construct($singlename,$context);
		//		$this->_filenameLocation = ForceFilenameLocation::SharedPath;
	}

	/**
	 * Shared Path
	 *
	 * @return string
	 */
	public function SharedPath()
	{
		return $this->_context->SharedRootPath() . "backup"  . FileUtil::Slash() . "setup"  . FileUtil::Slash();
	}

	/**
	 * Private Path
	 *
	 * @return string
	 */
	public function PrivatePath()
	{
		return $this->_context->CurrentSitePath() . "backup"  . FileUtil::Slash() . "setup"  . FileUtil::Slash();
	}

	public function Extension()
	{
		return ".backup.xml";
	}
}

class AnydatasetBackupLogFilenameProcessor extends AnydatasetBaseFilenameProcessor
{
	/**
	 * Constructor Method
	 *
	 * @param string $singlename
	 * @param Context $context
	 */
	public function __construct($singlename, $context)
	{
		parent::__construct($singlename,$context);
		$this->_filenameLocation = ForceFilenameLocation::SharedPath;
	}

	/**
	 * Shared Path
	 *
	 * @return string
	 */
	public function SharedPath()
	{
		return $this->_context->SharedRootPath() . "setup"  . FileUtil::Slash();
	}

	/**
	 * Private Path
	 *
	 * @return string
	 */
	public function PrivatePath()
	{
		return $this->_context->CurrentSitePath() . "setup". FileUtil::Slash();
	}

	public function Extension()
	{
		return ".log.xml";
	}
}


class AnydatasetLangFilenameProcessor extends AnydatasetBaseFilenameProcessor
{
	/**
	 *@param string $singlename
	 *@param Context $context
	 *@return void
	 *@desc
	 */
	public function __construct($singlename, $context )
	{
		parent::__construct($singlename, $context);
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function SharedPath()
	{
		return $this->_context->SharedRootPath() . "lang" . FileUtil::Slash();
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function PrivatePath()
	{
		return $this->_context->CurrentSitePath() . "lang" . FileUtil::Slash();
	}

	public function Extension()
	{
		return ".lang" . parent::Extension();
	}
}


class BackupFilenameProcessor extends FilenameProcessor
{
	/**
	 *@param string $singlename
	 *@param Context $context
	 *@return void
	 *@desc
	 */
	public function __construct($singlename,$context)
	{
		parent::__construct($singlename,$context);
		$this->_filenameLocation = ForceFilenameLocation::PrivatePath;
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function Extension()
	{
		return ".php.xmlnuke.module";
	}

	/**
	 *@param string $xml
	 *@param string $xsl
	 *@param string $languageId
	 *@return string
	 *@desc
	 */
	public function FullName($xml, $xsl, $languageId)
	{
		return $xml;
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function SharedPath()
	{
		return $this->_context->SharedRootPath() . "backup" . FileUtil::Slash();
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function PrivatePath()
	{
		return $this->_context->CurrentSitePath() . "backup" . FileUtil::Slash();
	}
}


class SnippetFilenameProcessor extends FilenameProcessor
{
	/**
	 *@param string $singlename
	 *@param Context $context
	 *@return void
	 *@desc
	 */
	public function __construct($singlename,$context)
	{
		parent::__construct($singlename,$context);
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function Extension()
	{
		return ".inc";
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function SharedPath()
	{
		return $this->_context->SharedRootPath() . "snippet" . FileUtil::Slash();
	}

	/**
	 *@param
	 *@return string
	 *@desc
	 */
	public function PrivatePath()
	{
		return $this->_context->CurrentSitePath() . "snippet" . FileUtil::Slash();
	}

	/**
	 *@param string $xml
	 *@param string $xsl
	 *@param string $languageId
	 *@return string
	 *@desc
	 */
	public function FullName($xml, $xsl, $languageId)
	{
		return "snippet_" . $xml;
	}
}

class UploadFilenameProcessor extends FilenameProcessor
{
	/**
	 * Use singlename like path/to/file/filename.* to get the source file extension
	 * @param string $singlename
	 * @param Context $context
	 * @return void
	 */
	public function __construct($singlename, $context )
	{
		parent::__construct($singlename, $context);
		$this->_filenameLocation = ForceFilenameLocation::PrivatePath;
	}

	/**
	 *@param
	 *@return string
	 *@desc Path to shared directory
	 */
	public function SharedPath()
	{
		return $this->_context->SystemRootPath();
	}

	/**
	 *@return string
	 *@desc Path to private directory
	 */
	public function PrivatePath()
	{
		return $this->_context->CurrentSitePath() . FileUtil::Slash() . "upload" . FileUtil::Slash();
	}
	/**
	 *@return string
	 */
	public function Extension()
	{
		return "";
	}

	/**
	 *@param string $name - File Name
	 *@param string $xsl
	 *@param string $languageId
	 *@return string
	 *@desc
	 */
	public function FullName($name, $xsl, $languageId)
	{
		return $name;
	}
}

class ImageFilenameProcessor extends FilenameProcessor
{
	/**
	 *@var string
	 */
	protected $_subpath;
	/**
	 *@var string
	 */
	protected $_extension;

	/**
	 * Use singlename as path/to/file/*.* to get the source file name and extension
	 * @param string $singlename
	 * @param Context $context
	 */
	public function __construct($singlename, $context )
	{
		$parts = pathinfo($singlename);
		if ($parts["dirname"] != '.')
		{
			$slash = FileUtil::Slash();
			$this->_subpath = $parts["dirname"] . $slash;
			if ($this->_subpath{0} == $slash)
			{
				$this->_subpath = substr($this->_subpath, 1);
			}
		}
		else
		{
			$this->_subpath = '';
		}
		$this->_extension = $parts["extension"];
		if (empty($this->_extension))
		{
			$this->_extension = 'jpg';
		}
		$singlename = basename($parts["basename"], $this->Extension());
		parent::__construct($singlename, $context);
	}

	/**
	 *@param
	 *@return string
	 *@desc Path to shared directory
	 */
	public function SharedPath()
	{
		// SystemRootPath is necessary to effort XMLNuke DATA directory may be located everywhere.
		return $this->_subpath;
	}

	/**
	 * Config file extension. Don't use '.'
	 *
	 * @param string $ext
	 */
	public function setExtension($ext)
	{
		if ($ext{0} == '.')
		{
			$ext = substr($ext, 1);
		}
		$this->_extension = $ext;
	}

	/**
	 * Set a new name to file
	 *
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->_singlename = $name;
	}

	/**
	 *@return string
	 *@desc Path to private directory
	 */
	public function PrivatePath()
	{
		// SystemRootPath is necessary to effort XMLNuke DATA directory may be located everywhere.
		return "lib" . FileUtil::Slash() . $this->_subpath;
	}

	/**
	 *@return string
	 */
	public function Extension()
	{
		return ".$this->_extension";
	}

	/**
	 *@param string $name - File Name
	 *@param string $xsl
	 *@param string $languageId
	 *@return string
	 *@desc
	 */
	public function FullName($name, $xsl, $languageId)
	{
		return $name;
	}

	/**
	 *@return string
	 *@desc Base path without system root path
	 */
	public function BaseFullFileNameAndPath($fullname = null)
	{
		if (is_null($fullname))
		{
			$fullname = $this->FullQualifiedNameAndPath();
		}
		return str_replace($this->_context->SystemRootPath(), '', $fullname);
	}
}
?>
