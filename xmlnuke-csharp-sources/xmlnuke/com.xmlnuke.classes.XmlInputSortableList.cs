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
		Disabled = 2,
        Portlet = 3
	}

	public struct SortableListKey
	{
		public string Key;
        public string Title;
        public SortableListItemState State;
		public SortableListKey(string key, SortableListItemState state)
		{
			this.Key = key;
            this.Title = null;
            this.State = state;
		}
        public SortableListKey(string key, string title, SortableListItemState state)
        {
            this.Key = key;
            this.Title = title;
            this.State = state;
        }
    }
	
	public class XmlInputSortableList : XmlnukeDocumentObject
	{
		protected Dictionary<string, Dictionary<SortableListKey, IXmlnukeDocumentObject>> _items;
		protected string _name;
		protected string _caption;
        protected string _connectKey;
	    protected int _columns = 1;
	    protected bool _fullSize = false;

        public XmlInputSortableList(string caption, string name) : this(caption, name, 1)
        { }

        public XmlInputSortableList(string caption, string name, int columns) : this(caption, name, new string[columns])
        { }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="caption"></param>
        /// <param name="name"></param>
        /// <param name="columns"></param>
        public XmlInputSortableList(string caption, string name, string[] columns)
		{
            this._items = new Dictionary<string, Dictionary<SortableListKey, IXmlnukeDocumentObject>>();
			this._name = name;
			this._caption = caption;
            
            this._columns = columns.Length;
			for(int i=0; i<columns.Length; i++)
			{
                this._items[String.IsNullOrEmpty(columns[i]) ? i.ToString() : columns[i]] = new Dictionary<SortableListKey, IXmlnukeDocumentObject>();
			}
        }

		public void addSortableItem(string key, IXmlnukeDocumentObject docobj)
		{
			this.addSortableItem(key, docobj, SortableListItemState.Normal);
		}

        public void addSortableItem(string key, IXmlnukeDocumentObject docobj, int column)
        {
            this.addSortableItem(key, docobj, SortableListItemState.Normal, column.ToString());
        }

        public void addSortableItem(string key, IXmlnukeDocumentObject docobj, string column)
        {
            this.addSortableItem(key, docobj, SortableListItemState.Normal, column);
        }

        public void addSortableItem(string key, IXmlnukeDocumentObject docobj, SortableListItemState state)
        {
            this.addSortableItem(key, docobj, state, "0");
        }

        public void addSortableItem(string key, IXmlnukeDocumentObject docobj, SortableListItemState state, string column)
		{
            this.addItem(key, null, docobj, state, column);
		}


        public void addPortlet(string key, string title, IXmlnukeDocumentObject docobj)
        {
            this.addItem(key, title, docobj, SortableListItemState.Portlet, "0");
        }

        public void addPortlet(string key, string title, IXmlnukeDocumentObject docobj, int column)
        {
            this.addItem(key, title, docobj, SortableListItemState.Portlet, column.ToString());
        }

        public void addPortlet(string key, string title, IXmlnukeDocumentObject docobj, string column)
        {
            this.addItem(key, title, docobj, SortableListItemState.Portlet, column);
        }


        protected void addItem(string key, string title, IXmlnukeDocumentObject docobj, SortableListItemState state, string column)
        {
            if (!this._items.ContainsKey(column))
            {
                throw new Exception("Column does not exists");
            }
            if (String.IsNullOrEmpty(title))
            {
                this._items[column][new SortableListKey(key, state)] = docobj;
            }
            else
            {
                this._items[column][new SortableListKey(key, title, state)] = docobj;
            }
        }
        
        public string getConnectKey()
	    {
		    if (String.IsNullOrEmpty(this._connectKey))
		    {
                Random r = new Random();
			    this._connectKey = "connect" + r.Next(0, 0).ToString() + r.Next(1000, 9999).ToString();
		    }
		    return this._connectKey;
	    }
	    public void setConnectKey(string value)
	    {
		    this._connectKey = value;
	    }

	    public bool getFullSize()
	    {
		    return this._fullSize;
	    }
	    public void setFullSize(bool value)
	    {
		    this._fullSize = value;
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
            util.XmlUtil.AddAttribute(node, "connectkey", this.getConnectKey());
		    util.XmlUtil.AddAttribute(node, "columns", this._columns);
    		util.XmlUtil.AddAttribute(node, "fullsize", (this._fullSize ? "true" : "false"));

            foreach (KeyValuePair<string, Dictionary<SortableListKey, IXmlnukeDocumentObject>> cols in this._items)
            {
                XmlNode columnNode = util.XmlUtil.CreateChild(node, "column", "");
			    util.XmlUtil.AddAttribute(columnNode, "id", cols.Key);

                foreach (KeyValuePair<SortableListKey, IXmlnukeDocumentObject> kvp in cols.Value)
                {
                    XmlNode nodeitem = util.XmlUtil.CreateChild(columnNode, "item", "");
                    util.XmlUtil.AddAttribute(nodeitem, "key", kvp.Key.Key);
                    util.XmlUtil.AddAttribute(nodeitem, "state", (kvp.Key.State == SortableListItemState.Highligth ? "highlight" : (kvp.Key.State == SortableListItemState.Disabled ? "disabled" : (kvp.Key.State == SortableListItemState.Portlet ? "portlet" :""))));
                    if (!String.IsNullOrEmpty(kvp.Key.Title))
                        util.XmlUtil.AddAttribute(nodeitem, "title", kvp.Key.Title);

                    kvp.Value.generateObject(nodeitem);
                }
            }
		}
	}

}