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
//using System.Collections;
using System.Collections.Generic;
using System.Xml;

using com.xmlnuke.engine;

namespace com.xmlnuke.classes
{
	public enum SortableListItemState
	{
		Normal = 0,
		Highligth = 1,
		Disabled = 2
	}

	public struct SortableListKey
	{
		public string Key;
		public SortableListItemState State;
		public SortableListKey(string key, SortableListItemState state)
		{
			this.Key = key;
			this.State = state;
		}
	}
	
	public class XmlInputSortableList : XmlnukeDocumentObject
	{
		protected Dictionary<SortableListKey, IXmlnukeDocumentObject> _items;
		protected string _name;
		protected string _caption;

		/**
		*@desc Generate page, processing yours childs.
		*@param DOMNode current
		*@return void
		*/
		public XmlInputSortableList(string caption, string name)
		{
			this._items = new Dictionary<SortableListKey, IXmlnukeDocumentObject>();
			this._name = name;
			this._caption = caption;
		}

		public void addSortableItem(string key, IXmlnukeDocumentObject docobj)
		{
			this.addSortableItem(key, docobj, SortableListItemState.Normal);
		}

		public void addSortableItem(string key, IXmlnukeDocumentObject docobj, SortableListItemState state)
		{
			this._items[new SortableListKey(key, state)] = docobj;
		}

		public override void generateObject(XmlNode current)
		{
			string submitFunction = "processSortableList('" + this._name + "')";

			XmlNode editForm = current;
			while ((editForm != null) && (editForm.Name != "editform")) 
			{
				editForm = editForm.ParentNode;
			}

			if (editForm == null)
			{
				throw new Exception("XmlSortableList must be inside a XmlFormCollection");
			}

			XmlNode node = util.XmlUtil.CreateChild(current, "sortablelist", "");
			util.XmlUtil.AddAttribute(node, "name", this._name);
			util.XmlUtil.AddAttribute(node, "caption", this._caption);
			foreach (KeyValuePair<SortableListKey, IXmlnukeDocumentObject> kvp in this._items)
			{
				XmlNode nodeitem = util.XmlUtil.CreateChild(node, "item", "");
				util.XmlUtil.AddAttribute(nodeitem, "key", kvp.Key.Key);
				util.XmlUtil.AddAttribute(nodeitem, "state", (kvp.Key.State == SortableListItemState.Highligth ? "highlight" : (kvp.Key.State == SortableListItemState.Disabled ? "disabled" : "")));
				kvp.Value.generateObject(nodeitem);
			}
		}
	}

}