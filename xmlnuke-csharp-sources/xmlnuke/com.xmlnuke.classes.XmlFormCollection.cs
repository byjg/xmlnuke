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

namespace com.xmlnuke.classes
{
	public class XmlFormCollection : XmlnukeCollection, IXmlnukeDocumentObject
	{
		protected string _action;
		protected string _title;
		protected string _formname;
		protected Context _context;
		protected bool _jsValidate;
		protected char _decimalSeparator;
		protected DATEFORMAT _dateformat;
		protected string _target;
		protected XmlnukeAjaxCallback _ajaxcallback = null;
		protected string _customSubmit = "";


		public XmlFormCollection(Context context, string action, string title)
			: this(context, action, title, "")
		{ }

		public XmlFormCollection(Context context, string action, string title, string target)
			: base()
		{
			this._action = action;
			this._title = title;
			this._context = context;
			this._formname = "frm" + this._context.getRandomNumber(10000).ToString();
			this._jsValidate = true;
			this._decimalSeparator = '.';
			this._dateformat = DATEFORMAT.DMY;
			this._target = target;
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

		public void setTarget(string target)
		{
			this._target = target;
		}

		public void setAjaxCallback(XmlnukeAjaxCallback objAjax)
		{
			this._ajaxcallback = objAjax;
		}

		public void setCustomSubmit(string customSubmit)
		{
			this._customSubmit = customSubmit;
		}

		/// <summary>
		/// Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
		/// </summary>
		/// <param name="px">PageXml class</param>
		/// <param name="current">XmlNode where the XML will be created.</param>
		public void generateObject(XmlNode current)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(current, "editform", "");
			util.XmlUtil.AddAttribute(nodeWorking, "action", this._action);
			util.XmlUtil.AddAttribute(nodeWorking, "title", this._title);
			util.XmlUtil.AddAttribute(nodeWorking, "name", this._formname);
			if (this._target != "")
			{
				util.XmlUtil.AddAttribute(nodeWorking, "target", this._target);
			}
			if (this._jsValidate)
			{
				util.XmlUtil.AddAttribute(nodeWorking, "jsvalidate", "true");
				util.XmlUtil.AddAttribute(nodeWorking, "decimalseparator", Convert.ToString(this._decimalSeparator));
				util.XmlUtil.AddAttribute(nodeWorking, "dateformat", Convert.ToInt32(this._dateformat).ToString());
				this._customSubmit += ((this._customSubmit != "") ? " && " : "") + this._formname + "_checksubmit()";
			}

			if (this._ajaxcallback != null)
			{
				string ajaxId = this._ajaxcallback.getId();
				this._customSubmit += ((this._customSubmit != "") ? " && " : "") + "AIM.submit(this, {'onStart' : startCallback" + ajaxId + ", 'onComplete' : completeCallback" + ajaxId + "})";
			}

			if (this._customSubmit != "")
			{
				util.XmlUtil.AddAttribute(nodeWorking, "customsubmit", this._customSubmit);
			}

			this.generatePage(nodeWorking);
		}

	}
}