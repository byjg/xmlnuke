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
 * Get the Culture Info based in the context
 *
 *
 * https://www.simonholywell.com/post/2015/07/international-php-dates-with-intl/
 * https://www.sitepoint.com/localizing-dates-currency-and-numbers-with-php-intl/
 * https://gist.github.com/bryanburgers/f375ea3086a0ed029636
 *
 * @package xmlnuke
 */
class CultureInfo
{
	private $_name;
	private $_Language;
	private $_CharSet;
	private $_cultureActive;

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
        $numberFormat = new \NumberFormatter($this->_name, \NumberFormatter::CURRENCY);
        return $numberFormat->getSymbol(\NumberFormatter::INTL_CURRENCY_SYMBOL);
	}

	public function getCurrencySymbol()
	{
        $numberFormat = new \NumberFormatter($this->_name, \NumberFormatter::CURRENCY);
        return $numberFormat->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
	}
	public function getDecimalPoint()
	{
        $a = new \NumberFormatter($this->_name, \NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
        return $a->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
	}


	public function getCultureActive()
	{
		return $this->_cultureActive;
	}

	public function getRegionalMonthNames()
	{
        $f = new IntlDateFormatter($this->_name, null, null, null, null, 'MMMM');

		$monthArray = array();
		for ($i=1; $i<=12; $i++)
		{
            $monthArray[$i] = $f->format(new DateTime("2018-$i-01"));
		}
		return $monthArray;
	}

	public function formatMoney($number, $intlSymbol = false, $truncate = false)
	{
        $fmt = new NumberFormatter( $this->_name, $intlSymbol ? NumberFormatter::CURRENCY : NumberFormatter::DECIMAL);

        if (!is_numeric($number)) {
            $number = $this->getDoubleVal($number);
        }
        if ($truncate)
        {
            $numberFormatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);
            $numberFormatter->setAttribute(NumberFormatter::ROUNDING_MODE, NumberFormatter::ROUND_HALFEVEN);
        }

        return $fmt->format($number);
    }

	/**
	 * Enter description here...
	 *
	 * @return DATEFORMAT
	 */
	public function getDateFormat()
	{
        $fmt = new IntlDateFormatter(
            $this->_name,
            IntlDateFormatter::SHORT,
            IntlDateFormatter::NONE
        );
        $pattern = $fmt->getPattern();
        $pattern = preg_replace(
            '/(?<!y)yy(?!y)/',
            'yyyy',
            $pattern);
        $fmt->setPattern($pattern);
        $date = $fmt->format(new DateTime('2018-01-31'));
		if (preg_match("/31[- \/.]0?1[- \/.](20)?18/", $date))
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

	public function getDoubleVal($value = 0)
	{
		if (doubleval($value) === $value) {
			return $value;
		}
        $fmt = new NumberFormatter( $this->_name, NumberFormatter::DECIMAL );
        return $fmt->parse($value);
	}

}

?>
