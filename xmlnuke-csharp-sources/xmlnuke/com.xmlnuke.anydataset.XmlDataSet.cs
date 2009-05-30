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

	public class XmlDataSet
	{
		private string _rowNode = null;
		private NameValueCollection _colNodes = null;
		private XmlDocument _domDocument;
		private com.xmlnuke.engine.Context _context;

		public XmlDataSet(com.xmlnuke.engine.Context context, string xmlstr, string rowNode, NameValueCollection colNode)
		{
			this.XmlDataSetInit(context, util.XmlUtil.CreateXmlDocumentFromStr(xmlstr), rowNode, colNode);
		}

		public XmlDataSet(com.xmlnuke.engine.Context context, XmlDocument xml, string rowNode, NameValueCollection colNode)
		{
			this.XmlDataSetInit(context, xml, rowNode, colNode);
		}

		protected void XmlDataSetInit(com.xmlnuke.engine.Context context, XmlDocument xml, string rowNode, NameValueCollection colNode)
		{
			this._domDocument = xml;
			this._rowNode = rowNode;
			this._colNodes = colNode;
			this._context = context;
		}

		public IIterator getIterator()
		{
			IIterator it = new XmlIterator(this._context, this._domDocument.DocumentElement.SelectNodes(this._rowNode), this._colNodes);
			return it;
		}

	}
}