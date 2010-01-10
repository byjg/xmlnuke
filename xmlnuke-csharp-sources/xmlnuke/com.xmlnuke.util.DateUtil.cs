/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  CSharp Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
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

using System;
using com.xmlnuke.classes;

namespace com.xmlnuke.util
{
	/// <summary>
	/// Description of comxmlnukeutilDateUtil.
	/// </summary>
	public class DateUtil
	{
		public static DateTime ConvertDate(string source, DATEFORMAT sourceFormat)
		{
			DateTime result;
			char dateSep = '/';
			string[] date = source.Split(' '); // Extract Hour
			if (date[0].IndexOf('-') >= 0)
				dateSep = '-';
			else if (date[0].IndexOf('.') >= 0)
				dateSep = '.';
			string[] parts = date[0].Split(dateSep); // Extract Day. Month and Year
			Int16 hour = 0;
			Int16 min = 0;
			Int16 secs = 0;
			if (date.Length > 1)
			{
				string[] hourParts = date[1].Split(':');
				try
				{
					hour = Convert.ToInt16(hourParts[0]);
				}
				catch
				{
					hour = 0;
				}
				try
				{
					min = Convert.ToInt16(hourParts[1]);
				}
				catch
				{
					min = 0;
				}
				try
				{
					secs = Convert.ToInt16(hourParts[2]);
				}
				catch
				{
					secs = 0;
				}
			}

			if (sourceFormat == DATEFORMAT.DMY)
			{
				result = new DateTime(Convert.ToInt16(parts[2]), Convert.ToInt16(parts[1]), Convert.ToInt16(parts[0]), hour, min, secs);
			}
			else if (sourceFormat == DATEFORMAT.MDY)
			{
				result = new DateTime(Convert.ToInt16(parts[2]), Convert.ToInt16(parts[0]), Convert.ToInt16(parts[1]), hour, min, secs);
			}
			else
			{
				result = new DateTime(Convert.ToInt16(parts[0]), Convert.ToInt16(parts[1]), Convert.ToInt16(parts[2]), hour, min, secs);
			}
			return result;
		}

		public static string ConvertDate(string source, DATEFORMAT sourceFormat, DATEFORMAT targetFormat)
		{
            return DateUtil.ConvertDate(source, sourceFormat, targetFormat, false);
		}

        public static string ConvertDate(string source, DATEFORMAT sourceFormat, DATEFORMAT targetFormat, bool hour)
        {
            return ConvertToString(ConvertDate(source, sourceFormat), targetFormat, hour);
        }

        public static string ConvertToString(DateTime source, DATEFORMAT targetFormat)
        {
            return DateUtil.ConvertToString(source, targetFormat, false);
        }

		public static string ConvertToString(DateTime source, DATEFORMAT targetFormat, bool hour)
		{
			return source.ToString(DateUtil.getDateTimeMask(targetFormat, hour));
		}

		public static string Today(DATEFORMAT targetFormat)
		{
			return DateUtil.ConvertToString(System.DateTime.Now, targetFormat);
		}

        public static string getDateTimeMask(DATEFORMAT format)
        {
            return DateUtil.getDateTimeMask(format, false);
        }

		public static string getDateTimeMask(DATEFORMAT format, bool hour)
		{
			string result;
			if (format == DATEFORMAT.DMY)
			{
				result = "dd/MM/yyyy";
			}
			else if (format == DATEFORMAT.MDY)
			{
				result = "MM/dd/yyyy";
			}
			else
			{
				result = "yyyy/MM/dd";
			}

            if (hour)
            {
                result += " HH:mm:ss";
            }
			return result;
		}
	}
}