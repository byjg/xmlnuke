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

	/// <summary>
	/// Class to represent the &lt;a&gt;&lt;/a&gt; XMLNuke tag.
	/// Elements and attributes defined:
	/// <c>
	/// &lt;a href="" target=""&gt;(... other XmlnukeDocumentObjects ...)&lt;/a&gt;
	/// </c>
	/// </summary>
	public class XmlAnchorCollection : XmlnukeCollection, IXmlnukeDocumentObject
	{
		protected string _src;
		protected string _target;

		public XmlAnchorCollection(string hrefSrc)
			: this(hrefSrc, "")
		{ }

		public XmlAnchorCollection(string hrefSrc, string target)
			: base()
		{
			_src = hrefSrc;
			_target = target;
		}

		/// <summary>
		/// Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
		/// </summary>
		/// <param name="px">PageXml class</param>
		/// <param name="current">XmlNode where the XML will be created.</param>
		public void generateObject(XmlNode current)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(current, "a", "");
			util.XmlUtil.AddAttribute(nodeWorking, "href", this._src);
			if (this._target != "")
			{
				util.XmlUtil.AddAttribute(nodeWorking, "target", this._target);
			}

			this.generatePage(nodeWorking);
		}

	}

}