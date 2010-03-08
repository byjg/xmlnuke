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

/* =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 * IMPORTANT NOTE:
 *
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
		return $this->_Language;
	}
	/**
	*@param string $lang
	*@return void
	*@desc
	*/
	public function setLanguage($lang)
	{
		$this->_Language = $lang;
	}


	/**
	*@param
	*@return string
	*@desc
	*/
	public function getCharSet()
	{
		return $this->_CharSet;
	}
	/**
	*@param string $CharSet
	*@return void
	*@desc
	*/
	public function setCharSet($CharSet)
	{
		$this->_CharSet = $CharSet;
	}


	public function __construct($language, $langstr = null)
	{
		if ($langstr == null)
		{
			$langstr = array();
		}
		$this->_name = $language;
		$systemLocale = $this->getIsoName();
		$this->_cultureActive = setlocale(LC_ALL, $systemLocale . ".UTF8", $systemLocale . ".UTF-8", $systemLocale, $this->_name);

		// Try to load in Windows if failed before
		$iLang = 0;
		while (!$this->_cultureActive && ($iLang < sizeof($langstr)))
		{
			$this->_cultureActive = setlocale(LC_ALL, $langstr[$iLang]);
			$iLang++;
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
		$number = str_replace($this->_localeConv["decimal_point"], ".", $number);
		if ($truncate)
		{
			$factor = pow(10, intval($this->_localeConv["frac_digits"]));
			$number = intval($number * $factor) / $factor;
		}
		if (!$intlSymbol)
		{
			$format = "%01." . $this->_localeConv["frac_digits"] . "f";
			$retorno = sprintf($format, $number);

			$moneySymbol = $this->getCurrencySymbol();
			if ($this->_localeConv["p_sep_by_space"]) $space = ' '; else $space = '';
			$retorno = ($this->_localeConv["p_cs_precedes"] ? "$moneySymbol$space$retorno" : "$retorno$space$moneySymbol");
		}
		else
		{
			$format = "%01." . $this->_localeConv["frac_digits"] . "F";
			$retorno = sprintf($format, $number);

			$retorno = $retorno . " " . $this->getIntlCurrencySymbol();
		}

		return $retorno;
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
