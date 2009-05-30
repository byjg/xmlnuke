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

namespace com.xmlnuke.classes
{
	public class XmlnukeAjaxCallback : XmlnukeDocumentObject
	{
		protected Context _context;

		protected string _class = "";

		protected string _style = "";

		protected string _id = "";

		public XmlnukeAjaxCallback(Context context)
		{
			this._context = context;
		}

		public void setClass(string className)
		{
			this._class = className;
		}
		public string getClass()
		{
			return this._class;
		}
		public void setStyle(string style)
		{
			this._style = style;
		}
		public string getStyle()
		{
			if (this._style == "")
			{
				this.setCustomStyle();
			}
			return this._style;
		}
		public void setId(string id)
		{
			this._id = id;
		}
		public string getId()
		{
			if (this._id == "")
			{
				this._id = "ACB_" + this._context.getRandomNumber(10000);
			}
			return this._id;
		}

		public void setCustomStyle()
		{
			this.setCustomStyle(400, true);
		}
		public void setCustomStyle(int width, bool border)
		{
			int halfWidth = width / 2;
			string borderStr = (border ? "border: 1px dashed gray;" : "");
			this._style = borderStr + "display: none; width: " + width + "px; position: relative; left: 50%; margin-left: -" + halfWidth + "px;";
		}

		public override void generateObject(XmlNode current)
		{
			XmlNode node = util.XmlUtil.CreateChild(current, "ajaxcallback", "");
			if (this._class != "")
			{
				util.XmlUtil.AddAttribute(node, "class", this.getClass());
			}
			util.XmlUtil.AddAttribute(node, "style", this.getStyle());
			util.XmlUtil.AddAttribute(node, "id", this.getId());
		}
	}

}