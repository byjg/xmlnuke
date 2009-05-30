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

	public class XmlInputMemo : XmlnukeDocumentObject
	{
		protected string _name;
		protected string _caption;
		protected string _value;
		protected int _cols;
		protected int _rows;
		protected string _wrap;
		protected bool _readonly;
		protected int _maxlength;
		protected bool _visualEditor;
		protected string _visualEditorBaseHref;

		public XmlInputMemo(string caption, string name, string value)
			: base()
		{
			this._name = name;
			this._value = value;
			this._caption = caption;
			this._cols = 50;
			this._rows = 10;
			this._maxlength = 0;
			this._wrap = "SOFT";  // "OFF"
			this._readonly = false;
			this._visualEditor = false;
		}

		public void setSize(int cols, int rows)
		{
			this._cols = cols;
			this._rows = rows;
		}

		public void setWrap(string wrap)
		{
			if ((wrap != "SOFT") && (wrap != "OFF"))
			{
				throw new Exception("InputMemo wrap values must be SOFT or OFF");
			}
			this._wrap = wrap;
		}

		public void setMaxLength(int value)
		{
			this._maxlength = value;
		}

		public void setReadOnly(bool value)
		{
			this._readonly = value;
		}

		public void setVisualEditor(bool value)
		{
			this._visualEditor = value;
		}

		/// <summary>
		/// Defines the Base Href to locate images and other objects
		/// </summary>
		/// <param name="value"></param>
		public void setVisualEditorBaseHref(string value)
		{
			this._visualEditorBaseHref = value;
		}

		/// <summary>
		/// Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
		/// </summary>
		/// <param name="px">PageXml class</param>
		/// <param name="current">XmlNode where the XML will be created.</param>
		public override void generateObject(XmlNode current)
		{
			if (this._readonly)
			{
				XmlInputLabelField ic = new XmlInputLabelField(this._caption, this._value);
				ic.generateObject(current);

				XmlInputHidden ih = new XmlInputHidden(this._name, this._value);
				ih.generateObject(current);
			}
			else
			{
				XmlNode nodeWorking = util.XmlUtil.CreateChild(current, "memo", "");
				util.XmlUtil.AddAttribute(nodeWorking, "caption", this._caption);
				util.XmlUtil.AddAttribute(nodeWorking, "name", this._name);
				util.XmlUtil.AddAttribute(nodeWorking, "cols", this._cols.ToString());
				util.XmlUtil.AddAttribute(nodeWorking, "rows", this._rows.ToString());
				util.XmlUtil.AddAttribute(nodeWorking, "wrap", this._wrap);
				if (this._visualEditor)
				{
					util.XmlUtil.AddAttribute(nodeWorking, "visualedit", "true");
					util.XmlUtil.AddAttribute(nodeWorking, "visualeditbasehref", this._visualEditorBaseHref);
				}
				else if (this._maxlength > 0)
				{
					util.XmlUtil.AddAttribute(nodeWorking, "maxlength", this._maxlength);
				}
				util.XmlUtil.AddTextNode(nodeWorking, this._value);
			}
		}

	}

}