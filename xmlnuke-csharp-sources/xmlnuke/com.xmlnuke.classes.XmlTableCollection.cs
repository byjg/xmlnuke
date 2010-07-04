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
using System.Xml;
using com.xmlnuke.util;

namespace com.xmlnuke.classes
{
	abstract public class XmlTableCollectionBase : XmlnukeCollection, IXmlnukeDocumentObject
	{
		protected string _NODE = "";
		protected XmlNode _genNode = null;

		protected string _style = "";
		public void setStyle(string value)
		{
			this._style = value;
		}
		public string getStyle()
		{
			return this._style;
		}

		protected string _id = "";
		public void setId(string value)
		{
			this._id = value;
		}
		public string getId()
		{
			return this._id;
		}

		public virtual void generateObject(XmlNode current)
		{
			this._genNode = XmlUtil.CreateChild(current, this._NODE, "");
			XmlUtil.AddAttribute(this._genNode, "id", this.getId());
			XmlUtil.AddAttribute(this._genNode, "style", this.getStyle());
			base.generatePage(this._genNode);
		}

	}

	public class XmlTableCollection : XmlTableCollectionBase
	{
		public XmlTableCollection()
		{
			this._NODE = "table";
		}

        public override void addXmlnukeObject(IXmlnukeDocumentObject docobj)
        {
            if (docobj is XmlTableRowCollection)
            {
                base.addXmlnukeObject(docobj);
            }
            else
            {
                throw new Exception("XmlTableCollecion expects a XmlTableRowCollection");
            }
        }
	}

	public class XmlTableRowCollection : XmlTableCollectionBase
	{
		public XmlTableRowCollection()
		{
			this._NODE = "tr";
		}

        public override void addXmlnukeObject(IXmlnukeDocumentObject docobj)
        {
            if (docobj is XmlTableColumnCollection)
            {
                base.addXmlnukeObject(docobj);
            }
            else
            {
                throw new Exception("XmlTableRowCollecion expects a XmlTableColumnCollection");
            }
        }
	}


	class XmlTableColumnCollection : XmlTableCollectionBase
	{
		protected string _colspan = "";
		public void setColspan(string value)
		{
			this._colspan = value;
		}
		public string getColspan()
		{
			return this._colspan;
		}

		protected string _rowspan = "";
		public void setRowspan(string value)
		{
			this._rowspan = value;
		}
		public string getRowspan()
		{
			return this._rowspan;
		}

		public XmlTableColumnCollection()
		{
			this._NODE = "td";
		}

		/**
		*@desc Generate page, processing yours childs.
		*@param DOMNode current
		*@return void
		*/
		public override void generateObject(XmlNode current)
		{
			base.generateObject(current);
			XmlUtil.AddAttribute(this._genNode, "colspan", this.getColspan());
			XmlUtil.AddAttribute(this._genNode, "rowspan", this.getRowspan());
		}

	}

}