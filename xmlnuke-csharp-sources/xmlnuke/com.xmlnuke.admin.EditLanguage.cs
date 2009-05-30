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
using System.Collections;
using System.Collections.Specialized;
using com.xmlnuke.module;
using com.xmlnuke.processor;

namespace com.xmlnuke.admin
{
	public class EditLanguage : BaseAdminModule
	{
		/// <summary>
		/// Default constructor
		/// </summary>
		public EditLanguage()
		{ }

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

		/// <summary>
		/// Output error message
		/// </summary>
		/// <returns>PageXml object</returns>
		override public classes.IXmlnukeDocument CreatePage()
		{
			base.CreatePage(); // Doesnt necessary get PX, because PX is protected!

			com.xmlnuke.international.LanguageCollection myWords = this.WordCollection();

			this.setHelp(myWords.Value("DESCRIPTION"));
			this.setTitlePage(myWords.Value("TITLE"));
			this.addMenuOption(myWords.Value("LANGUAGEMENU"), "admin:EditLanguage");
			//this.addMenuOption("Add Category","admin:Download?action=addcat");

			string langfile = this._context.ContextValue("langfile");
			string contents = this._context.ContextValue("contents");

			XmlNode form;
			XmlNode boxButton;
			XmlNode paragraph;

			XmlNode blockcenter = px.addBlockCenter(myWords.Value("WORKINGAREA"));
			paragraph = px.addParagraph(blockcenter);

			px.addBold(paragraph, myWords.Value("MESSAGEWORKING"));
			AnydatasetLangFilenameProcessor langDir = new AnydatasetLangFilenameProcessor("", _context);
			string[] filelist = util.FileUtil.RetrieveFilesFromFolder(langDir.PrivatePath(), "*" + langDir.Extension());
			generateList(px, paragraph, filelist, langDir);

			px.addBold(paragraph, myWords.Value("MESSAGEWORKINGSHARED"));
			filelist = util.FileUtil.RetrieveFilesFromFolder(langDir.SharedPath(), "*" + langDir.Extension());
			generateList(px, paragraph, filelist, langDir);

			blockcenter = px.addBlockCenter(myWords.Value("EDITINGAREA"));

			// --------------------------------------
			// CHECK ACTION
			// --------------------------------------
			if ((this._action == "save") || (this._action == "create"))
			{
				try
				{
					XmlDocument xmlLang = util.XmlUtil.CreateXmlDocumentFromStr(contents);
					AnydatasetLangFilenameProcessor lang = new AnydatasetLangFilenameProcessor(langfile, _context);
					util.FileUtil.QuickFileWrite(lang.FullQualifiedNameAndPath(), util.XmlUtil.GetFormattedDocument(xmlLang));
					paragraph = px.addParagraph(blockcenter);
					px.addBold(paragraph, myWords.Value("SAVED"));
				}
				catch (XmlException ex)
				{
					paragraph = px.addParagraph(blockcenter);
					px.AddErrorMessage(paragraph, contents, ex);
				}
			}


			form = px.addForm(blockcenter, "admin:EditLanguage", myWords.Value("FORMTITLE", new string[] { langfile }));
			if (this._action == "")
			{
				px.addTextBox(form, myWords.Value("LANGUAGEFILE"), "langfile", "", 20);
				px.addHidden(form, "action", "create");
				contents = "<?xml version=\"1.0\"?>\n" +
					"<anydataset>\n" +
					"	<row>\n" +
					"		<field name=\"LANGUAGE\">en-us</field>\n" +
					"	</row>\n" +
					"	<row>\n" +
					"		<field name=\"LANGUAGE\">pt-br</field>\n" +
					"	</row>\n" +
					"</anydataset>\n";
			}
			else
			{
				px.addHidden(form, "action", "save");
				px.addLabelField(form, myWords.Value("LANGUAGEFILE"), langfile);
				px.addHidden(form, "langfile", langfile);
				AnydatasetLangFilenameProcessor lang = new AnydatasetLangFilenameProcessor(langfile, _context);
				if (util.FileUtil.Exists(lang.FullQualifiedNameAndPath()))
				{
					XmlDocument langXml = util.XmlUtil.CreateXmlDocument(lang.FullQualifiedNameAndPath());
					contents = util.XmlUtil.GetFormattedDocument(langXml).Replace("&amp;", "&");
				}
			}
			px.addMemo(form, myWords.Value("LABEL_CONTENT"), "contents", contents, 80, 30, "soft");
			boxButton = px.addBoxButtons(form);
			px.addSubmit(boxButton, "", myWords.Value("TXT_SAVE"));

			return px;
		}

		private void generateList(classes.PageXml px, XmlNode paragraph, string[] filelist, FilenameProcessor proc)
		{
			XmlNode list;
			XmlNode optlist;
			list = px.addUnorderedList(paragraph);
			foreach (string file in filelist)
			{
				string name = util.FileUtil.ExtractFileName(file);
				name = proc.removeLanguage(name);
				optlist = px.addOptionList(list);
				px.addHref(optlist, "admin:EditLanguage?action=edit&langfile=" + name, name);
			}

		}

	}
}