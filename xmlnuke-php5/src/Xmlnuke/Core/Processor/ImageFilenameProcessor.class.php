<?php

/*
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
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
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 */

/**
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Processor;

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
	public function __construct($singlename)
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
		parent::__construct($singlename);
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