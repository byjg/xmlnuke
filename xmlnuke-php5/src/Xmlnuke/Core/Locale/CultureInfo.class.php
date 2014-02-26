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

namespace Xmlnuke\Core\Locale;

use Xmlnuke\Core\Enum\DATEFORMAT;
use Xmlnuke\Util\FileUtil;

/** 
 * Get the Culture Info based in the context
 *
 * <b>Important Note</b>
 * This class is Operational System dependant. Your OS must support the desired language to get work
 * specific regional settings.
 *
 * On Debian machines:
 *   uncomment or add the desired language in file /etc/locale.gen (examples: pt_BR, en_CA, de_DE, etc)
 *   run locale-gen from the shell
 *
 * On Windows Machines:
 *   ??
 *
 * @package xmlnuke
 */
class CultureInfo
{
	private $_name;
	private $_Language;
	private $_CharSet;
	private $_cultureActive;

	private $_localeConv;

	/**
	*@param
	*@return string
	*@desc Get name
	*/
	public function getName()
	{
		return $this->_name;
	}

	/**
	*@param
	*@return string
	*@desc
	*/
	public function getLanguage()
	{
		$row = LocaleFactory::getInfoLocaleDB('shortname', $this->getName());
		return $row->getField('name');
	}

	public function __construct($language, $langstr = null)
	{
		if ($langstr == null)
		{
			$langstr = array();
		}
		$this->_name = $language;
		$systemLocale = $this->getIsoName();
		$this->_cultureActive = setlocale(LC_ALL, $systemLocale . ".UTF8", $language . ".UTF-8", $systemLocale, $language, $this->_name);

		// Try to load in Windows if failed before
		if (!$this->_cultureActive)
		{
			$row = $this->getInfoLocaleDB('shortname', $this->getName());
			$langstr = $sr->getFieldArray("langstr");

			$iLang = 0;
			while (!$this->_cultureActive && ($iLang < count($langstr)))
			{
				$this->_cultureActive = setlocale(LC_ALL, $langstr[$iLang]);
				$iLang++;
			}
		}

		if (!$this->_cultureActive)
		{
			$firstPart = substr ($language, 0, 2);
			
			if(FileUtil::isWindowsOS())
				$complement = "";
			else
				$complement = "Only the languages available in 'locale -a' can be setted. On debian try execute 'apt-get install language-pack-$firstPart'.";

			echo "<br/>\n<b>Warning: </b> The culture language '$language' was not found. $complement\n<br/>";

			// I cant call the context here because it's call CultureInfo but the context is not complete yet.
			//$context = Context::getInstance();
			//$context->WriteWarningMessage ("");
		}
		
		$this->_localeConv = localeConv();
		#if (stripos(PHP_OS, 'win') !== false)
		#{
		#	$this->_localeConv["currency_symbol"] = utf8_encode($this->_localeConv["currency_symbol"]);
		#}

		#Debug::PrintValue($this->_localeConv, $langstr, $this->getRegionalMonthNames());
	}

	public function getIsoName()
	{
		$systemLocale = $this->_name;
		$arrLocale = explode("-", $systemLocale);
		$systemLocale = $arrLocale[0] . "_" . strtoupper($arrLocale[1]);

		return $systemLocale;
	}

	public function getIntlCurrencySymbol()
	{
		return $this->_localeConv["int_curr_symbol"];
	}

	public function getCurrencySymbol()
	{
		return $this->_localeConv["currency_symbol"];
	}
	public function getDecimalPoint()
	{
		return $this->_localeConv["decimal_point"];
	}


	public function getCultureActive()
	{
		return $this->_cultureActive;
	}

	public function getRegionalMonthNames()
	{
		$monthArray = array();
		for ($i=1; $i<=12; $i++)
		{
			$monthArray[$i] = strftime("%B", mktime(0, 0, 0, $i, 1, 2009));
			if (stripos(PHP_OS, 'win') !== false)
			{
				$monthArray[$i] = utf8_encode($monthArray[$i]);
			}
		}
		return $monthArray;
	}

	public function formatMoney($number, $intlSymbol = false, $truncate = false)
	{
		if ($truncate)
			$mask = '12.0';
		else
			$mask = 0;

		if ($intlSymbol)
			$formatted = trim(money_format("%{$mask}i", $number));
		else
			$formatted = trim(money_format("%{$mask}n", $number));

		return $formatted;
	}

	/**
	 * Enter description here...
	 *
	 * @return DATEFORMAT
	 */
	public function getDateFormat()
	{
		$date = strftime("%x", mktime(0, 0, 0, 1, 31, 2009));
		if (preg_match("/31[- \/.]0?1[- \/.](20)?09/", $date))
		{
			return DATEFORMAT::DMY;
		}
		elseif (preg_match("/0?1[- \/.]31[- \/.](20)?09/", $date))
		{
			return DATEFORMAT::MDY;
		}
		else
		{
			return DATEFORMAT::YMD;
		}
	}

	public function getDoubleVal($value)
	{
		return doubleval(str_replace($this->getDecimalPoint(), ".", $value));
	}

}

?>
