/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
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

	public class XmlnukeFaq : XmlnukeDocumentObject
	{
		protected ArrayList _faqs = new ArrayList();
		protected ArrayList _faqsData = new ArrayList();
		protected string _title;

		/**
		*@desc Generate page, processing yours childs.
		*@param DOMNode $current
		*@return void
		*/
		public XmlnukeFaq(string title)
		{
			this._title = title;
		}

		public void addFaqItem(string title, IXmlnukeDocumentObject docobj)
		{
			this._faqs.Add(title);
			this._faqsData.Add(docobj);
		}

		public override void generateObject(XmlNode current)
		{
			XmlNode node = util.XmlUtil.CreateChild(current, "faq", "");
			util.XmlUtil.AddAttribute(node, "title", this._title);
			for (int i = 0; i < this._faqs.Count; i++)
			{
				XmlNode nodefaq = util.XmlUtil.CreateChild(node, "item", "");
				util.XmlUtil.AddAttribute(nodefaq, "question", this._faqs[i].ToString());
				((IXmlnukeDocumentObject)this._faqsData[i]).generateObject(nodefaq);
			}
		}
	}

}