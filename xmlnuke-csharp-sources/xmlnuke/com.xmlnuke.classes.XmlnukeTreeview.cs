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


using com.xmlnuke.engine;
using System.Collections.Specialized;
using System.Collections.Generic;
using com.xmlnuke.processor;
using com.xmlnuke.util;

namespace com.xmlnuke.classes
{

	public enum TreeViewActionType
	{
		OpenUrl,
		OpenUrlInsideContainer,
		OpenUrlInsideFrame,
		OpenInNewWindow,
		ExecuteJS,
		None
	}

	public class XmlnukeTreeview : XmlnukeDocumentObject
	{

		protected Context _context;

		protected string _title;

		protected List<XmlnukeTreeViewLeaf> _collection = new List<XmlnukeTreeViewLeaf>();

		/// <summary>
		/// 
		/// </summary>
		/// <param name="context"></param>
		/// <param name="title"></param>
		public XmlnukeTreeview(Context context, string title)
		{
			this._context = context;
			this._title = title;
		}

		/// <summary>
		/// 
		/// </summary>
		/// <param name="current"></param>
		public override void generateObject(XmlNode current)
		{
			XmlNode treeview = XmlUtil.CreateChild(current, "treeview");
			XmlUtil.AddAttribute(treeview, "title", this._title);

			foreach (XmlnukeTreeViewLeaf treeviewItem in this._collection)
			{
				treeviewItem.generateObject(treeview);
			}
		}

		/// <summary>
		/// 
		/// </summary>
		/// <param name="o"></param>
		public void addChild(XmlnukeTreeViewLeaf o)
		{
			this._collection.Add(o);
		}
	}



	public class XmlnukeTreeViewLeaf : XmlnukeDocumentObject
	{
		protected Context _context;
		protected string _title;
		protected string _img;
		protected string _id;
		protected bool _expanded = false;
		protected bool _selected = false;

		protected TreeViewActionType _action = TreeViewActionType.None;
		protected string _actionText = "";
		protected string _location = "";

		protected string _NODE = "leaf";
		protected List<XmlnukeTreeViewLeaf> _collection = new List<XmlnukeTreeViewLeaf>();


		public XmlnukeTreeViewLeaf(Context context, string title, string img)
			: this(context, title, img, null)
		{ }

		/// <summary>
		/// 
		/// </summary>
		/// <param name="context"></param>
		/// <param name="title"></param>
		/// <param name="img"></param>
		/// <param name="id"></param>
		public XmlnukeTreeViewLeaf(Context context, string title, string img, string id)
		{
			this._context = context;
			this._title = title;
			this._img = img;
			if (String.IsNullOrEmpty(id))
			{
				this._id = id;
			}
			else
			{
				this._id = this._NODE.ToLower() + (context.getRandomNumber(9000) + 1000).ToString();
			}
		}

		public void setSelected(bool value)
		{
			this._selected = value;
		}
		public bool getSelected()
		{
			return this._selected;
		}

		public void setAction(TreeViewActionType actionType, string actionText)
		{
			this.setAction(actionType, actionText, "");
		}

		/// <summary>
		/// 
		/// </summary>
		/// <param name="actionType"></param>
		/// <param name="actionText"></param>
		/// <param name="actionContainer"></param>
		public void setAction(TreeViewActionType actionType, string actionText, string actionContainer)
		{
			this._action = actionType;
			this._actionText = actionText;
			this._location = actionContainer;
		}

		/// <summary>
		/// 
		/// </summary>
		/// <param name="current"></param>
		public override void generateObject(XmlNode current)
		{
			XmlNode leaf = XmlUtil.CreateChild(current, this._NODE);
			XmlUtil.AddAttribute(leaf, "title", this._title);
			XmlUtil.AddAttribute(leaf, "img", this._img);
			XmlUtil.AddAttribute(leaf, "code", this._id);
			if (this._expanded)
			{
				XmlUtil.AddAttribute(leaf, "expanded", "true");
			}
			
			if (this._selected)
			{
				XmlUtil.AddAttribute(leaf, "selected", "true");
			}
		
			if (this._action != TreeViewActionType.None)
			{
				string jsAction = "";
				ParamProcessor processor = new ParamProcessor(this._context);
				string url = processor.GetFullLink(this._actionText);
				switch (this._action)
				{
					case TreeViewActionType.OpenUrl:
						jsAction = "window.location = '" + url + "';";
						break;

					case TreeViewActionType.OpenUrlInsideContainer:
						jsAction = "loadUrl('" + this._location + "', '" + url + "');";
						break;

					case TreeViewActionType.OpenUrlInsideFrame:
						jsAction = this._location + ".location = '" + url + "';";
						break;

					case TreeViewActionType.OpenInNewWindow:
						jsAction = "window.open('" + url + "', '" + this._id + "', 'status=1,location=1;')";
						break;

					default:
						jsAction = this._actionText;
						break;
				}

				XmlUtil.AddAttribute(leaf, "action", jsAction);
			}

			foreach (XmlnukeTreeViewLeaf leafTv in this._collection)
			{
				leafTv.generateObject(leaf);
			}
		}
	}


	public class XmlnukeTreeViewFolder : XmlnukeTreeViewLeaf
	{
		/// <summary>
		/// 
		/// </summary>
		/// <param name="context"></param>
		/// <param name="title"></param>
		/// <param name="img"></param>
		public XmlnukeTreeViewFolder(Context context, string title, string img)
			: base(context, title, img, null)
		{
			this._NODE = "folder";
		}

		public void setExpanded(bool value)
		{
			this._expanded = value;
		}
		public bool getExpanded()
		{
			return this._expanded;
		}
	
		/// <summary>
		/// 
		/// </summary>
		/// <param name="o"></param>
		public void addChild(XmlnukeTreeViewLeaf o)
		{
			this._collection.Add(o);
		}
	}
}