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
using com.xmlnuke.engine;
using com.xmlnuke.anydataset;

using System.Collections.Specialized;

namespace com.xmlnuke.classes
{

	/// <summary>
	/// Dual list Button Types
	/// </summary>
	public enum DualListButtonType
	{
		Button,
		Image,
		None
	}

	/// <summary>
	/// Dual list Buttons
	/// </summary>
	public struct DualListButton
	{
		/**
		 * Button inside text
		 */
		public string text;
		/**
		 * Button type
		 */
		public DualListButtonType type;
		/**
		 * If image button type, needle a url from image
		 */
		public string href;

		public DualListButton(string text, DualListButtonType type, string imgurl)
		{
			this.text = text;
			this.type = type;
			this.href = imgurl;
		}
	}

	/**
	*Edit list class
	*@package com.xmlnuke
	*@subpackage xmlnukeobject
	*/
	public class XmlDualList : XmlnukeDocumentObject
	{
		protected Context _context;
		protected string _name;
		protected DualListButton _buttonOneLeft;
		protected DualListButton _buttonAllLeft;
		protected DualListButton _buttonOneRight;
		protected DualListButton _buttonAllRight;
		protected string _listLeftName;
		protected string _listRightName;
		protected IIterator _listLeftDataSource;
		protected IIterator _listRightDataSource;
		protected string _listLeftCaption;
		protected string _listRightCaption;
		protected int _listLeftSize = 5;
		protected int _listRightSize = 5;
		protected string _dataTableFieldId;
		protected string _dataTableFieldText;

		public XmlDualList(Context context, string name)
			: this(context, name, "", "")
		{ }

		/**
		* XmlEditList constructor
		* 
		* @param Context context
		* @param string name
		* @param string captionLeft
		* @param string captionRight
		*/
		public XmlDualList(Context context, string name, string captionLeft, string captionRight)
		{
			this._name = name;
			this._context = context;
			this._listLeftName = "DL_LEFT_" + this._context.getRandomNumber(100000);
			this._listRightName = "DL_RIGHT_" + this._context.getRandomNumber(100000);
			this._listLeftCaption = captionLeft;
			this._listRightCaption = captionRight;
			this._buttonOneLeft.type = DualListButtonType.None;
			this._buttonAllLeft.type = DualListButtonType.None;
			this._buttonOneRight.type = DualListButtonType.None;
			this._buttonAllRight.type = DualListButtonType.None;
		}

		public void setDataSource(IIterator listLeft)
		{
			this.setDataSource(listLeft, null);
		}

		/**
		 * Config DataSource to Dual List
		 *
		 * @param IIterator listLeft
		 * @param IIterator listRight
		 */
		public void setDataSource(IIterator listLeft, IIterator listRight)
		{
			this._listLeftDataSource = listLeft;
			this._listRightDataSource = listRight;
		}

		/**
		 * Config Database table fields of datasource to Dual List
		 *
		 * @param DualListDataField fields
		 */
		public void setDataSourceFieldName(string id, string text)
		{
			this._dataTableFieldId = id;
			this._dataTableFieldText = text;
		}

		/**
		 * Create all default buttons.
		 *
		 */
		public void createDefaultButtons()
		{
			this.setButtonOneLeft(new DualListButton("<--", DualListButtonType.Button, null));
			this.setButtonAllLeft(new DualListButton("<<<", DualListButtonType.Button, null));
			this.setButtonOneRight(new DualListButton("-->", DualListButtonType.Button, null));
			this.setButtonAllRight(new DualListButton(">>>", DualListButtonType.Button, null));
		}

		/**
		 * Config move one element from a list to left Button
		 *
		 * @param DualListButton button
		 */
		public void setButtonOneLeft(DualListButton button)
		{
			this._buttonOneLeft = button;
		}

		/**
		 * Config move all elements from a list to left Button
		 *
		 * @param DualListButton button
		 */
		public void setButtonAllLeft(DualListButton button)
		{
			this._buttonAllLeft = button;
		}

		/**
		 * Config move one element from a list to right Button
		 *
		 * @param DualListButton button
		 */
		public void setButtonOneRight(DualListButton button)
		{
			this._buttonOneRight = button;
		}

		/**
		 * Config move all elements from a list to right Button
		 *
		 * @param DualListButton button
		 */
		public void setButtonAllRight(DualListButton button)
		{
			this._buttonAllRight = button;
		}

		/**
		 * Set Dual Lists names
		 *
		 * @param string leftName
		 * @param string rightName
		 */
		public void setDualListName(string leftName, string rightName)
		{
			this._listLeftName = leftName;
			this._listRightName = rightName;
		}

		/**
		 * Set Dual Lists names
		 *
		 * @param int leftSize
		 * @param int rightSize
		 */
		public void setDualListSize(int leftSize, int rightSize)
		{
			this._listLeftSize = leftSize;
			this._listRightSize = rightSize;
		}

		/**
		*@desc Generate page, processing yours childs.
		*@param DOMNode current
		*@return void
		*/
		public override void generateObject(XmlNode current)
		{
			string submitFunction = "buildDualListField(this, '" + this._listRightName + "', '" + this._name + "');";
		
			XmlNode editForm = current;
			while ((editForm != null) && (editForm.Name != "editform")) 
			{
				editForm = editForm.ParentNode;
			} 
		
			if (editForm != null)
			{
				XmlNode customSubmit = editForm.Attributes["customsubmit"];
				if (customSubmit != null)
				{
					customSubmit.Value = customSubmit.Value + " && " + submitFunction;
				}
				else
				{
					util.XmlUtil.AddAttribute(editForm, "customsubmit", submitFunction);
				}
			}
			else
			{
				throw new Exception("XMLDualList must be inside a XmlFormCollection");
			}

			XmlNode nodeWorking = util.XmlUtil.CreateChild(current, "duallist", "");

			if (this._buttonAllRight.type != DualListButtonType.None)
			{
				this.makeButton(this._buttonAllRight, "allright", nodeWorking, this._listLeftName, this._listRightName, "true");
			}
			if (this._buttonOneRight.type != DualListButtonType.None)
			{
				this.makeButton(this._buttonOneRight, "oneright", nodeWorking, this._listLeftName, this._listRightName, "false");
			}
			if (this._buttonOneLeft.type != DualListButtonType.None)
			{
				this.makeButton(this._buttonOneLeft, "oneleft", nodeWorking, this._listRightName, this._listLeftName, "false");
			}
			if (this._buttonAllLeft.type != DualListButtonType.None)
			{
				this.makeButton(this._buttonAllLeft, "allleft", nodeWorking, this._listRightName, this._listLeftName, "true");
			}

			util.XmlUtil.AddAttribute(nodeWorking, "name", this._name);
			XmlNode leftList = util.XmlUtil.CreateChild(nodeWorking, "leftlist", "");
			XmlNode rightList = util.XmlUtil.CreateChild(nodeWorking, "rightlist", "");
			util.XmlUtil.AddAttribute(leftList, "name", this._listLeftName);
			util.XmlUtil.AddAttribute(leftList, "caption", this._listLeftCaption);
			util.XmlUtil.AddAttribute(leftList, "size", this._listLeftSize);
			util.XmlUtil.AddAttribute(rightList, "name", this._listRightName);
			util.XmlUtil.AddAttribute(rightList, "caption", this._listRightCaption);
			util.XmlUtil.AddAttribute(rightList, "size", this._listRightSize);

			NameValueCollection arrRight = new NameValueCollection();
			if (this._listRightDataSource != null)
			{
				while (this._listRightDataSource.hasNext())
				{
					SingleRow row = this._listRightDataSource.moveNext();
					arrRight[row.getField(this._dataTableFieldId)] = row.getField(this._dataTableFieldText);
				}
			}

			NameValueCollection arrLeft = new NameValueCollection();
			while (this._listLeftDataSource.hasNext())
			{
				SingleRow row = this._listLeftDataSource.moveNext();
				if (string.IsNullOrEmpty(arrRight[row.getField(this._dataTableFieldId)]))
				{
					arrLeft[row.getField(this._dataTableFieldId)] = row.getField(this._dataTableFieldText);
				}
			}

			this.buildListItens(leftList, arrLeft);
			this.buildListItens(rightList, arrRight);
		}

		/**
		 * Parse RESULTSS from DualList object
		 *
		 * @param Context context
		 * @param string duallistaname
		 * @return string[]
		 */
		public static string[] Parse(Context context, string duallistaname)
		{
			string val = context.ContextValue(duallistaname);
			if (val != "")
			{
				return val.Split(',');

			}
			else
			{
				return new String[] { };
			}
		}

		/**
		 * Build Dual lista data
		 *
		 * @param DOMNode list
		 * @param array arr
		 */
		private void buildListItens(XmlNode list, NameValueCollection arr)
		{
			foreach (string key in arr)
			{
				XmlNode item = util.XmlUtil.CreateChild(list, "item", "");
				util.XmlUtil.AddAttribute(item, "id", key);
				util.XmlUtil.AddAttribute(item, "text", arr[key]);
			}
		}

		/**
		 * Make a buttom
		 *
		 * @param DualListButton button
		 * @param string name
		 * @param DOMNode duallist
		 * @param string from
		 * @param string to
		 * @param string all
		 */
		private void makeButton(DualListButton button, string name, XmlNode duallist, string from, string to, string all)
		{
			XmlNode newbutton = util.XmlUtil.CreateChild(duallist, "button", "");
			util.XmlUtil.AddAttribute(newbutton, "name", name);
			if (button.type == DualListButtonType.Image)
			{
				util.XmlUtil.AddAttribute(newbutton, "type", "image");
				util.XmlUtil.AddAttribute(newbutton, "src", button.href);
				util.XmlUtil.AddAttribute(newbutton, "value", button.text);
			}
			else
			{
				util.XmlUtil.AddAttribute(newbutton, "type", "button");
				util.XmlUtil.AddAttribute(newbutton, "value", button.text);
			}
			util.XmlUtil.AddAttribute(newbutton, "from", from);
			util.XmlUtil.AddAttribute(newbutton, "to", to);
			util.XmlUtil.AddAttribute(newbutton, "all", all);
		}
	}

}