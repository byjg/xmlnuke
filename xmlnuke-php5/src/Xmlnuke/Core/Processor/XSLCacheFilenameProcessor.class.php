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

class XSLCacheFilenameProcessor extends FilenameProcessor
{
	/**
	 * @param string $singlename
	 */
	public function __construct($singlename)
	{
		parent::__construct($singlename);
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


?>