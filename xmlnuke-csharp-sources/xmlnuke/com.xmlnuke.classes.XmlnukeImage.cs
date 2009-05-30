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
	public class XmlnukeImage : XmlnukeDocumentObject
	{
		private string _src;
		private string _alt;
		private int _width;
		private int _height;
		private string _alternateImage;

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

		public void setAlternateImage(string src)
		{
			this._alternateImage = src;
		}

		/// <summary>
		/// Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
		/// </summary>
		/// <param name="px">PageXml class</param>
		/// <param name="current">XmlNode where the XML will be created.</param>
		public override void generateObject(XmlNode current)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(current, "img", "");
			util.XmlUtil.AddAttribute(nodeWorking, "src", this._src);
			util.XmlUtil.AddAttribute(nodeWorking, "alt", this._alt);
			if (this._width != 0)
			{
				util.XmlUtil.AddAttribute(nodeWorking, "width", this._width.ToString());
			}
			if (this._height != 0)
			{
				util.XmlUtil.AddAttribute(nodeWorking, "height", this._height.ToString());
			}
			if (this._alternateImage != "")
			{
	 			util.XmlUtil.AddAttribute(nodeWorking, "altimage", this._alternateImage);
			}
		}

	}

}