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
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlInputDateTime extends XmlnukeDocumentObject
{
	/**
	*@var string
	*/
	protected $_name;
	/**
	*@var string
	*/
	protected $_caption;
	/**
	*@var string
	*/
	protected $_date;
	/**
	*@var string
	*/
	protected $_time;
	/**
	 * @var Context
	 */
	protected $_context;
	/**
	 * @var DATEFORMAT
	 */
	protected $_type;
	/**
	 * @var bool
	 */
	protected $_showHour;
	/**
	 * @var bool
	 */
	protected $_yearmin;
	/**
	 * @var bool
	 */
	protected $_yearmax;
	
	/**
	 * Enter description here...
	 *
	 * @param string $caption
	 * @param string $name
	 * @param DATEFORMAT $dateformat
	 * @param bool $showhour
	 * @param string $date
	 * @param string $time
	 * @return XmlInputDateTime
	 */
	public function __construct($caption, $name, $dateformat, $showhour = true, $date="", $time="")
	{ 
		$this->_caption = $caption;
		$this->_name = $name;
		$this->_dateformat = $dateformat;
		if ($date != "")
		{
			$this->_date = $date;
		}
		else 
		{
			$this->_date = DateUtil::Today($dateformat);
		}
		if ($time != "")
		{
			$this->_time = $time;
		}
		else 
		{
			$this->_time = date("H:i");
		}
		$this->_showHour = $showhour;
		$this->_yearmin = -10;
		$this->_yearmax = +10;
	}
	
	public static function ParseSubmit($context, $name, $getTime=true)
	{
		$date = $context->ContextValue($name);
		if ($getTime)
		{
			$date .= " " . $context->ContextValue($name."_hour") . ":" . $context->ContextValue($name."_minute");
		}
		return $date;
	}
	
	public function setYearBounds($yearmin, $yearmax)
	{
		$this->_yearmin = $yearmin;
		$this->_yearmax = $yearmax;
	}
	
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		$datetimebox = XmlUtil::CreateChild($current, "datetimebox");
		$date = DateUtil::TimeStampFromStr($this->_date, $this->_dateformat);
		XmlUtil::AddAttribute($datetimebox, "name", $this->_name); 
		XmlUtil::AddAttribute($datetimebox, "caption", $this->_caption); 
		XmlUtil::AddAttribute($datetimebox, "day", date('j', $date)); // Day without leading zeros
		XmlUtil::AddAttribute($datetimebox, "month", date('n', $date)); // Month without leading zeros
		XmlUtil::AddAttribute($datetimebox, "year", date('Y', $date));
		XmlUtil::AddAttribute($datetimebox, "dateformat", INPUTTYPE::DATE);
		XmlUtil::AddAttribute($datetimebox, "date", $this->_date);
		if ($this->_showHour)
		{
			$time = explode(":", $this->_time);
			XmlUtil::AddAttribute($datetimebox, "showhour", "true"); 
			XmlUtil::AddAttribute($datetimebox, "hour", $this->removeLeadingZero($time[0])); // Hour without leading zeros
			XmlUtil::AddAttribute($datetimebox, "minute", $this->removeLeadingZero($time[1]));
		}
		XmlUtil::AddAttribute($datetimebox, "yearmin", $this->_yearmin);
		XmlUtil::AddAttribute($datetimebox, "yearmax", $this->_yearmax);
	}
	
	protected function removeLeadingZero($str)
	{
		if (strlen($str) > 1)
		{
			if ($str[0] == "0")
			{
				return substr($str, 1);
			}
		}
		return $str;
	}
}
?>
