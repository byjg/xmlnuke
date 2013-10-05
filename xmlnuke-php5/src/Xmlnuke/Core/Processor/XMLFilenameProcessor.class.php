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
 * XMLFilenameProcessor class
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Processor;

class XMLFilenameProcessor extends FilenameProcessor
{
	/**
	 *@param string $singlename
	 *@return string
	 *@desc
	 */
	public function __construct($singlename)
	{
		parent::__construct($singlename);
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
		return $this->_context->XmlPath() . $this->getLanguageId() . FileUtil::Slash();
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


?>