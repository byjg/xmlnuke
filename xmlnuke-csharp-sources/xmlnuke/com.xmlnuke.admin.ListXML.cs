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
using com.xmlnuke;
using com.xmlnuke.classes;
using com.xmlnuke.processor;
using com.xmlnuke.module;

namespace com.xmlnuke.admin
{
	/// <summary>
	/// Summary description for com.
	/// </summary>
	public class ListXML : NewBaseAdminModule
	{
		public ListXML()
		{
		}

		override public bool useCache()
		{
			return false;
		}

		override public AccessLevel getAccessLevel()
		{
			return AccessLevel.CurrentSiteAndRole;
		}

		override public string[] getRole()
		{
			return new string[] { "MANAGER", "EDITOR" };
		}

		//Returns: classes.PageXml
		override public IXmlnukeDocument CreatePage()
		{
			base.CreatePage();

			bool onlyGroup = (this._context.ContextValue("onlygroup") != "");
			string urlXml = "admin:ManageXML";
			string urlGrp = "admin:ManageGroup";

			com.xmlnuke.international.LanguageCollection myWords = this.WordCollection();
			this.setTitlePage(myWords.Value("TITLE"));
			this.setHelp(myWords.Value("DESCRIPTION"));

			if (!onlyGroup)
			{
				this.addMenuOption(myWords.Value("EDITALLXML"), urlXml + "?id=_all");
				this.addMenuOption(myWords.Value("NEWXML"), urlXml);
			}
			this.addMenuOption(myWords.Value("NEWGROUP"), urlGrp);

			// Open Index File
			XMLFilenameProcessor indexFile = new XMLFilenameProcessor("index", this._context);
			//XmlDocument 

			XmlDocument index = this._context.getXMLDataBase().getDocument(indexFile.FullQualifiedName(), null);

			XmlNodeList groupList = index.SelectNodes("xmlindex/group");
			XmlTableCollection table = new XmlTableCollection();
			foreach (XmlNode node in groupList)
			{

				string groupText = node.SelectSingleNode("title").InnerText;
				string groupId = node.SelectSingleNode("id").InnerText;

				XmlTableRowCollection row = new XmlTableRowCollection();

				XmlTableColumnCollection col = new XmlTableColumnCollection();
				XmlAnchorCollection anchor = new XmlAnchorCollection(urlGrp + "?id=" + groupId, "");
				anchor.addXmlnukeObject(new XmlnukeText(myWords.Value("TXT_EDIT"), true, false, false));
				col.addXmlnukeObject(anchor);
				row.addXmlnukeObject(col);

				col = new XmlTableColumnCollection();
				anchor = new XmlAnchorCollection(urlGrp + "?id=" + groupId + "&action=delete", "");
				anchor.addXmlnukeObject(new XmlnukeText(myWords.Value("TXT_DELETE"), true, false, false));
				col.addXmlnukeObject(anchor);
				row.addXmlnukeObject(col);

				col = new XmlTableColumnCollection();
				col.addXmlnukeObject(new XmlnukeText(groupText, true, false, false));
				row.addXmlnukeObject(col);
				table.addXmlnukeObject(row);

				if (!onlyGroup)
				{
					XmlNodeList fileList = index.SelectNodes("xmlindex/group[id='" + groupId + "']/page");
					foreach (XmlNode nodeFile in fileList)
					{
						string fileText = nodeFile.SelectSingleNode("title").InnerText;
						string fileId = nodeFile.SelectSingleNode("id").InnerText;
						string fileAbstract = nodeFile.SelectSingleNode("summary").InnerText;

						row = new XmlTableRowCollection();

						col = new XmlTableColumnCollection();
						anchor = new XmlAnchorCollection(urlXml + "?id=" + fileId, "");
						anchor.addXmlnukeObject(new XmlnukeText(myWords.Value("TXT_EDIT")));
						col.addXmlnukeObject(anchor);
						row.addXmlnukeObject(col);

						col = new XmlTableColumnCollection();
						anchor = new XmlAnchorCollection(urlXml + "?id=" + fileId + "&action=delete", "");
						anchor.addXmlnukeObject(new XmlnukeText(myWords.Value("TXT_DELETE")));
						col.addXmlnukeObject(anchor);
						row.addXmlnukeObject(col);

						col = new XmlTableColumnCollection();
						col.addXmlnukeObject(new XmlnukeText(fileText));
						col.addXmlnukeObject(new XmlnukeBreakLine());
						col.addXmlnukeObject(new XmlnukeText(fileAbstract, false, true, false));
						row.addXmlnukeObject(col);
						table.addXmlnukeObject(row);

					}
				}

			}

			XmlBlockCollection block = new XmlBlockCollection(myWords.Value("WORKINGAREA"), BlockPosition.Center);

			XmlParagraphCollection paragraph = new XmlParagraphCollection();
			paragraph.addXmlnukeObject(table);
			block.addXmlnukeObject(paragraph);
			this.defaultXmlnukeDocument.addXmlnukeObject(block);

			return this.defaultXmlnukeDocument.generatePage();
		}

	}
}