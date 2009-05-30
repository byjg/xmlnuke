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
using System.Xml;

using com.xmlnuke.util;

namespace com.xmlnuke.classes
{

	public enum XmlListType
	{
		UnorderedList,
		OrderedList
	}

	/**
	 * Xml List Collection
	 *
	 * @package com.xmlnuke
	 * @subpackage xmlnukeobject
	 */
	public class XmlListCollection : XmlnukeCollection, IXmlnukeDocumentObject
	{
		protected XmlListType _type;

		protected string _caption;

		protected string _name;

		public XmlListCollection(XmlListType type, string caption)
			: this(XmlListType.UnorderedList, caption, "")
		{ }

		public XmlListCollection(XmlListType type)
			: this(XmlListType.UnorderedList, "", "")
		{ }

		public XmlListCollection(string caption, string name)
			: this(XmlListType.UnorderedList, caption, name)
		{ }

		public XmlListCollection(XmlListType type, string caption, string name)
		{
			this._type = type;
			this._caption = caption;
			this._name = name;
		}

		/// <summary>
		/// Create a NEW generatePage for processing all childrens like ITEM objects.
		/// </summary>
		/// <param name="current"></param>
		new protected void generatePage(XmlNode current)
		{
			if (this._items != null)
			{
				foreach (IXmlnukeDocumentObject item in this._items)
				{
					if ((this._type == XmlListType.UnorderedList) || (this._type == XmlListType.OrderedList))
					{
						XmlNode node = XmlUtil.CreateChild(current, "li", "");
						item.generateObject(node);
					}
				}
			}
		}

		/**
		 * Generate page, processing yours childs.
		 *
		 * @param DOMNode current
		 */
		public void generateObject(XmlNode current)
		{
			XmlNode node = current;
			XmlnukeText text = new XmlnukeText(this._caption, true);
			text.generateObject(current);
			if (this._type == XmlListType.UnorderedList)
			{
				node = XmlUtil.CreateChild(current, "ul", "");
			}
			else if (this._type == XmlListType.OrderedList)
			{
				node = XmlUtil.CreateChild(current, "ol", "");
			}
			this.generatePage(node);
		}
	}

}