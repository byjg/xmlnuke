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

namespace com.xmlnuke.classes
{

	public class XmlnukeTabView : XmlnukeDocumentObject
	{
		protected ArrayList _tabs = new ArrayList();
		protected ArrayList _tabsData = new ArrayList();
		protected string _tabDefault = "";

		/**
		*@desc Generate page, processing yours childs.
		*@param DOMNode $current
		*@return void
		*/
		public XmlnukeTabView()
		{

		}

		public void addTabItem(string title, IXmlnukeDocumentObject docobj)
		{
			this.addTabItem(title, docobj, false);
		}
		public void addTabItem(string title, IXmlnukeDocumentObject docobj, bool defaultTab)
		{
			this._tabs.Add(title);
			this._tabsData.Add(docobj);
			if (defaultTab)
			{
				this._tabDefault = title;
			}
		}

		public override void generateObject(XmlNode current)
		{
			XmlNode node = util.XmlUtil.CreateChild(current, "tabview", "");
			for (int i = 0; i < this._tabs.Count; i++)
			{
				XmlNode nodetab = util.XmlUtil.CreateChild(node, "tabitem", "");
				util.XmlUtil.AddAttribute(nodetab, "title", this._tabs[i].ToString());
				if (this._tabDefault == this._tabs[i].ToString())
				{
					util.XmlUtil.AddAttribute(nodetab, "default", "true");
				}
				((IXmlnukeDocumentObject)this._tabsData[i]).generateObject(nodetab);
			}
		}
	}

}