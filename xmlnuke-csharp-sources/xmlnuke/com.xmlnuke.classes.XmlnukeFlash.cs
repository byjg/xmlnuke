/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  CSharp Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
 *  Acknowledgments to: Roan Brasil Monteiro
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
using System.Collections.Generic;
using System.Xml;

namespace com.xmlnuke.classes
{

	public class XmlnukeFlash : XmlnukeCollection, IXmlnukeDocumentObject
	{
		private string _movie;
		private int _width;
		private int _height;
		private Dictionary<string, string> _extraParams = new Dictionary<string, string>();

		protected int _majorVersion = 9;
		protected int _minorVersion = 0;
		protected int _revision = 45;

		public XmlnukeFlash()
		{
		}

		public XmlnukeFlash(int majorVersion, int minorVersion, int revision)
		{
			this._majorVersion = majorVersion;
			this._minorVersion = minorVersion;
			this._revision = revision;
		}

		public void setMovie(string text)
		{
			this._movie = text;
		}

		public string getMovie()
		{
			return this._movie.Replace(".swf", "");
		}

		public void setWidth(int integer)
		{
			this._width = integer;
		}

		public int getWidth()
		{
			return this._width;
		}

		public void setHeight(int integer)
		{
			this._height = integer;
		}

		public int getHeight()
		{
			return this._height;
		}

		public void setDimension(int width, int height)
		{
			this._width = width;
			this._height = height;
		}
		
		public void addParam(string name, string value)
		{
			this._extraParams.Add(name, value);
		}
		
		public void generateObject(XmlNode current)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(current, "flash", "");
			util.XmlUtil.AddAttribute(nodeWorking, "major", this._majorVersion.ToString());
			util.XmlUtil.AddAttribute(nodeWorking, "minor", this._minorVersion.ToString());
			util.XmlUtil.AddAttribute(nodeWorking, "revision", this._revision.ToString());

			if (this._movie != "")
			{
				util.XmlUtil.AddAttribute(nodeWorking, "movie", this.getMovie());
			}
			if (this._width != 0)
			{
				util.XmlUtil.AddAttribute(nodeWorking, "width", this.getWidth());
			}
			if (this._height != 0)
			{
				util.XmlUtil.AddAttribute(nodeWorking, "height", this.getHeight());
			}
			
			foreach( KeyValuePair<string, string> pair in this._extraParams )
			{
				XmlNode param = util.XmlUtil.CreateChild(nodeWorking, "param");
				util.XmlUtil.AddAttribute(param, "name", pair.Key);
				util.XmlUtil.AddAttribute(param, "value", pair.Value);
			}

			this.generatePage(nodeWorking);
		}
	}

}