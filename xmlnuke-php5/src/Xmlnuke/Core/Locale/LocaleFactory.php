<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  Acknowledgments to: Yuri Bastos Wanderley
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
 * LocaleFactory creates the proper CultureInfo and assign it to CurrentThread. 
 * 
 * This classes enable output from Data, Currency, numbers and others are localized properly from the Language specified.
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Locale;

use ByJG\AnyDataset\Repository\AnyDataset;
use ByJG\AnyDataset\Repository\IteratorFilter;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Enum\Relation;
use Xmlnuke\Util\FileUtil;

class LocaleFactory
{
	
	/**
	*@param 
	*@return void
	*@desc 
	*/
	private function __construct()
	{
	}

	protected static $_locales = array();

	private static $_localeData = null;

	private static $_localeDbCache = array();


	/**
	 *
	 * @param type $field
	 * @param type $value
	 * @return \ByJG\AnyDataset\Repository\SingleRow
	 */
	public static function getInfoLocaleDB($field, $value)
	{
		if (self::$_localeData == null)
		{
			$file = new \Xmlnuke\Core\Processor\AnydatasetSetupFilenameProcessor('locale');
			self::$_localeData = new \ByJG\AnyDataset\Repository\AnyDataset($file);
		}

		if (!isset(self::$_localeDbCache[$field]))
		{
			$filter = new \ByJG\AnyDataset\Repository\IteratorFilter();
			$filter->addRelation($field, \Xmlnuke\Core\Enum\Relation::Contains, $value);
			$it = self::$_localeData->getIterator($filter);
			if ($it->hasNext())
				self::$_localeDbCache[$field] = $it->moveNext();
			else
			{
				$sr = new \ByJG\AnyDataset\Repository\SingleRow();
				\Xmlnuke\Core\Engine\Context::getInstance()->WriteWarningMessage("The language $value was not found in locale.anydata.xml file");
				$sr->AddField('name', $value . ' ???');
				$sr->AddField('shortname', $value);
				self::$_localeDbCache[$field] = $sr;
			}
		}
		
		return self::$_localeDbCache[$field];
	}

	/**
	 * Get a CulturuInfo class from the Language Name in the 5 letters format. Example: pt-br, en-us
	 * @param string $lang
	 * @param Context $context
	 * @return \CultureInfo
	 */
	public static function GetLocale($lang)
	{
		if (array_key_exists($lang, LocaleFactory::$_locales))
			return LocaleFactory::$_locales[$lang];
		
		$locale = new CultureInfo($lang);

		LocaleFactory::$_locales[$lang] = $locale;

		return $locale;
	}
}

?>
