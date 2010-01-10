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
using com.xmlnuke.module;

namespace com.xmlnuke.admin
{
	/// <summary>
	/// Summary description for com.
	/// </summary>
	public class ManageXML : BaseAdminModule
	{
		public ManageXML()
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

		override public classes.IXmlnukeDocument CreatePage()
		{
			base.CreatePage(); // Doesnt necessary get PX, because PX is protected!

			com.xmlnuke.international.LanguageCollection myWords = this.WordCollection();

			bool deleteMode = false;

			string action = this._action.ToLower();
			string id = this._context.ContextValue("id");
			string group = this._context.ContextValue("group");
			string contents = "";

			string titleIndex = this._context.ContextValue("titleIndex");
			string summaryIndex = this._context.ContextValue("summaryIndex");
			string groupKeyword = this._context.ContextValue("groupKeyword");

			this.setTitlePage(myWords.Value("TITLE"));
			this.setHelp(myWords.Value("DESCRIPTION"));

			XmlNode block = px.addBlockCenter(myWords.Value("WORKINGAREA"));

            this.addMenuOption(myWords.Value("TXT_BACK"), "admin:ListXML");
            
            XmlNode paragraph;
			XmlNode form;
			XmlNode boxButton;
			XmlNode editNode; // (For Index)

			processor.XMLFilenameProcessor xmlFile;

			// Open Index File
			processor.XMLFilenameProcessor indexFile = new processor.XMLFilenameProcessor("index", this._context);
			XmlDocument index = this._context.getXMLDataBase().getDocument(indexFile.FullQualifiedName());

			// --------------------------------------
			// CHECK ACTION
			// --------------------------------------
			if ((action == "edit") || (action == "new"))
			{
				contents = this._context.ContextValue("contents");
				try
				{
					string title = "";
					string summary = "";
					XmlNode node;

					// Get edited XML and update info about INDEX.
					XmlDocument xml = util.XmlUtil.CreateXmlDocumentFromStr(contents);
					node = xml.SelectSingleNode("/page/meta/title");
					if (node != null)
					{
						title = node.InnerText;
					}
					node = xml.SelectSingleNode("/page/meta/abstract");
					if (node != null)
					{
						summary = node.InnerText;
					}
					node = xml.SelectSingleNode("/page/meta/modified");
					if (node != null)
					{
						node.InnerText = System.DateTime.Now.ToString();
					}
					node = xml.SelectSingleNode("/page/meta/groupkeyword");
					if (node != null)
					{
						node.InnerText = groupKeyword;
					}

					if (id != "_all")
					{
						if (action == "edit")
						{
							editNode = index.SelectSingleNode("xmlindex/group[id='" + group + "']/page[id='" + id + "']");
							if (titleIndex == "")
							{
								titleIndex = title;
							}
							editNode.SelectSingleNode("title").InnerText = titleIndex;

							if (summaryIndex == "")
							{
								summaryIndex = summary;
							}
							editNode.SelectSingleNode("summary").InnerText = summaryIndex;
						}
						else
						{
							editNode = index.SelectSingleNode("xmlindex/group[id='" + group + "']");
							XmlNode newNode = util.XmlUtil.CreateChild(editNode, "page", "");
							util.XmlUtil.CreateChild(newNode, "id", id);
							util.XmlUtil.CreateChild(newNode, "title", title);
							util.XmlUtil.CreateChild(newNode, "summary", summary);
							titleIndex = title;
							summaryIndex = summary;
						}
					}

					xmlFile = new processor.XMLFilenameProcessor(id, this._context);
					this._context.getXMLDataBase().saveDocument(xmlFile.FullQualifiedName(), xml);
					this._context.getXMLDataBase().saveDocument(indexFile.FullQualifiedName(), index);
					this._context.getXMLDataBase().saveIndex();
					paragraph = px.addParagraph(block);
					util.FileUtil.DeleteFilesFromPath(this._cacheFile);
					util.FileUtil.DeleteFilesFromPath(new processor.XSLCacheFilenameProcessor("", this._context));
					px.addBold(paragraph, myWords.Value("SAVED"));
				}
				catch (XmlException ex)
				{
					paragraph = px.addParagraph(block);
					px.AddErrorMessage(paragraph, contents, ex);
				}
			}

			// Get the group from the Index and Update Edit Fields
			editNode = index.SelectSingleNode("xmlindex/group[page[id='" + id + "']]/id");
			if (editNode != null)
			{
				group = editNode.InnerText;
				editNode = index.SelectSingleNode("xmlindex/group[id='" + group + "']/page[id='" + id + "']");
				titleIndex = editNode.SelectSingleNode("title").InnerText;
				summaryIndex = editNode.SelectSingleNode("summary").InnerText;
			}

			if (action == "delete")
			{
				paragraph = px.addParagraph(block);
				px.addHref(paragraph, "admin:ManageXML?id=" + _context.ContextValue("id") + "&action=confirmdelete", myWords.Value("CONFIRMDELETE", new string[] { _context.ContextValue("id") }));
				deleteMode = true;
			}

			if (action == "confirmdelete")
			{
				paragraph = px.addParagraph(block);
				editNode = index.SelectSingleNode("xmlindex/group[id='" + group + "']");
				XmlNode delNode = editNode.SelectSingleNode("page[id='" + id + "']");
				if (delNode != null)
				{
					editNode.RemoveChild(delNode);
				}

				this._context.getXMLDataBase().saveDocument(indexFile.FullQualifiedName(), index);
				//util.FileUtil.DeleteFile(new processor.XMLFilenameProcessor(_context.ContextValue("id"), this._context));
				this._context.getXMLDataBase().saveIndex();
				px.addBold(paragraph, myWords.Value("DELETED"));
				deleteMode = true;
			}

			// --------------------------------------
			// EDIT XML PAGE
			// --------------------------------------
			// If doesnt have an ID, list all pages or add new!
			if (id == "")
			{
				action = "new";
			}
			else
			{
				this.addMenuOption(myWords.Value("PREVIEWMENU"), "engine:xmlnuke?site=[param:site]&xml=" + id + "&xsl=page&lang=[param:lang]", "preview");
				this.addMenuOption(myWords.Value("NEWXMLMENU"), "admin:ManageXML");
				System.Collections.Specialized.NameValueCollection langAvail = _context.LanguagesAvailable();
				processor.XMLFilenameProcessor processorFile = new processor.XMLFilenameProcessor(id, this._context);
				foreach (string key in langAvail)
				{
					if (key != this._context.Language.Name.ToLower())
					{
						com.xmlnuke.db.XmlNukeDB repositorio = new com.xmlnuke.db.XmlNukeDB(this._context.XmlHashedDir(), this._context.XmlPath, key);
						string fileToCheck = processorFile.FullName(id, "", key) + processorFile.Extension();
						if (repositorio.existsDocument(fileToCheck))
						{
							this.addMenuOption(myWords.Value("EDITXMLMENU", new string[] { langAvail[key] }), "admin:ManageXML?id=" + id + "&lang=" + key);
						}
						else
						{
							this.addMenuOption(myWords.Value("CREATEXMLMENU", new string[] { langAvail[key] }), "admin:ManageXML?id=" + id + "&lang=" + key);
						}
					}
				}
				action = "edit";
			}


			// Show form to Edit/Insert
			if (!deleteMode)
			{
				paragraph = px.addParagraph(block);
				XmlNode table = px.addTable(paragraph);
				XmlNode row = px.addTableRow(table);
				XmlNode col = px.addTableColumn(row);
				form = px.addForm(col, "admin:ManageXML", "");

				bool xmlExist = true;
				if (id != "")
				{
					processor.XMLFilenameProcessor xmlTestExist = new processor.XMLFilenameProcessor(id, this._context);
					xmlExist = _context.getXMLDataBase().existsDocument(xmlTestExist.FullQualifiedName());
				}

				bool canUseNew = ((action != "new") && !xmlExist);

				if ((action == "new") || canUseNew)
				{
					action = "new"; // This is necessary, because user can Create a predefined ID...
					if (!canUseNew || (id == ""))
					{
						px.addTextBox(form, myWords.Value("XMLBOX"), "id", "", 20);
					}
					else
					{
						px.addLabelField(form, myWords.Value("XMLBOX"), id);
						px.addHidden(form, "id", id);
					}
					px.addLabelField(form, myWords.Value("LANGUAGEBOX"), this._context.Language.Name.ToLower());
					contents =
						"<page>\n" +
						"  <meta>\n" +
						"    <title>" + myWords.Value("TITLEINXML") + "</title>\n" +
						"    <abstract>" + myWords.Value("ABSTRACTINXML") + "</abstract>\n" +
						"    <created>" + System.DateTime.Now.ToString() + "</created>\n" +
						"    <modified/>\n" +
						"    <keyword>xmlnuke</keyword>\n" +
						"    <groupkeyword>all</groupkeyword>\n" +
						"  </meta>\n" +
						"  <blockcenter>\n" +
						"    <title>" + myWords.Value("BLOCKTITLEINXML") + "</title>\n" +
						"    <body>\n" +
						"      <p>" + myWords.Value("PARAGRAPHINXML") + "</p>\n" +
						"    </body>\n" +
						"  </blockcenter>\n" +
						"</page>\n";
				}
				else
				{
					px.addLabelField(form, myWords.Value("XMLBOX"), id);
					px.addLabelField(form, myWords.Value("LANGUAGEBOX"), this._context.Language.Name.ToLower());
					px.addHidden(form, "id", id);
					xmlFile = new processor.XMLFilenameProcessor(id, this._context);
					XmlDocument xml = this._context.getXMLDataBase().getDocument(xmlFile.FullQualifiedName());
					contents = util.XmlUtil.GetFormattedDocument(xml).Replace("&amp;", "&");
					editNode = xml.SelectSingleNode("/page/meta/groupkeyword");
					if (editNode != null)
					{
						groupKeyword = editNode.InnerText;
					}
				}

				if (id != "_all")
				{
					px.addCaption(form, myWords.Value("SITEMAPINFO"));
					px.addTextBox(form, myWords.Value("INDEXBOX"), "titleIndex", titleIndex, 60);
					XmlNode selectNode = px.addSelect(form, myWords.Value("LISTEDBOX"), "group");
					px.addTextBox(form, myWords.Value("INDEXSUMMARYBOX"), "summaryIndex", summaryIndex, 60);

					px.addCaption(form, myWords.Value("PAGEINFO"));
					XmlNode selectPageNode = px.addSelect(form, myWords.Value("SHOWMENUBOX"), "groupKeyword");
					px.addOption(selectPageNode, myWords.Value("NOTLISTEDOPTION"), "-");

					XmlNodeList groupList = index.SelectNodes("xmlindex/group");
					foreach (XmlNode node in groupList)
					{
						px.addOption(selectNode, node.SelectSingleNode("title").InnerText + " (" + node.SelectSingleNode("id").InnerText + ")", node.SelectSingleNode("id").InnerText, node.SelectSingleNode("id").InnerText == group);
						px.addOption(selectPageNode, node.SelectSingleNode("title").InnerText + " (" + node.SelectSingleNode("keyword").InnerText + ")", node.SelectSingleNode("keyword").InnerText, node.SelectSingleNode("keyword").InnerText == groupKeyword);
					}
				}

				px.addHidden(form, "action", action);
				px.addCaption(form, myWords.Value("XMLEDITINFO"));
				px.addMemo(form, myWords.Value("XMLCONTENTBOX"), "contents", contents, 80, 30, "soft");
				boxButton = px.addBoxButtons(form);
				px.addSubmit(boxButton, "", myWords.Value("TXT_SAVE"));
			}

			return px;
		}

	}
}