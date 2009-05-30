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
*LocaleFactory creates the proper CultureInfo and assign it to CurrentThread. This classes enable output from Data, Currency, 
*numbers and others are localized properly from the Language specified.
*/
class LocaleFactory
{
	
	/**
	*@param 
	*@return void
	*@desc 
	*/
	private function LocaleFactory()
	{
	}
	
	/**
	*@param string $lang - Language Name in the 5 letters format. Example: pt-br, en-us
	*@return CultureInfo
	*@desc Static method to Create the CultureInfo and assign it to the CurrentThread
	*/
	public static function GetLocale($lang, $context)
	{
		// ***************************
		// * ATENTION - Dont do it again!!!!!!!
		// *
		$path = $context->SharedRootPath(); 
		// *
		// * The line above is necessary, because, FilenameProcessor need Language,
		// * but Language isnt created yet. Dont do it again!!!!!!!
		// ******************************************************************
		$localeFile = new AnyDataSet($path. FileUtil::Slash() . "setup" . FileUtil::Slash() . "locale.anydata.xml");
		$itf = new IteratorFilter();
		$itf->addRelation("shortname", Relation::Contains, $lang);
		
		$it = $localeFile->getIterator($itf);
		if ($it->hasNext())
		{
			$sr = $it->moveNext();
			
			$locale = new CultureInfo($sr->getField("shortname"), $sr->getFieldArray("langstr"));
			$locale->setLanguage($sr->getField("locale"));
			$locale->setCharSet("utf-8");
		}
		else
		{
			$locale = new CultureInfo($lang);
			$locale->setLanguage("?");//?????????
			$locale->setCharSet("utf-8");
		}

		return $locale;
	}
}

?>
