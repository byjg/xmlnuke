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

	public class XmlInputGroup : XmlnukeCollection, IXmlnukeDocumentObject
	{

		protected Context _context;
		protected string _name;
		protected bool _canhide = false;
		protected bool _breakline = false;
		protected string _caption;
		protected bool _visible = true;

		public XmlInputGroup(Context context)
		{
			this._context = context;
			this._name = "ING" + this._context.getRandomNumber(100000);
		}

		public XmlInputGroup(Context context, string name)
			: this(context)
		{
			this._name = name;
		}

		public XmlInputGroup(Context context, string name, bool breakline)
			: this(context, name)
		{
			this._breakline = breakline;
		}

		public XmlInputGroup(Context context, string name, bool breakline, bool canhide, string caption)
			: this(context, name, breakline)
		{
			this._canhide = canhide;
			this._caption = caption;
		}

		public void setVisible(bool value)
		{
			this._visible = value;
		}

		/// <summary>
		/// Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
		/// </summary>
		/// <param name="px">PageXml class</param>
		/// <param name="current">XmlNode where the XML will be created.</param>
		public void generateObject(XmlNode current)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(current, "inputgroup", "");
			util.XmlUtil.AddAttribute(nodeWorking, "name", this._name);
			if (!String.IsNullOrEmpty(this._caption))
			{
				util.XmlUtil.AddAttribute(nodeWorking, "caption", this._caption);
			}
			if (this._canhide)
			{
				util.XmlUtil.AddAttribute(nodeWorking, "canhide", "true");
			}
			if (this._breakline)
			{
				util.XmlUtil.AddAttribute(nodeWorking, "breakline", "true");
			}
			if (!this._visible)
			{
				util.XmlUtil.AddAttribute(nodeWorking, "visible", "false");
			}
			this.generatePage(nodeWorking);
		}

	}

}