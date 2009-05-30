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
using System.Collections.Specialized;
using System.Xml;

namespace com.xmlnuke.classes
{
	/// <summary>EasyList types</summary>
	public enum EasyListType
	{
		CHECKBOX,
		RADIOBOX,
		SELECTLIST,
		UNORDEREDLIST
	}

	/// <summary>
	/// Class to represent all the most used list of itens in XML. You can create the object pass a name value collection and the list of object will be created. 
	/// List of objects: CheckBox, RadioBox, SelectList and UnorderedList. 
	/// Elements and attributes defined:
	/// CheckBox:
	/// <c>
	/// &lt;caption&gt;caption&lt;/caption&gt;
	/// &lt;checkbox caption="" name="" value="" /&gt; 
	///      .
	///      .
	///      .
	/// </c>
	/// RadioBox:
	/// <c>
	/// &lt;caption&gt;caption&lt;/caption&gt;
	/// &lt;radiobox caption="" name="" value="" /&gt; 
	///      .
	///      .
	///      .
	/// </c>
	/// SelectList:
	/// <c>
	/// &lt;select caption="" name="" &gt;
	///    &lt;option value="" &gt;&lt;/option&gt;
	///         .
	///         .
	///         .
	/// &lt;/select&gt;
	/// </c>
	/// UnorderedList:
	/// <c>
	/// &lt;b&gt;caption&lt;/b&gt;
	/// &lt;ul&gt;
	///    &lt;li&gt;&lt;/li&gt;
	///       .
	///       .
	///       .
	/// &lt;/ul&gt;
	/// </c>
	/// </summary>
	public class XmlEasyList : XmlnukeDocumentObject
	{
		protected string _name;
		protected string _selected;
		protected string _caption;
		protected NameValueCollection _values;
		protected EasyListType _easyListType;
		protected bool _readOnly;
		protected bool _required;
		protected string _readOnlyDeli = "[]";
		protected int _size = 1;

		public XmlEasyList(EasyListType listType, string name, string caption, NameValueCollection values, string selected)
			: base()
		{
			this._name = name;
			this._caption = caption;
			this._values = values;
			this._selected = selected;
			this._easyListType = listType;
			this._readOnly = false;
			this._required = false;

		}

		public XmlEasyList(EasyListType listType, string name, string caption, NameValueCollection values)
			: this(listType, name, caption, values, null)
		{ }

		public XmlEasyList(EasyListType listType, string name, string caption, ICollection values)
			: this(listType, name, caption, values, null)
		{ }
		public XmlEasyList(EasyListType listType, string name, string caption, ICollection values, string selected)
		{
			this._name = name;
			this._caption = caption;
			this._values = new NameValueCollection();
			int i = 0;
			foreach (object o in values)
			{
				this._values.Add((i++).ToString(), o.ToString());
			}
			this._selected = selected;
			this._easyListType = listType;
			this._readOnly = false;
		}

		public void setSize(int value)
		{
			if (this._easyListType != EasyListType.SELECTLIST)
			{
				throw new Exception("Size is valid only in Select list type");
			}
			else
			{
				this._size = value;
			}
		}

		public void setReadOnly(bool value)
		{
			this._readOnly = value;
		}

		public void setRequired(bool value)
		{
			this._required = value;
		}

		public void setReadOnlyDelimiters(string value)
		{
			if (value.Length != 2 && !String.IsNullOrEmpty(value))
			{
				throw new Exception("Read Only Delimiters must have two characters or is empty");
			}
			else
			{
				this._readOnlyDeli = value;
			}
		}

		/// <summary>
		/// Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
		/// </summary>
		/// <param name="px">PageXml class</param>
		/// <param name="current">XmlNode where the XML will be created.</param>
		public override void generateObject(XmlNode current)
		{
			XmlNode nodeWorking = null;
			// Criando o objeto que conterá a lista.
			switch (this._easyListType)
			{
				case EasyListType.CHECKBOX:
					{
						util.XmlUtil.CreateChild(current, "caption", this._caption);
						nodeWorking = current;
						XmlInputHidden iHid = new XmlInputHidden("qty" + this._name, this._values.Keys.Count.ToString());
						iHid.generateObject(nodeWorking);
						break;
					}
				case EasyListType.RADIOBOX:
					{
						util.XmlUtil.CreateChild(current, "caption", this._caption);
						nodeWorking = current;
						break;
					}
				case EasyListType.SELECTLIST:
					{
						if (this._readOnly)
						{
							string deliLeft = (String.IsNullOrEmpty(this._readOnlyDeli) ? this._readOnlyDeli[0].ToString() : "");
							string deliRight = (String.IsNullOrEmpty(this._readOnlyDeli) ? this._readOnlyDeli[1].ToString() : "");

							XmlInputLabelField xlf = new XmlInputLabelField(this._caption, deliLeft + this._values[this._selected] + deliRight);
							XmlInputHidden xih = new XmlInputHidden(this._name, this._selected);
							xlf.generateObject(current);
							xih.generateObject(current);
							return;
						}
						else
						{
							nodeWorking = util.XmlUtil.CreateChild(current, "select", "");
							util.XmlUtil.AddAttribute(nodeWorking, "caption", this._caption);
							util.XmlUtil.AddAttribute(nodeWorking, "name", this._name);
							if (this._required)
							{
								util.XmlUtil.AddAttribute(nodeWorking, "required", "true");
							}
							if (this._size > 1)
							{
								util.XmlUtil.AddAttribute(nodeWorking, "size", this._size);
							}
						}
						break;
					}
				case EasyListType.UNORDEREDLIST:
					{
						util.XmlUtil.CreateChild(current, "b", this._caption);
						nodeWorking = util.XmlUtil.CreateChild(current, "ul", "");
						break;
					}
			}

			int i = 0;

			foreach (string key in this._values.Keys)
			{
				string value = this._values[key];
				switch (this._easyListType)
				{
					case EasyListType.CHECKBOX:
						{
							XmlInputCheck iCk = new XmlInputCheck(value, this._name + (i++).ToString(), key);
							iCk.setType(InputCheckType.CHECKBOX);
							iCk.setChecked(key == this._selected);
							iCk.setReadOnly(this._readOnly);
							iCk.generateObject(nodeWorking);
							break;
						}
					case EasyListType.RADIOBOX:
						{
							XmlInputCheck iCk = new XmlInputCheck(value, this._name, key);
							iCk.setType(InputCheckType.RADIOBOX);
							iCk.setChecked(key == this._selected);
							iCk.setReadOnly(this._readOnly);
							iCk.generateObject(nodeWorking);
							break;
						}
					case EasyListType.SELECTLIST:
						{
							XmlNode node = util.XmlUtil.CreateChild(nodeWorking, "option", "");
							util.XmlUtil.AddAttribute(node, "value", key);
							if (key == this._selected)
							{
								util.XmlUtil.AddAttribute(node, "selected", "yes");
							}
							util.XmlUtil.AddTextNode(node, value);
							break;
						}
					case EasyListType.UNORDEREDLIST:
						{
							util.XmlUtil.CreateChild(nodeWorking, "li", value);
							break;
						}
				}
			}

		}

	}

}