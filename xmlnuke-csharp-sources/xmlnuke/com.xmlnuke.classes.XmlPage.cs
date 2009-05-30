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

namespace com.xmlnuke.classes
{
	public abstract class XmlnukeCollection
	{
		protected ArrayList _items;

		public XmlnukeCollection()
		{
			_items = new ArrayList();
		}

		public void addXmlnukeObject(IXmlnukeDocumentObject docobj)
		{
			_items.Add(docobj);
		}
	
		protected PageXml generatePage(PageXml px, XmlNode current)
		{
			foreach(IXmlnukeDocumentObject item in _items)
			{
				item.generateObject(px, current);
			}
			return px;
		}
	}
	
	
	public class XmlnukeDocument : XmlnukeCollection
	{
		protected string _title = "XmlNuke Page";
		protected string _abstract = "";
		protected string _groupKeyword = "";
		protected DateTime _created = DateTime.Now;

		public XmlnukeDocument() : base(){}

		public XmlnukeDocument(string title, string desc) : base()
		{
			this._title = title;
			this._abstract = desc;
		}
		
		public PageXml generatePage()
		{
			return this.generatePage(new PageXml(), null);
		}

		/// <summary>
		/// Get/Set the xml metadata title
		/// </summary>
		public string Title
		{
			set
			{
				_title = value;
			}
			get
			{
				return _title;
			}
		}

		/// <summary>
		/// Get/Set the xml metadata abstract
		/// </summary>
		public string Abstract
		{
			set
			{
				_abstract = value;
			}
			get
			{
				return _abstract;
			}
		}

		/// <summary>
		/// Get/Set the xml metadata groupkeyword (used to list menus)
		/// </summary>
		public string GroupKeyword
		{
			set
			{
				_groupKeyword = value;
			}
			get
			{
				return _groupKeyword;
			}
		}

		/// <summary>
		/// Get the xml metadata datetime created
		/// </summary>
		public DateTime Created
		{
			get
			{
				return _created;
			}
		}
	
	}


	public interface IXmlnukeDocumentObject
	{
		void generateObject(PageXml px, XmlNode current);
	}
	
	public abstract class XmlnukeDocumentObject : IXmlnukeDocumentObject
	{
		public abstract void generateObject(PageXml px, XmlNode current);
	}


	public enum BlockPosition
	{
		Left,
		Center,
		Right
	}
	
	/// <summary>
	/// Summary description for com.
	/// </summary>
	public class XmlBlockCollection : XmlnukeCollection, IXmlnukeDocumentObject 
	{
		protected string _title;
		protected BlockPosition _position;

		public XmlBlockCollection(string title, BlockPosition position) : base()
		{
			_title = title;
			_position = position;
		}

		public void generateObject(PageXml px, XmlNode current)
		{
			string block = "";
			switch (_position)
			{
				case BlockPosition.Center:
					block = "blockcenter";
					break;
				case BlockPosition.Left:
					block = "blockleft";
					break;
				case BlockPosition.Right:
					block = "blockright";
					break;
			}

			XmlDocument xmlDoc = px.getDomObject();
			XmlNode nodePage = xmlDoc.SelectSingleNode("page");
			
			XmlNode objBlockCenter = util.XmlUtil.CreateChild(nodePage, block, "");
			util.XmlUtil.CreateChild(objBlockCenter, "title", this._title);
			
			this.generatePage(px, util.XmlUtil.CreateChild(objBlockCenter, "body", ""));
		}

	}
	
	public class XmlParagraphCollection : XmlnukeCollection, IXmlnukeDocumentObject 
	{
		public void generateObject(PageXml px, XmlNode current)
		{
			this.generatePage(px, util.XmlUtil.CreateChild(current, "p", ""));
		}
	}
	
	public class XmlnukeText : XmlnukeDocumentObject
	{
		private string _text;
		private bool _bold;
		private bool _italic;
		private bool _underline;
		private bool _breakline;
		
		public XmlnukeText(string text) : this(text, false, false, false, false) 
		{}
		
		public XmlnukeText(string text, bool bold, bool italic, bool underline) : this(text, bold, italic, underline, false) 
		{}

		public XmlnukeText(string text, bool bold, bool italic, bool underline, bool breakline)
		{
			this._text = text;
			this._bold = bold;
			this._italic = italic;
			this._underline = underline;
			this._breakline = breakline;
		}

		public override void generateObject(PageXml px, XmlNode current)
		{
			XmlNode node = current;
			if (this._bold)
			{
				node = util.XmlUtil.CreateChild(node, "b", "");
			}
			if (this._italic)
			{
				node = util.XmlUtil.CreateChild(node, "i", "");
			}
			if (this._underline)
			{
				node = util.XmlUtil.CreateChild(node, "u", "");
			}

			util.XmlUtil.AddTextNode(node, this._text);
			
			if (_breakline)
			{
				util.XmlUtil.CreateChild(node, "br", "");
			}
		}
	
	}

	public class XmlnukeImage : XmlnukeDocumentObject
	{
		private string _src;
		private string _alt;
		private int _width;
		private int _height;
		
		public XmlnukeImage(string src) 
		{
			this._src = src;
			this._alt = "";
		}

		public XmlnukeImage(string src, string text)
		{
			this._src = src;
			this._alt = text;
		}

		public void setText(string text)
		{
			this._alt = text;
		}
		
		public void setDimension(int width, int height)
		{
			this._width = width;
			this._height = height;
		}

		public override void generateObject(PageXml px, XmlNode current)
		{
			px.addImage(current, this._src, this._alt, this._width, this._height);
		}
		
	}

	public class XmlnukeBreakLine : XmlnukeDocumentObject
	{
		public override void generateObject(PageXml px, XmlNode current)
		{
			util.XmlUtil.CreateChild(current, "br", "");
		}
		
	}
	
	public class XmlAnchorCollection : XmlnukeCollection, IXmlnukeDocumentObject 
	{
		protected string _src;
		protected string _target;

		public XmlAnchorCollection(string hrefSrc, string target) : base()
		{
			_src = hrefSrc;
			_target = target;
		}

		public void generateObject(PageXml px, XmlNode current)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(current, "a", "");
			util.XmlUtil.AddAttribute(nodeWorking, "href", this._src);
			if (this._target != "")
			{
				util.XmlUtil.AddAttribute(nodeWorking, "target", this._target);
			}

			this.generatePage(px, nodeWorking);
		}

	}

	/*
	public class XmlListCollection : XmlnukeCollection, IXmlnukeDocumentObject 
	{
		public XmlAnchorCollection() : base()
		{}

		public void generateObject(PageXml px, XmlNode current)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(current, "ul", "")			

			this.generatePage(px, nodeWorking);
		}

	}
	*/
	
	public struct CustomButtons
	{
		public bool enabled;
		public string url;
		public string icon;
		public string action;
		public string alternateText;
	}
	
	public enum SelectType
	{
		RADIO,
		CHECKBOX
	}
	
	public enum EditListFieldType
	{
		TEXT,
		IMAGE
	}
	
	public struct EditListField
	{
		public string fieldData;     // Name of field it contains DATA in the SINGLEROW
		public string editlistName;  // Header NAME for this field (Show on top)
		public EditListFieldType fieldType;
		public int maxSize;
	}

	public class XmlEditList : XmlnukeDocumentObject
	{
		protected string _title;
		protected string _module;
		protected bool _new;
		protected bool _view;
		protected bool _edit;
		protected bool _delete;
		protected bool _readonly;
		protected SelectType _selecttype;
		protected com.xmlnuke.anydataset.Iterator _it;
		protected EditListField[] _fields;
		
		protected CustomButtons[] CustomButton;
		
		public XmlEditList(string title, string module) : this(title, module, true, true, true, true)
		{}

		public XmlEditList(string title, string module, bool newButton, bool view, bool edit, bool delete)
		{
			this._module = module;
			this._title = title;
			this._new = newButton;
			this._view = view;
			this._edit = edit;
			this._delete = delete;
			this._readonly = false;
			this._selecttype = SelectType.RADIO;
			
			CustomButton = new CustomButtons[4];
			for(int i=0; i<4; i++) 
			{
				CustomButton[i].enabled = false;
				CustomButton[i].url = this._module;
				CustomButton[i].icon = "";
				CustomButton[i].action = "custom" + (i+1).ToString();
				CustomButton[i].alternateText = "";
			}
		}
		
		public CustomButtons customButton(int i)
		{
			return CustomButton[i+1];
		}
		
		public void setDataSource(com.xmlnuke.anydataset.Iterator it)
		{
			this._it = it;
		}
		
		public void setEditListField(EditListField[] fields)
		{
			this._fields = fields;
		}

		public override void generateObject(PageXml px, XmlNode current)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(current, "editlist", "");
			util.XmlUtil.AddAttribute(nodeWorking, "module", this._module);
			util.XmlUtil.AddAttribute(nodeWorking, "title", this._title);
			util.XmlUtil.AddAttribute(nodeWorking, "cols", this._fields.Length.ToString());
			
			if(this._new)
				util.XmlUtil.AddAttribute(nodeWorking, "new", "true");
			if(this._edit)
				util.XmlUtil.AddAttribute(nodeWorking, "edit", "true");
			if(this._view)
				util.XmlUtil.AddAttribute(nodeWorking, "view", "true");
			if(this._delete)
				util.XmlUtil.AddAttribute(nodeWorking, "delete", "true");
			if(this._readonly)
				util.XmlUtil.AddAttribute(nodeWorking, "readonly", "true");
			if(this._selecttype == SelectType.CHECKBOX)
				util.XmlUtil.AddAttribute(nodeWorking, "selecttype", "check");

			while (this._it.hasNext())
			{
				com.xmlnuke.anydataset.SingleRow registro = this._it.moveNext();
				XmlNode row = util.XmlUtil.CreateChild(nodeWorking, "row", "");
				
				foreach(EditListField field in this._fields)
				{
					XmlNode nodeField = util.XmlUtil.CreateChild(row, "field", "");
					util.XmlUtil.AddAttribute(nodeField, "name", field.editlistName);
					switch (field.fieldType) 
					{
						case EditListFieldType.TEXT:
						{
							util.XmlUtil.AddTextNode(nodeField, registro.getField(field.fieldData));
							break;
						}
						case EditListFieldType.IMAGE:
						{
							px.addImage(nodeField, registro.getField(field.fieldData), "", 0, 0);
							break;
						}
					}
				}
			}
		}	
	}
	
	
	
	
	public class XmlFormCollection : XmlnukeCollection, IXmlnukeDocumentObject 
	{
		protected string _action;
		protected string _title;
		protected string _formname;
		protected bool _jsValidate;
		protected char _decimalSeparator;
		protected DATEFORMAT _dateformat;
	
		public XmlFormCollection(string action, string title) : base()
		{
			this._action = action;
			this._title = title;
			this._formname = this.ToString();
			this._jsValidate = true;
			this._decimalSeparator = '.';
			this._dateformat = DATEFORMAT.DMY;
		}
		
		public void setJSValidate(bool enable)
		{
			this._jsValidate = enable;
		}

		public void setFormName(string name)
		{
			this._formname = name;
		}

		public void setDecimalSeparator(char separator)
		{
			this._decimalSeparator = separator;
		}

		public void setDateFormat(DATEFORMAT format)
		{
			this._dateformat = format;
		}

		public void generateObject(PageXml px, XmlNode current)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(current, "editform", "");
			util.XmlUtil.AddAttribute(nodeWorking, "action", this._action);
			util.XmlUtil.AddAttribute(nodeWorking, "title", this._title);
			util.XmlUtil.AddAttribute(nodeWorking, "name", this._formname);
			if (this._jsValidate)
			{
				util.XmlUtil.AddAttribute(nodeWorking, "jsvalidate", "true");
				util.XmlUtil.AddAttribute(nodeWorking, "decimalseparator", Convert.ToString(this._decimalSeparator));
				util.XmlUtil.AddAttribute(nodeWorking, "dateformat", this._dateformat.ToString());
			}
				
			this.generatePage(px, nodeWorking);
		}

	}
	

	public class XmlInputCaption : XmlnukeDocumentObject
	{
		protected string _caption;
		
		public XmlInputCaption(string caption)
		{ 
			this._caption = caption;
		}

		public override void generateObject(PageXml px, XmlNode current)
		{
			util.XmlUtil.CreateChild(current, "caption", this._caption);
		}
	
	}

	
	public class XmlInputHidden : XmlnukeDocumentObject
	{
		protected string _name;
		protected string _value;
		
		public XmlInputHidden(string name, string value)
		{
			this._name = name;
			this._value = value;
		}

		public override void generateObject(PageXml px, XmlNode current)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(current, "hidden", "");
			util.XmlUtil.AddAttribute(nodeWorking, "name", this._name);
			util.XmlUtil.AddAttribute(nodeWorking, "value", this._value);
		}
	}
	
	public class XmlInputLabelField : XmlnukeDocumentObject
	{
		protected string _caption;
		protected string _value;
		
		public XmlInputLabelField(string caption, string value)
		{
			this._caption = caption;
			this._value = value;
		}

		public override void generateObject(PageXml px, XmlNode current)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(current, "label", "");
			util.XmlUtil.AddAttribute(nodeWorking, "caption", this._caption);
			util.XmlUtil.AddAttribute(nodeWorking, "value", this._value);
		}
	}

	public abstract class XmlInputValidate : XmlnukeDocumentObject
	{
		protected bool _required;
		protected INPUTTYPE _inputtype;
		protected string _minvalue;
		protected string _maxvalue;
		protected string _description;
		protected string _customjs;
		
		public XmlInputValidate()
		{
			this._required = false;
			this._inputtype = INPUTTYPE.TEXT;
			this._minvalue = "";
			this._maxvalue = "";
			this._description = "";
			this._customjs = "";
		}
		
		public void setRequired(bool required)
		{
			this._required = required;
		}
		
		public void setRange(string minvalue, string maxvalue)
		{
			this._minvalue = minvalue;
			this._maxvalue = maxvalue;
		}

		public void setDescription(string description)
		{
			this._description = description;
		}

		public void setCustomJS(string customjs)
		{
			this._customjs = customjs;
		}

		public override void generateObject(PageXml px, XmlNode current)
		{
			util.XmlUtil.AddAttribute(current, "required", this._required.ToString().ToLower());
			util.XmlUtil.AddAttribute(current, "type", Convert.ToInt32(this._inputtype).ToString());
			if (this._minvalue != "")
			{
				util.XmlUtil.AddAttribute(current, "minvalue", this._minvalue);
			}
			if (this._maxvalue != "")
			{
				util.XmlUtil.AddAttribute(current, "maxvalue", this._maxvalue);
			}
			if (this._description != "")
			{
				util.XmlUtil.AddAttribute(current, "description", this._description);
			}
			if (this._customjs != "")
			{
				util.XmlUtil.AddAttribute(current, "customjs", this._customjs);
			}
		}
	}
	
	public enum InputTextBoxType
	{
		TEXT, 
		PASSWORD
	}
	
	public class XmlInputTextBox : XmlInputValidate
	{
		protected string _name;
		protected string _caption;
		protected string _value;
		protected int _size;
		protected InputTextBoxType _inputextboxtype;
		
		public XmlInputTextBox(string caption, string name, string value) : base()
		{
			this._name = name;
			this._value = value;
			this._caption = caption;
			this._size = 20;
		}

		public XmlInputTextBox(string caption, string name, string value, int size) : base()
		{
			this._name = name;
			this._value = value;
			this._caption = caption;
			this._size = size;
		}
		
		public void setInputTextBoxType(InputTextBoxType inputextboxtype)
		{
			this._inputextboxtype = inputextboxtype;
		}

		public override void generateObject(PageXml px, XmlNode current)
		{
			XmlNode nodeWorking;
			if (this._inputextboxtype == InputTextBoxType.TEXT)
			{
				nodeWorking = util.XmlUtil.CreateChild(current, "textbox", "");
			}
			else
			{
				nodeWorking = util.XmlUtil.CreateChild(current, "password", "");
			}
			util.XmlUtil.AddAttribute(nodeWorking, "caption", this._caption);
			util.XmlUtil.AddAttribute(nodeWorking, "name", this._name);
			util.XmlUtil.AddAttribute(nodeWorking, "value", this._value);
			util.XmlUtil.AddAttribute(nodeWorking, "size", this._size.ToString());
			base.generateObject(px, current);
		}
	
	}

	/*
		
	public class XmlInputCaption : XmlnukeDocumentObject
	{
		protected string _name;
		protected string _caption;
		protected string _value;
		
		protected INPUTTYPE _inputType;
		
		public XmlInputObject(string caption) : this(XMLINPUTMODE.CAPTION, caption, "", "")
		{}

		public XmlInputObject(XMLINPUTMODE inputMode, string name, string value) : this(inputMode, "", name, value)
		{}

		public XmlInputObject(XMLINPUTMODE inputMode, string caption, string name, string value)
		{
			this._xmlInputMode = inputMode;
			this._name = name;
			this._value = value;
			this._caption = caption;
		}
		
		public void setInputType(INPUTTYPE type)
		{
			this._inputType = type;
		}
	
	}

			
				public class XmlInputCaption : XmlnukeDocumentObject
	{
		protected string _name;
		protected string _caption;
		protected string _value;
		
		protected INPUTTYPE _inputType;
		
		public XmlInputObject(string caption) : this(XMLINPUTMODE.CAPTION, caption, "", "")
		{}

		public XmlInputObject(XMLINPUTMODE inputMode, string name, string value) : this(inputMode, "", name, value)
		{}

		public XmlInputObject(XMLINPUTMODE inputMode, string caption, string name, string value)
		{
			this._xmlInputMode = inputMode;
			this._name = name;
			this._value = value;
			this._caption = caption;
		}
		
		public void setInputType(INPUTTYPE type)
		{
			this._inputType = type;
		}
	
	}

				
					public class XmlInputCaption : XmlnukeDocumentObject
	{
		protected string _name;
		protected string _caption;
		protected string _value;
		
		protected INPUTTYPE _inputType;
		
		public XmlInputObject(string caption) : this(XMLINPUTMODE.CAPTION, caption, "", "")
		{}

		public XmlInputObject(XMLINPUTMODE inputMode, string name, string value) : this(inputMode, "", name, value)
		{}

		public XmlInputObject(XMLINPUTMODE inputMode, string caption, string name, string value)
		{
			this._xmlInputMode = inputMode;
			this._name = name;
			this._value = value;
			this._caption = caption;
		}
		
		public void setInputType(INPUTTYPE type)
		{
			this._inputType = type;
		}
	
	}
	*/

}
