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
 * @package xmlnuke
 */
class LanguageFileTypes
{
	const ADMINMODULE = 1;
	const ADMININTERNAL = 2;
	const MODULE = 3;
	const OBJECT = 4;
	const INTERNAL = 999;
}

/**
 * @package xmlnuke
 */
class LanguageFactory
{
	/**
	 * Enter description here...
	 *
	 * @param Context $context
	 * @param LanguageFileTypes $type
	 * @param string $name
	 * @return LanguageCollection
	 */
	public static function GetLanguageCollection($context, $type, $name)
	{
		switch ($type) 
		{
			case LanguageFileTypes::ADMINMODULE:
				$name = "com-xmlnuke-".str_replace(".","-",strtolower($name));
				$langFile = new AnydatasetLangFilenameProcessor($name);
				break;
		
			case LanguageFileTypes::ADMININTERNAL:
				$langFile = new AdminModulesLangFilenameProcessor();
				break;
		
			case LanguageFileTypes::MODULE:
				$name = str_replace(".","-",strtolower($name));
				$langFile = new AnydatasetLangFilenameProcessor($name);
				break;
				
			case LanguageFileTypes::OBJECT:
				$name = str_replace(".class.php", "", basename($name));
				$name = str_replace(FileUtil::Slash(),"-",str_replace(".","-",strtolower($name)));
				$langFile = new AnydatasetLangFilenameProcessor($name);
				break;
				
			default:
				$langFile = new AnydatasetLangFilenameProcessor($name);
				break;
		}
		$lang = new LanguageCollection($context);
		$lang->LoadLanguages($langFile);
		return $lang;
	}
}
?>