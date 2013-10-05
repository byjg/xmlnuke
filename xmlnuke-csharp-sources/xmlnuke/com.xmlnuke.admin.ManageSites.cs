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

using com.xmlnuke.module;
using com.xmlnuke.anydataset;
using com.xmlnuke.classes;
using com.xmlnuke.processor;
using com.xmlnuke.util;
using com.xmlnuke.international;

namespace com.xmlnuke.admin
{
	class ManageSitesAction : ModuleAction
	{
		public const string OFFLINE = "action.OFFLINE";
		public const string Save = "save"; /* Editlist Action */
	}

	/// <summary>
	/// Summary description for com.
	/// </summary>
	public class ManageSites : NewBaseAdminModule
	{
		protected XmlBlockCollection _block;

		public ManageSites()
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
			return new string[] { "MANAGER" };
		}

		override public IXmlnukeDocument CreatePage()
		{
			base.CreatePage(); // Doesnt necessary get PX, because PX is protected!

			LanguageCollection myWords = this.WordCollection();
			this.setTitlePage(myWords.Value("TITLE"));
			this.setHelp(myWords.Value("DESCRIPTION"));

			this._block = new XmlBlockCollection(myWords.Value("WORKINGAREA"), BlockPosition.Center);
			XmlParagraphCollection paragraph = new XmlParagraphCollection();
			this._block.addXmlnukeObject(paragraph);
			this.defaultXmlnukeDocument.addXmlnukeObject(this._block);

			XmlnukeManageUrl url;
			switch (this._action)
			{
				case ManageSitesAction.OFFLINE:
					paragraph.addXmlnukeObject(new XmlnukeText(myWords.Value("NOTIMPLEMENTED")));
					break;
				case ManageSitesAction.Save:
					this.ActionCreate(paragraph);
					break;
				case ManageSitesAction.Delete:
					this.ActionDelete(paragraph);
					break;
				case ManageSitesAction.Create:
					this.ActionCreateForm(paragraph);
					break;
				case ManageSitesAction.View:
					url = new XmlnukeManageUrl(URLTYPE.ENGINE);
					url.addParam("site", this._context.ContextValue("valueid"));
					this._context.redirectUrl(url.getUrlFull(this._context));
					break;
				case ManageSitesAction.Edit:
					url = new XmlnukeManageUrl(URLTYPE.ADMIN, "admin:ManageSites");
					url.addParam("site", this._context.ContextValue("valueid"));
					this._context.redirectUrl(url.getUrlFull(this._context));
					break;
				default:
					/* Nothing to do */
					break;
			}
			this.ActionList();
			return this.defaultXmlnukeDocument.generatePage();
		}

		/**
		*@param XmlParagraphCollection paragraph
		*@return void
		*@desc Action to list sites in repository
		*/
		protected void ActionList()
		{
			LanguageCollection myWords = this.WordCollection();
			XmlParagraphCollection paragraph = new XmlParagraphCollection();
			this._block.addXmlnukeObject(paragraph);
			paragraph.addXmlnukeObject(new XmlnukeText(myWords.Value("CURRENTSITE"), true, false, false, true));
			paragraph.addXmlnukeObject(new XmlnukeText(myWords.Value("SELECTSITE"), false, false, false, true));

			XmlParagraphCollection paragraph2 = new XmlParagraphCollection();
			this._block.addXmlnukeObject(paragraph2);
			XmlnukeManageUrl url = new XmlnukeManageUrl(URLTYPE.ADMIN, "admin:ManageSites");
			url.addParam("xsl", "page");
			XmlEditList editList = new XmlEditList(this._context, myWords.Value("SITESLIST"), url.getUrl());
			editList.setDataSource(this.getSiteList());

			EditListField field = new EditListField(true);
			field.fieldData = "id";
			editList.addEditListField(field);

			field = new EditListField(true);
			field.fieldData = "name";
			field.editlistName = myWords.Value("TXT_NAME");
			editList.addEditListField(field);

			CustomButtons cb = new CustomButtons();
			cb.action = ManageSitesAction.OFFLINE;
			cb.enabled = true;
			cb.alternateText = myWords.Value("CREATEOFFLINE");
			url.addParam("action", ManageSitesAction.OFFLINE);
			cb.url = url.getUrl();
			cb.icon = "common/editlist/ic_custom.gif";
			editList.setCustomButton(cb);
			//			editList.setPageSize(10, 0);
			editList.setEnablePage(false);
			paragraph2.addXmlnukeObject(editList);
		}

		/**
		*@param XmlParagraphCollection paragraph
		*@return void
		*@desc Action to create repository
		*/
		protected void ActionCreate(XmlParagraphCollection paragraph)
		{
			LanguageCollection myWords = this.WordCollection();
			string newSiteName = this._context.ContextValue("newsite").ToLower();
			string xslTemplate = this._context.ContextValue("xsltemplate");
			string newSitePath = this._context.SiteRootPath + newSiteName;
			FileUtil.ForceDirectories(newSitePath);
			FileUtil.ForceDirectories(newSitePath + FileUtil.Slash() + "xsl");
			FileUtil.ForceDirectories(newSitePath + FileUtil.Slash() + "cache");
			FileUtil.ForceDirectories(newSitePath + FileUtil.Slash() + "offline");
			FileUtil.ForceDirectories(newSitePath + FileUtil.Slash() + "anydataset");
			FileUtil.ForceDirectories(newSitePath + FileUtil.Slash() + "lang");
			FileUtil.ForceDirectories(newSitePath + FileUtil.Slash() + "snippet");

			NameValueCollection langAvail = this._context.LanguagesAvailable();

			foreach (string key in langAvail.Keys)
			{
				ManageSites.createRepositoryForSite(this._context.XmlHashedDir(), xslTemplate, newSitePath, key, this._context);
			}
			paragraph.addXmlnukeObject(new XmlnukeText(myWords.Value("CREATED", newSiteName)));
		}
		/**
		*@param XmlParagraphCollection paragraph
		*@return void
		*@desc Action to create repository
		*/
		protected void ActionDelete(XmlParagraphCollection paragraph)
		{
			bool complete = false;
			LanguageCollection myWords = this.WordCollection();
			string SiteName = this._context.ContextValue("valueid");
			string removeSitePath = this._context.SiteRootPath + SiteName;
			try
			{
				FileUtil.ForceRemoveDirectories(removeSitePath);
				complete = true;
			}
			catch
			{
				paragraph.addXmlnukeObject(new XmlnukeText(myWords.Value("TEXT_REMOVED_FAIL_DIR")));
			}
			if (complete)
			{
				paragraph.addXmlnukeObject(new XmlnukeText(myWords.Value("TEXT_REMOVED")));
			}
		}
		/**
		*@param XmlParagraphCollection paragraph
		*@return void
		*@desc Action to create repository
		*/
		protected void ActionCreateForm(XmlParagraphCollection paragraph)
		{
			XmlnukeManageUrl url = new XmlnukeManageUrl(URLTYPE.ADMIN, "admin:ManageSites");
			url.addParam("xsl", "page");
			url.addParam("action", ManageSitesAction.Save);
			LanguageCollection myWords = this.WordCollection();
			XmlFormCollection form = new XmlFormCollection(this._context, url.getUrl(), myWords.Value("FORM_TITLE"));
			XmlInputTextBox textbox = new XmlInputTextBox(myWords.Value("FORM_NEWSITE"), "newsite", "");
			textbox.setDataType(INPUTTYPE.LOWER);
			textbox.setRequired(true);

			XSLFilenameProcessor xsl = new XSLFilenameProcessor("", this._context);
			string[] filelist = FileUtil.RetrieveFilesFromFolder(xsl.SharedPath(), "*" + xsl.Extension());
			NameValueCollection xsllist = new NameValueCollection();
			foreach (string file in filelist)
			{
				xsllist[FileUtil.ExtractFileName(file)] = FileUtil.ExtractFileName(file);
			}
			XmlEasyList easylist = new XmlEasyList(EasyListType.SELECTLIST, "xsltemplate", myWords.Value("FORM_DEFAULTXSL"), xsllist);
			easylist.setRequired(true);
			form.addXmlnukeObject(easylist);

			XmlInputButtons button = new XmlInputButtons();
			button.addSubmit(myWords.Value("TXT_CREATE"), "submit");
			form.addXmlnukeObject(textbox);
			form.addXmlnukeObject(button);
			paragraph.addXmlnukeObject(form);
		}



		protected IIterator getSiteList()
		{
			AnyDataSet sitesanydata = new AnyDataSet();
			string[] siteAvail = this._context.ExistingSites();
			foreach (string key in siteAvail)
			{
				string keySite = FileUtil.ExtractFileName(key);
				if (keySite != "CVS")
				{
					sitesanydata.appendRow();
					sitesanydata.addField("id", keySite);
					sitesanydata.addField("name", keySite.ToUpper());
				}
			}
			return sitesanydata.getIterator();
		}


		public static void createRepositoryForSite(bool hashedDir, string xslTemplate, string sitePath, string language, engine.Context context)
		{
			if (!sitePath.EndsWith(util.FileUtil.Slash()))
			{
				sitePath += util.FileUtil.Slash();
			}
			com.xmlnuke.db.XmlnukeDB repositorio = new com.xmlnuke.db.XmlnukeDB(hashedDir, sitePath + "xml", language, true);
			processor.XMLFilenameProcessor processorFile = new processor.XMLFilenameProcessor("index", context);
			string index = processorFile.FullName("index", "", language) + processorFile.Extension();
			string home = processorFile.FullName("home", "", language) + processorFile.Extension();
			string notfound = processorFile.FullName("notfound", "", language) + processorFile.Extension();
			string _all = processorFile.FullName("_all", "", language) + processorFile.Extension();
			if (!repositorio.existsDocument(index))
			{
				repositorio.saveDocument(index, util.FileUtil.QuickFileRead(context.SiteRootPath + "index.xml.template"));
				repositorio.saveDocument(home, util.FileUtil.QuickFileRead(context.SiteRootPath + "home.xml.template"));
				repositorio.saveDocument(notfound, util.FileUtil.QuickFileRead(context.SiteRootPath + "notfound.xml.template"));
				repositorio.saveDocument(_all, "<?xml version=\"1.0\"?><page/>");
			}
			else
			{
				repositorio.recreateIndex();
			}
			repositorio.saveIndex();

			processor.XSLFilenameProcessor xslFile = new processor.XSLFilenameProcessor("index", context);
			string indexXsl = sitePath + "xsl" + util.FileUtil.Slash() + xslFile.FullName("", "index", language) + xslFile.Extension();
			string pageXsl = sitePath + "xsl" + util.FileUtil.Slash() + xslFile.FullName("", "page", language) + xslFile.Extension();

			if (!util.FileUtil.Exists(indexXsl))
			{
				util.FileUtil.QuickFileWrite(indexXsl, util.FileUtil.QuickFileRead(context.SiteRootPath + "index.xsl.template"));
			}
			if (!util.FileUtil.Exists(pageXsl))
			{
				util.FileUtil.QuickFileWrite(pageXsl, util.FileUtil.QuickFileRead(xslFile.SharedPath() + xslTemplate));
			}
		}

	}
}