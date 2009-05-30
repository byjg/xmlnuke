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

	public class XmlnukeText : XmlnukeDocumentObject
	{
		private string _text;
		private bool _bold;
		private bool _italic;
		private bool _underline;
		private bool _breakline;

		public XmlnukeText(string text)
			: this(text, false, false, false, false)
		{ }

		public XmlnukeText(string text, bool bold)
			: this(text, bold, false, false, false)
		{ }

		public XmlnukeText(string text, bool bold, bool italic)
			: this(text, bold, italic, false, false)
		{ }

		public XmlnukeText(string text, bool bold, bool italic, bool underline)
			: this(text, bold, italic, underline, false)
		{ }

		public XmlnukeText(string text, bool bold, bool italic, bool underline, bool breakline)
		{
			this._text = text;
			this._bold = bold;
			this._italic = italic;
			this._underline = underline;
			this._breakline = breakline;
		}

		/// <summary>
		/// Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
		/// </summary>
		/// <param name="px">PageXml class</param>
		/// <param name="current">XmlNode where the XML will be created.</param>
		public override void generateObject(XmlNode current)
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

}