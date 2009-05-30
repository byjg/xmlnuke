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

		public void setDataType(INPUTTYPE itype)
		{
			this._inputtype = itype;
		}

		/// <summary>
		/// Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
		/// </summary>
		/// <param name="px">PageXml class</param>
		/// <param name="current">XmlNode where the XML will be created.</param>
		public override void generateObject(XmlNode current)
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
}