/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  CSharp Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
 *  Acknowledgments to: Roan Brasil Monteiro
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
	public class XmlContainerCollection : XmlnukeCollection, IXmlnukeDocumentObject
	{
		private string _class;

		private string _align;

		private string _style;

		private string _id;

		protected int _timeOut;

		public XmlContainerCollection()
		{
			Random rand = new Random();
			this.setId("div" + rand.Next(1000, 9999).ToString());
		}

		public XmlContainerCollection(string id)
		{
			this.setId(id);
		}


		public void setClass(string text)
		{
			this._class = text;
		}

		public string getClass()
		{
			return this._class;
		}

		public void setAlign(string align)
		{
			this._align = align;
		}

		public string getAlign()
		{
			return this._align;
		}

		public void setStyle(string style)
		{
			this._style = style;
		}

		public string getStyle()
		{
			return this._style;
		}

		public void setId(string id)
		{
			this._id = id;
		}

		public string getId()
		{
			return this._id;
		}

		public void setHideAfterTime(int milisecs)
		{
			this._timeOut = milisecs;
		}
		public int getHideAfterTime()
		{
			return this._timeOut;
		}

		public void generateObject(XmlNode current)
		{
			XmlNode node = util.XmlUtil.CreateChild(current, "container", "");
			if (this._class != "")
			{
				util.XmlUtil.AddAttribute(node, "class", this.getClass());
			}
			if (this._align != "")
			{
				util.XmlUtil.AddAttribute(node, "align", this.getAlign());
			}
			if (this._style != "")
			{
				util.XmlUtil.AddAttribute(node, "style", this.getStyle());
			}
			if (this._id != "")
			{
				util.XmlUtil.AddAttribute(node, "id", this.getId());
			}
			if (this._timeOut > 0)
			{
				util.XmlUtil.AddAttribute(node, "timeout", this.getHideAfterTime().ToString());
			}
			this.generatePage(node);
		}
	}
}