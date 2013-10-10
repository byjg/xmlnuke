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

use Xmlnuke\Util\FileUtil;

class UploadFilenameProcessor extends FilenameProcessor
{
	/**
	 * Use singlename like path/to/file/filename.* to get the source file extension
	 * @param string $singlename
	 * @return void
	 */
	public function __construct($singlename)
	{
		parent::__construct($singlename);
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

?>