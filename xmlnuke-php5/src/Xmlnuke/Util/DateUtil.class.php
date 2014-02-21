<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  PHP Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
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

namespace Xmlnuke\Util;

use Xmlnuke\Core\Enum\DATEFORMAT;
use Xmlnuke\Core\Enum\DateParts;
use Xmlnuke\Core\Exception\DateUtilException;

class DateUtil
{
	/**
	*@desc Return the current Data
	*@param DATEFORMAT $dateFormat
	*@return string 
	*/
	public static function Today($dateFormat = DATEFORMAT::YMD, $separator = "/", $hour = false)
	{
		$dateStr = getdate();
		return DateUtil::FormatDate($dateStr[0], $dateFormat, $separator, $hour);
	}
	
	/**
	*@desc Get the Date string from a date in timestamp format
	*@param int $timestamp
	*@param DATEFORMAT $dateFormat
	*@return string 
	*/
	public static function FormatDate($timestamp, $dateFormat = DATEFORMAT::YMD, $separator = "/", $hour = false)
	{
		if (strpos(".-/", $separator)===false)
		{
			throw new DateUtilException("Date separator must be . - or /");
		}
		switch ($dateFormat)
		{
			case DATEFORMAT::DMY:
			{
				$mask = "%d$separator%m$separator%Y";
				break;
			}
			case DATEFORMAT::MDY:
			{
				$mask = "%m$separator%d$separator%Y";
				break;
			}
			default:
			{
				$mask = "%Y$separator%m$separator%d";
				break;
			}
		}
		
		if ($hour)
		{
			$mask .= " %H:%M:%S";
		}
		
		return strftime($mask, $timestamp);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $source
	 * @param DATEFORMAT $sourceFormat
	 * @param DATEFORMAT $targetFormat
	 * @return string
	 */
	public static function ConvertDate($source, $sourceFormat, $targetFormat, $separator = "/", $hour = false)
	{
		$timestamp = DateUtil::TimeStampFromStr($source, $sourceFormat, $hour);
		return DateUtil::FormatDate($timestamp, $targetFormat, $separator, $hour);
	}

	/**
	 * Check if a date is Valid
	 *
	 * @param type $date
	 * @param type $format
	 * @param type $separator
	 * @param type $hour
	 * @return type
	 */
	public static function Validate($date, $format = DATEFORMAT::YMD, $separator = "/")
	{
		$timestamp = DateUtil::TimeStampFromStr($date, $format);
		$dateCheck = DateUtil::FormatDate($timestamp, $format, $separator, true);

		$date = $date . substr('--/--/---- 00:00:00', strlen($date));

		$timestamp2 = DateUtil::TimeStampFromStr($dateCheck, $format);
		return ($timestamp == $timestamp2) && ($date == $dateCheck);
	}
	
	/**
	*@desc Add days in a specfied date
	*@param string $date
	*@param int $days
	*@param DATEFORMAT $dateFormat
	*@return string 
	*/
	public static function DateAdd($date, $days, $dateFormat = DATEFORMAT::YMD)
	{
		$timestamp = strtotime("$days day", DateUtil::TimeStampFromStr($date, $dateFormat));
		return DateUtil::FormatDate($timestamp, $dateFormat);
	}
	
	/**
	*@desc Days Difference between two dates.
	*@param string $newestDate
	*@param string $oldiestDate
	*@param DATEFORMAT $dateFormat
	*@return int 
	*/
	public static function DateDiff($newestDate, $oldiestDate, $dateFormat = DATEFORMAT::YMD)
	{
		$diff =  floor((DateUtil::TimeStampFromStr($newestDate, $dateFormat) - DateUtil::TimeStampFromStr($oldiestDate, $dateFormat)) / 86400);
		return $diff;
	}
	
	/**
	*@desc Get the timestamp from a date string
	*@param strint $date
	*@param DATEFORMAT $dateFormat
	*@return string 
	*/
	public static function TimeStampFromStr($date, $dateFormat = DATEFORMAT::YMD)
	{
		if ($date == "")
		{
			$reg = array(0, 0, 0, 0, 0, 0);
		}
		else 
		{
			$reg = preg_split("/[^0-9]/", $date);
			while (count($reg) < 6)
			{
				$reg[count($reg)] = 0;
			}
		}
		
		//Debug::PrintValue($reg);
		
		$timestamp = -1;
		switch ($dateFormat)
		{
			case DATEFORMAT::DMY:
			{
       			$timestamp = mktime($reg[3], $reg[4], $reg[5], $reg[1], $reg[0], $reg[2]); 
				break;
			}
			case DATEFORMAT::MDY:
			{
       			$timestamp = mktime($reg[3], $reg[4], $reg[5], $reg[0], $reg[1], $reg[2]); 
				break;
			}
			default:
			{
				$timestamp = mktime($reg[3], $reg[4], $reg[5], $reg[1], $reg[2], $reg[0]);
				break;
			}
		}
		
		if ($timestamp == -1) 
		{
			throw new DateUtilException("Error parsing timestamp formtat", 700);
		}
		else 
		{
			return $timestamp;
		}
	}
	
	public static function GetDateParts($date, $dateFormat = DATEFORMAT::YMD, $separator="/")
	{
		if (!DateUtil::Validate($date, $dateFormat, $separator, true))
		{
			if (($dateFormat == DATEFORMAT::DMY) || ($dateFormat == DATEFORMAT::MDY))
				$reg = array("00", "00", "0000", "00", "00", "00");
			else
				$reg = array("0000", "00", "00", "00", "00", "00");
		}
		else
		{
			$reg = preg_split("/[^0-9]/", $date);
			while (sizeof($reg) < 6)
			{
				$reg[sizeof($reg)] = '00';
			}
		}
		
		$datePart = array();		
		if (($dateFormat == DATEFORMAT::DMY) || ($dateFormat == DATEFORMAT::MDY))
			$datePart[DateParts::DATE] = sprintf("%02d$separator%02d$separator%04d", $reg[0], $reg[1], $reg[2]);
		else
			$datePart[DateParts::DATE] = sprintf("%04d$separator%02d$separator%02d", $reg[0], $reg[1], $reg[2]);

		switch ($dateFormat) {
			case DATEFORMAT::DMY:
				$datePart[DateParts::DAY] = $reg[0];
				$datePart[DateParts::MONTH] = $reg[1];
				$datePart[DateParts::YEAR] = $reg[2];
				break;
		
			case DATEFORMAT::MDY:
				$datePart[DateParts::DAY] = $reg[1];
				$datePart[DateParts::MONTH] = $reg[0];
				$datePart[DateParts::YEAR] = $reg[2];
				break;
		
			default:
				$datePart[DateParts::DAY] = $reg[2];
				$datePart[DateParts::MONTH] = $reg[1];
				$datePart[DateParts::YEAR] = $reg[0];
				break;
		}

		$datePart[DateParts::TIME] = sprintf("%02d:%02d:%02d", $reg[3], $reg[4], $reg[5]);
		$datePart[DateParts::HOUR] = sprintf("%02d", $reg[3]);
		$datePart[DateParts::MINUTE] = sprintf("%02d", $reg[4]);
		$datePart[DateParts::SECOND] = sprintf("%02d", $reg[5]);
		
		$datePart[DateParts::FULL] = $datePart[DateParts::DATE] . " " . $datePart[DateParts::TIME];
		
		return $datePart;
	}
}
?>