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
using System;
using System.Collections;
using System.Xml;

using com.xmlnuke.engine;
using com.xmlnuke.util;

namespace com.xmlnuke.classes
{
	public class XmlInputDateTime : XmlnukeDocumentObject
	{
		protected string _name;
		protected string _caption;
		protected string _date;
		protected string _time;
		protected Context _context;
		protected DATEFORMAT _dateformat;
		protected bool _showHour;
		protected int _yearmin;
		protected int _yearmax;

		public XmlInputDateTime(string caption, string name, DATEFORMAT dateformat)
			: this(caption, name, dateformat, true, "", "")
		{ }
		public XmlInputDateTime(string caption, string name, DATEFORMAT dateformat, bool showhour)
			: this(caption, name, dateformat, showhour, "", "")
		{ }
		public XmlInputDateTime(string caption, string name, DATEFORMAT dateformat, bool showhour, string date)
			: this(caption, name, dateformat, showhour, date, "")
		{ }
		public XmlInputDateTime(string caption, string name, DATEFORMAT dateformat, bool showhour, string date, string time)
		{
			this._caption = caption;
			this._name = name;
			this._dateformat = dateformat;
			if (date != "")
			{
				this._date = date;
			}
			else
			{
				this._date = DateUtil.Today(dateformat);
			}
			if (time != "")
			{
				this._time = time;
			}
			else
			{
				this._time = System.DateTime.Now.ToString("H:m");
			}
			this._showHour = showhour;
			this._yearmin = -10;
			this._yearmax = +10;
		}

		public static string ParseSubmit(Context context, string name)
		{
			return XmlInputDateTime.ParseSubmit(context, name, true);
		}
		public static string ParseSubmit(Context context, string name, bool getTime)
		{
			string date = context.ContextValue(name);
			if (getTime)
			{
				date += " " + context.ContextValue(name + "_hour") + ":" + context.ContextValue(name + "_minute");
			}
			return date;
		}

		public void setYearBounds(int yearmin, int yearmax)
		{
			this._yearmin = yearmin;
			this._yearmax = yearmax;
		}

		/**
		*@desc Generate page, processing yours childs.
		*@param DOMNode current
		*@return void
		*/
		public override void generateObject(XmlNode current)
		{
			XmlNode datetimebox = util.XmlUtil.CreateChild(current, "datetimebox");
			DateTime date = DateUtil.ConvertDate(this._date, this._dateformat);
			util.XmlUtil.AddAttribute(datetimebox, "name", this._name);
			util.XmlUtil.AddAttribute(datetimebox, "caption", this._caption);
			util.XmlUtil.AddAttribute(datetimebox, "day", date.Day); // Day without leading zeros
			util.XmlUtil.AddAttribute(datetimebox, "month", date.Month); // Month without leading zeros
			util.XmlUtil.AddAttribute(datetimebox, "year", date.Year);
			util.XmlUtil.AddAttribute(datetimebox, "dateformat", INPUTTYPE.DATE.ToString());

			if (this._showHour)
			{
				string[] time = this._time.Split(':');
                util.XmlUtil.AddAttribute(datetimebox, "showhour", "true");
				util.XmlUtil.AddAttribute(datetimebox, "hour", this.removeLeadingZero(time[0])); // Hour without leading zeros
				util.XmlUtil.AddAttribute(datetimebox, "minute", this.removeLeadingZero(time[1]));
			}
			util.XmlUtil.AddAttribute(datetimebox, "yearmin", "c" + this._yearmin.ToString());
			util.XmlUtil.AddAttribute(datetimebox, "yearmax", "c+" + this._yearmax.ToString());
		}

		protected string removeLeadingZero(string str)
		{
			if (str.Length > 1)
			{
				if (str[0] == '0')
				{
					return str.Substring(1);
				}
			}
			return str;
		}
	}
}