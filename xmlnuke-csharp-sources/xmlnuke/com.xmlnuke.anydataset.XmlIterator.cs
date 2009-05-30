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
using System.Xml;
using System.Collections.Specialized;

namespace com.xmlnuke.anydataset
{

	public class XmlIterator : IIterator
	{
		private com.xmlnuke.engine.Context _context = null;
		private XmlNodeList _nodeList = null;
		//private string _rowNode = null;
		private NameValueCollection _colNodes = null;
		private int _current = 0;

		public XmlIterator(com.xmlnuke.engine.Context context, XmlNodeList nodeList, NameValueCollection colNodes)
		{
			this._context = context;
			this._nodeList = nodeList;
			this._colNodes = colNodes;

			this._current = 0;
		}

		public int Count()
		{
			return this._nodeList.Count;
		}

		public bool hasNext()
		{
			if (this._current < this.Count())
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public SingleRow moveNext()
		{
			if (!this.hasNext())
			{
				throw new Exception("No more records. Did you used hasNext() before moveNext()?");
			}

			XmlNode node = this._nodeList[this._current++];

			AnyDataSet any = new AnyDataSet();
			any.appendRow();
			foreach (string key in this._colNodes.Keys)
			{
				string colxpath = this._colNodes[key];
				XmlNode nodecol = node.SelectSingleNode(colxpath);
				if (nodecol == null)
				{
					any.addField(key.ToLower(), "");
				}
				else
				{
					//Debug.PrintValue(nodecol);
					any.addField(key.ToLower(), nodecol.InnerXml);
				}
			}

			IIterator it = any.getIterator();
			SingleRow sr = it.moveNext();

			return sr;
		}
	}
}