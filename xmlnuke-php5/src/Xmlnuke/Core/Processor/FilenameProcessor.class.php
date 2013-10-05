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

namespace Xmlnuke\Core\Processor;

use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Util\FileUtil;

/**
 * FilenameProcessor is the class who process the Single argument filename (example: home or page)
 * and get directory and localized informations about this file from FilenameType and XmlNukeContext.
 * 
 * @package xmlnuke
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
	 *@return string
	 *@desc Constructor
	 */
	public function __construct($singlename)
	{
		if (strpos($singlename, "..")===false)
		{
			$this->_singlename = $singlename;
			$this->_context = Context::getInstance();
			$this->_languageid = strtolower($this->_context->Language()->getName());
			$this->_filenameLocation = ForceFilenameLocation::UseWhereExists;
		}
		else
		{
			throw new InvalidArgumentException("Invalid file name");
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

?>
