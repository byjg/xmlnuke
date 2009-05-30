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
	public class ManageGroup : BaseAdminModule
	{
		public ManageGroup()
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

			this.setHelp(myWords.Value("DESCRIPTION"));
			//this.addMenuOption("OK", "admin:ManageGroup?action=aqui");
			this.setTitlePage(myWords.Value("TITLE"));

			// Strings
			string action = this._action.ToLower();
			string id = this._context.ContextValue("id");
			string title = this._context.ContextValue("title");
			string keyword = this._context.ContextValue("keyword");

			// XmlNodes
			XmlNode block = px.addBlockCenter(myWords.Value("WORKINGAREA"));
			this.addMenuOption(myWords.Value("TXT_BACK"), "admin:ListXML?onlygroup=true");
			XmlNode paragraph;
			XmlNode form;
			XmlNode boxButton;

			// Open Index File
			processor.XMLFilenameProcessor indexFile = new processor.XMLFilenameProcessor("index", this._context);
			XmlDocument index = _context.getXMLDataBase().getDocument(indexFile.FullQualifiedName());

			// Delete a Group Node
			if (action == "delete")
			{
				paragraph = px.addParagraph(block);
				px.addHref(paragraph, "admin:ManageGroup?id=" + id + "&action=confirmdelete", myWords.Value("CONFIRMDELETE", new string[] { id }));
				return px;
			}

			if (action == "confirmdelete")
			{
				XmlNode editNode = index.SelectSingleNode("xmlindex");
				XmlNode delNode = editNode.SelectSingleNode("group[id='" + id + "']");
				editNode.RemoveChild(delNode);
				paragraph = px.addParagraph(block);
				_context.getXMLDataBase().saveDocument(indexFile.FullQualifiedName(), index);

				px.addBold(paragraph, myWords.Value("DELETED"));
				util.FileUtil.DeleteFilesFromPath(this._cacheFile);
				return px;
			}

			// Edit or Insert a new Group!
			if (title != "")
			{
				if (this._context.ContextValue("new") != "")
				{
					XmlNode editNode = index.SelectSingleNode("xmlindex");
					XmlNode newNode = util.XmlUtil.CreateChild(editNode, "group", "");
					util.XmlUtil.CreateChild(newNode, "id", id);
					util.XmlUtil.CreateChild(newNode, "title", title);
					util.XmlUtil.CreateChild(newNode, "keyword", keyword);
				}
				else
				{
					XmlNode editNode = index.SelectSingleNode("xmlindex/group[id='" + id + "']");
					editNode.SelectSingleNode("title").InnerText = title;
					editNode.SelectSingleNode("keyword").InnerText = keyword;
				}
				paragraph = px.addParagraph(block);
				_context.getXMLDataBase().saveDocument(indexFile.FullQualifiedName(), index);
				util.FileUtil.DeleteFilesFromPath(this._cacheFile);
				px.addBold(paragraph, myWords.Value("SAVED", new string[] { id }));
			}

			// Get new Index from disk
			string idnew = "true";
			index = _context.getXMLDataBase().getDocument(indexFile.FullQualifiedName());
			XmlNode edit = index.SelectSingleNode("xmlindex/group[id='" + id + "']");
			if (edit != null)
			{
				idnew = "";
				title = edit.SelectSingleNode("title").InnerText;
				keyword = edit.SelectSingleNode("keyword").InnerText;
			}

			// Show form to Edit/Insert
			paragraph = px.addParagraph(block);
			XmlNode table = px.addTable(paragraph);
			XmlNode row = px.addTableRow(table);
			XmlNode col = px.addTableColumn(row);
			form = px.addForm(col, "admin:ManageGroup", "");
			px.addHidden(form, "new", idnew);
			if (idnew == "")
			{
				px.addCaption(form, myWords.Value("EDITINGID", new string[] { id }));
				px.addHidden(form, "id", id);
			}
			else
			{
				px.addTextBox(form, myWords.Value("GROUPID"), "id", id, 20);
			}
			px.addTextBox(form, myWords.Value("TITLEBOX"), "title", title, 20);
			px.addTextBox(form, myWords.Value("KEYWORDBOX"), "keyword", keyword, 20);
			boxButton = px.addBoxButtons(form);
			px.addSubmit(boxButton, "", myWords.Value("TXT_SAVE"));

			return px;
		}

	}
}