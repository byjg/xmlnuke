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
	public class ManageXSL : BaseAdminModule
	{
		public ManageXSL()
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
			return new string[] { "MANAGER", "DESIGNER" };
		}

		override public classes.IXmlnukeDocument CreatePage()
		{
			base.CreatePage(); // Doesnt necessary get PX, because PX is protected!

			bool deleteMode = false;
			com.xmlnuke.international.LanguageCollection myWords = this.WordCollection();

			string action = this._action.ToLower();
			string id = this._context.ContextValue("id");
			string contents = "";

			this.setTitlePage(myWords.Value("TITLE"));
			this.setHelp(myWords.Value("DESCRIPTION"));

			XmlNode block = px.addBlockCenter(myWords.Value("WORKINGAREA"));
			XmlNode paragraph;
			XmlNode form;
			XmlNode boxButton;

			processor.XSLFilenameProcessor xslFile;

			// --------------------------------------
			// CHECK ACTION
			// --------------------------------------
			if ((action == "edit") || (action == "new"))
			{
				contents = this._context.ContextValue("contents");
				try
				{
					XmlDocument xsl = util.XmlUtil.CreateXmlDocumentFromStr(contents);
					xslFile = new processor.XSLFilenameProcessor(id, this._context);
					util.FileUtil.QuickFileWrite(xslFile.FullQualifiedNameAndPath(), util.XmlUtil.GetFormattedDocument(xsl));
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

			if (action == "delete")
			{
				paragraph = px.addParagraph(block);
				px.addHref(paragraph, "admin:ManageXSL?id=" + _context.ContextValue("id") + "&action=confirmdelete", myWords.Value("CONFIRMDELETE", new string[] { _context.ContextValue("id") }));
				deleteMode = true;
			}

			if (action == "confirmdelete")
			{
				paragraph = px.addParagraph(block);
				util.FileUtil.DeleteFile(new processor.XSLFilenameProcessor(_context.ContextValue("id"), this._context));
				px.addBold(paragraph, myWords.Value("DELETED"));
				deleteMode = true;
			}

			// --------------------------------------
			// EDIT XSL PAGE
			// --------------------------------------
			// If doesnt have an ID, list all pages or add new!
			if (id == "")
			{
				XmlNode list;
				XmlNode optlist;
				xslFile = new processor.XSLFilenameProcessor("page", this._context);
				string[] templates = util.FileUtil.RetrieveFilesFromFolder(xslFile.PathSuggested(), "*." + _context.Language.Name.ToLower() + xslFile.Extension());
				paragraph = px.addParagraph(block);
				px.addText(paragraph, myWords.Value("SELECTPAGE"));
				list = px.addUnorderedList(paragraph);
				foreach (string key in templates)
				{
					optlist = px.addOptionList(list);
					string xslKey = key.Substring(xslFile.PathSuggested().Length);
					xslKey = processor.FilenameProcessor.StripLanguageInfo(xslKey);
					px.addHref(optlist, "admin:ManageXSL?id=" + xslKey, xslKey);
					px.addText(optlist, " [");
					px.addHref(optlist, "admin:ManageXSL?id=" + xslKey + "&action=delete", myWords.Value("TXT_DELETE"));
					px.addText(optlist, "]");
				}
				action = "new";
			}
			else
			{
				this.addMenuOption("Preview This Page", "engine:xmlnuke?site=[param:site]&xml=home&xsl=" + id + "&lang=[param:lang]");
				this.addMenuOption("New Page", "admin:ManageXSL");
				System.Collections.Specialized.NameValueCollection langAvail = _context.LanguagesAvailable();
				foreach (string key in langAvail)
				{
					if (key != this._context.Language.Name.ToLower())
					{
						this.addMenuOption(myWords.Value("EDITXSLMENU", new string[] { langAvail[key] }), "admin:ManageXSL?id=" + id + "&lang=" + key);
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
				form = px.addForm(col, "admin:ManageXSL", "");
				if (action == "new")
				{
					px.addTextBox(form, myWords.Value("XSLBOX"), "id", "", 20);
				}
				else
				{
					px.addLabelField(form, myWords.Value("XSLBOX"), id);
					px.addHidden(form, "id", id);
					xslFile = new processor.XSLFilenameProcessor(id, this._context);
					if (util.FileUtil.Exists(xslFile.FullQualifiedNameAndPath()))
					{
						XmlDocument xsl = util.XmlUtil.CreateXmlDocument(xslFile.FullQualifiedNameAndPath());
						contents = util.XmlUtil.GetFormattedDocument(xsl).Replace("&amp;", "&");
					}
				}
				px.addLabelField(form, myWords.Value("LANGUAGEBOX"), this._context.Language.Name.ToLower());
				px.addMemo(form, myWords.Value("LABEL_CONTENT"), "contents", contents, 80, 30, "soft");
				px.addHidden(form, "action", action);
				boxButton = px.addBoxButtons(form);
				px.addSubmit(boxButton, "", myWords.Value("TXT_SAVE"));
			}

			return px;
		}

	}
}