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
using System.Collections;
using System.Xml;

namespace com.xmlnuke.classes
{

	public class XmlnukeCalendar : XmlnukeDocumentObject
	{
		protected int _month;
		protected int _year;
		protected string _title;

		protected ArrayList _events;

		public XmlnukeCalendar()
			: this(DateTime.Today.Month, DateTime.Today.Year)
		{ }

		public XmlnukeCalendar(int month)
			: this(month, DateTime.Today.Year)
		{ }

		public XmlnukeCalendar(int month, int year)
		{
			DateTime date = new DateTime(year, month, 1);
			this._month = month;
			this._year = year;
			this._title = date.ToString("MMMM/yyyy");

			this._events = new ArrayList();
		}

		public string getTitle()
		{
			return this._title;
		}

		public void setTitle(string title)
		{
			this._title = title;
		}

		public void addCalendarEvent(XmlnukeCalendarEvent calendarEvent)
		{
			this._events.Add(calendarEvent);
		}

		/// <summary>
		/// Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
		/// </summary>
		/// <param name="px">PageXml class</param>
		/// <param name="current">XmlNode where the XML will be created.</param>
		public override void generateObject(XmlNode current)
		{
			XmlNode nodeCalendar = util.XmlUtil.CreateChild(current, "calendar", "");

			Random rand = new Random();
			util.XmlUtil.AddAttribute(nodeCalendar, "name", "cal" + rand.Next(1000, 9999));
			util.XmlUtil.AddAttribute(nodeCalendar, "month", this._month);
			util.XmlUtil.AddAttribute(nodeCalendar, "year", this._year);
			util.XmlUtil.AddAttribute(nodeCalendar, "title", this._title);

			foreach (object calendarEvent in this._events)
			{
				((XmlnukeCalendarEvent)calendarEvent).generateObject(nodeCalendar);
			}
		}

	}




	public class XmlnukeCalendarEvent : XmlnukeCollection, IXmlnukeDocumentObject
	{
		protected int _day;
		protected int _type;
		protected string _text;

		public XmlnukeCalendarEvent(int day)
			: this(day, -1, "")
		{ }

		public XmlnukeCalendarEvent(int day, string text)
			: this(day, -1, text)
		{ }

		public XmlnukeCalendarEvent(int day, int type)
			: this(day, type, "")
		{ }

		public XmlnukeCalendarEvent(int day, int type, string text)
		{
			this._day = day;
			this._type = type;
			this._text = text;
		}

		/// <summary>
		/// Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
		/// </summary>
		/// <param name="px">PageXml class</param>
		/// <param name="current">XmlNode where the XML will be created.</param>
		public void generateObject(XmlNode current)
		{
			XmlNode nodeCalendarEvent = util.XmlUtil.CreateChild(current, "event", this._text);

			util.XmlUtil.AddAttribute(nodeCalendarEvent, "day", this._day.ToString());
			if (this._type > 0)
			{
				util.XmlUtil.AddAttribute(nodeCalendarEvent, "type", this._type.ToString());
			}

			this.generatePage(nodeCalendarEvent);
		}
	}

}