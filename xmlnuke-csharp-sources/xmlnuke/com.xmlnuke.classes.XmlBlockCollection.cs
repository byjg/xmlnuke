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
	public enum BlockPosition
	{
		Left,
		Center,
		Right
	}

	/// <summary>
	/// Class to represent the &lt;blockcenter&gt;&lt;/blockcenter&gt;, &lt;blockleft&gt;&lt;/blockleft&gt; and &lt;blockright&gt;&lt;/blockright&gt; XMLNuke tag.
	/// Elements and attributes defined:
	/// <c>
	/// &lt;blockcenter&gt;
	///   &lt;title&gt;&lt;/title&gt;
	///   &lt;body&gt;
	///       (... other XmlnukeDocumentObjects ...)
	///   &lt;/body&gt;
	/// &lt;/blockcenter&gt;
	/// </c>
	/// </summary>
	public class XmlBlockCollection : XmlnukeCollection, IXmlnukeDocumentObject
	{
		protected string _title;
		protected BlockPosition _position;

		public XmlBlockCollection(string title, BlockPosition position)
			: base()
		{
			_title = title;
			_position = position;
		}

		public void setTitle(string title)
		{
			this._title = title;
		}

		/// <summary>
		/// Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
		/// </summary>
		/// <param name="px">PageXml class</param>
		/// <param name="current">XmlNode where the XML will be created.</param>
		public void generateObject(XmlNode current)
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

			XmlNode objBlockCenter = util.XmlUtil.CreateChild(current, block, "");
			util.XmlUtil.CreateChild(objBlockCenter, "title", this._title);

			this.generatePage(util.XmlUtil.CreateChild(objBlockCenter, "body", ""));
		}

	}
}