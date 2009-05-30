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
	public enum InputCheckType
	{
		CHECKBOX,
		RADIOBOX
	}

	public class XmlInputCheck : XmlnukeDocumentObject
	{
		protected string _name;
		protected string _caption;
		protected string _value;
		protected bool _checked;
		protected bool _readonly;
		protected InputCheckType _inputCheckType;

		public XmlInputCheck(string caption, string name, string value)
			: base()
		{
			this._name = name;
			this._value = value;
			this._caption = caption;
			this._checked = false;
			this._inputCheckType = InputCheckType.CHECKBOX;
			this._readonly = false;
		}

		public void setChecked(bool value)
		{
			this._checked = value;
		}

		public void setType(InputCheckType inputCheckType)
		{
			this._inputCheckType = inputCheckType;
		}

		public void setReadOnly(bool value)
		{
			this._readonly = value;
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
				XmlInputLabelField ic;
				if (this._checked)
				{
					XmlInputHidden ih = new XmlInputHidden(this._name, this._value);
					ic = new XmlInputLabelField(this._caption, "[X]");
					ih.generateObject(current);
				}
				else
				{
					ic = new XmlInputLabelField(this._caption, "[ ]");
				}
				ic.generateObject(current);
			}
			else
			{
				XmlNode nodeWorking;
				if (this._inputCheckType == InputCheckType.CHECKBOX)
				{
					nodeWorking = util.XmlUtil.CreateChild(current, "checkbox", "");
				}
				else
				{
					nodeWorking = util.XmlUtil.CreateChild(current, "radiobox", "");
				}
				util.XmlUtil.AddAttribute(nodeWorking, "caption", this._caption);
				util.XmlUtil.AddAttribute(nodeWorking, "name", this._name);
				util.XmlUtil.AddAttribute(nodeWorking, "value", this._value);
				if (this._checked)
				{
					util.XmlUtil.AddAttribute(nodeWorking, "selected", "yes");
				}
			}
		}

	}

}