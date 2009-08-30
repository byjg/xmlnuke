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
using com.xmlnuke.engine;
using com.xmlnuke.international;
using com.xmlnuke.admin;
using com.xmlnuke.classes;
using com.xmlnuke.module;
using com.xmlnuke.processor;
using com.xmlnuke.anydataset;


namespace com.xmlnuke.admin
{

	public class UsersGroupsActions
	{
		public const string Create = "new";
		public const string CreateConfirm = "newconfirm";
		public const string Delete = "delete";
		public const string Edit = "edit";
		public const string EditConfirm = "editconfirm";
	}


	public class ManageUsersGroups : NewBaseAdminModule
	{
		protected XmlnukeManageUrl url;
		protected IUsersBase user;
		protected LanguageCollection myWords;

		public ManageUsersGroups()
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

		override public void Setup(XMLFilenameProcessor xmlModuleName, Context context, object customArgs)
		{
			base.Setup(xmlModuleName, context, customArgs);
			this.url = new XmlnukeManageUrl(URLTYPE.MODULE, this._xmlModuleName.ToString());
			this.user = this.getUsersDatabase();
		}

		override public IXmlnukeDocument CreatePage()
		{
			this.myWords = this.WordCollection();
			base.CreatePage(); // Doesnt necessary get PX, because PX is protected!
			this.setTitlePage(this.myWords.Value("TITLE"));
			this.setHelp(this.myWords.Value("DESCRIPTION"));
			this.addMenuOption(this.myWords.Value("CREATE_ROLE"), this.url.getUrl());
			switch (this._action)
			{
				case UsersGroupsActions.Create:
					{
						this.actionCreate();
						break;
					}
				case UsersGroupsActions.CreateConfirm:
					{
						this.actionSave();
						break;
					}
				case UsersGroupsActions.Delete:
					{
						this.actionDelete();
						break;
					}
				case UsersGroupsActions.Edit:
					{
						this.actionEdit();
						break;
					}
				case UsersGroupsActions.EditConfirm:
					{
						this.actionEditSave();
						break;
					}
				default:
					{
						this.actionList();
						break;
					}
			}

			return this.defaultXmlnukeDocument.generatePage();
		}

		protected void actionCreate()
		{
			this._mainBlock = new XmlBlockCollection(this.myWords.Value("BLOCK_TITLE_NEW"), BlockPosition.Center);
			XmlParagraphCollection para = new XmlParagraphCollection();
			this.url.addParam("action", UsersGroupsActions.CreateConfirm);
			XmlFormCollection form = new XmlFormCollection(this._context, this.url.getUrl(), "");
			XmlInputTextBox textbox = new XmlInputTextBox(this.myWords.Value("FORM_NAME"), "textbox_role", "");

			NameValueCollection sitesArray = new NameValueCollection();
			sitesArray["_all"] = this.myWords.Value("FORM_ALLSITES");
			sitesArray[this._context.Site] = this._context.Site;
			XmlEasyList select = new XmlEasyList(EasyListType.SELECTLIST, "select_sites", this.myWords.Value("FORM_SELECTSITE"), sitesArray, "_all");
			XmlInputButtons button = new XmlInputButtons();
			button.addSubmit("OK", "");
			form.addXmlnukeObject(select);
			form.addXmlnukeObject(textbox);
			form.addXmlnukeObject(button);
			para.addXmlnukeObject(form);
			this._mainBlock.addXmlnukeObject(para);
			this.AddListLink(this._mainBlock);
			this.defaultXmlnukeDocument.addXmlnukeObject(this._mainBlock);
		}
		protected void actionEdit()
		{
			string selectedRole = this._context.ContextValue("valueid");
			string selectedSite = this._context.ContextValue("editsite");
			IIterator it = this.user.getRolesIterator(selectedSite, selectedRole);
			SingleRow sr = it.moveNext();
			selectedSite = sr.getField(this.user.getRolesTable().Site);
			this._mainBlock = new XmlBlockCollection(this.myWords.Value("BLOCK_TITLE_EDIT"), BlockPosition.Center);
			XmlParagraphCollection para = new XmlParagraphCollection();
			this.url.addParam("action", UsersGroupsActions.EditConfirm);
			XmlFormCollection form = new XmlFormCollection(this._context, this.url.getUrl(), "");
			form.addXmlnukeObject(new XmlInputHidden("valueid", selectedRole));
			form.addXmlnukeObject(new XmlInputHidden("editsite", selectedSite));
			XmlInputTextBox textbox = new XmlInputTextBox(this.myWords.Value("FORM_NAME"), "textbox_role", selectedRole);
			XmlInputButtons button = new XmlInputButtons();
			button.addSubmit("OK", "");
			form.addXmlnukeObject(new XmlInputLabelField(this.myWords.Value("FORM_FROMSITE"), (selectedSite == "_all" ? this.myWords.Value("TEXT_ALLSITES") : selectedSite)));
			form.addXmlnukeObject(textbox);
			form.addXmlnukeObject(button);
			para.addXmlnukeObject(form);
			this._mainBlock.addXmlnukeObject(para);
			this.AddListLink(this._mainBlock);
			this.defaultXmlnukeDocument.addXmlnukeObject(this._mainBlock);
		}
		protected void actionEditSave()
		{
			this._mainBlock = new XmlBlockCollection(this.myWords.Value("BLOCK_TITLE_EDIT"), BlockPosition.Center);
			XmlParagraphCollection para = new XmlParagraphCollection();
			string selectedRole = this._context.ContextValue("valueid");
			string selectedSite = this._context.ContextValue("editsite");
			string newRole = this._context.ContextValue("textbox_role").ToUpper();
			try
			{
				this.user.editRolePublic(selectedSite, selectedRole, newRole);
				para.addXmlnukeObject(new XmlnukeText(this.myWords.Value("MSG_EDITED"), true));
			}
			catch
			{
				para.addXmlnukeObject(new XmlnukeText(this.myWords.Value("MSG_ALREADYEXISTS"), true));
			}

			this._mainBlock.addXmlnukeObject(para);
			this.AddListLink(this._mainBlock);
			this.defaultXmlnukeDocument.addXmlnukeObject(this._mainBlock);
		}
		protected void actionDelete()
		{
			this._mainBlock = new XmlBlockCollection(this.myWords.Value("BLOCK_TITLE_DELETE"), BlockPosition.Center);
			XmlParagraphCollection para = new XmlParagraphCollection();
			string selectedRole = this._context.ContextValue("valueid");
			string selectedSite = this._context.ContextValue("editsite");
			IIterator it = this.user.getRolesIterator(selectedSite, selectedRole);
			SingleRow sr = it.moveNext();
			selectedSite = sr.getField(this.user.getRolesTable().Site);
			this.user.editRolePublic(selectedSite, selectedRole);
			para.addXmlnukeObject(new XmlnukeText(this.myWords.Value("MSG_DELETED"), true));
			this._mainBlock.addXmlnukeObject(para);
			this.AddListLink(this._mainBlock);
			this.defaultXmlnukeDocument.addXmlnukeObject(this._mainBlock);
		}
		protected void actionSave()
		{
			this._mainBlock = new XmlBlockCollection(this.myWords.Value("BLOCK_TITLE_NEW"), BlockPosition.Center);
			XmlParagraphCollection para = new XmlParagraphCollection();
			string newRole = this._context.ContextValue("textbox_role").ToString().ToString();

			try
			{
				this.user.addRolePublic(this._context.ContextValue("select_sites"), newRole);
				para.addXmlnukeObject(new XmlnukeText(this.myWords.Value("MSG_CREATE"), true));
			}
			catch
			{
				para.addXmlnukeObject(new XmlnukeText(this.myWords.Value("MSG_ALREADYEXISTS"), true));
			}
			this._mainBlock.addXmlnukeObject(para);
			this.AddListLink(this._mainBlock);
			this.defaultXmlnukeDocument.addXmlnukeObject(this._mainBlock);
		}
		protected void actionList()
		{
			this._mainBlock = new XmlBlockCollection(this.myWords.Value("BLOCK_TITLE_LIST"), BlockPosition.Center);

			XmlParagraphCollection para = new XmlParagraphCollection();
			this._mainBlock.addXmlnukeObject(para);

			this.AddEditListToSite(this._mainBlock, this._context.Site, this.getRolesFromSite(this._context.Site));
			this.url.addParam("action", UsersGroupsActions.Create);
			this.url.addParam("site", this._context.Site);
			this.defaultXmlnukeDocument.addXmlnukeObject(this._mainBlock);
		}

		/**
		 * Get all rules from a site
		 *
		 * @param string site
		 * @return AnyDataSet
		 */
		protected AnyDataSet getRolesFromSite(string site)
		{
			AnyDataSet newDataSet = new AnyDataSet();
			IIterator it = this.user.getRolesIterator(site);
			while (it.hasNext())
			{
				SingleRow sr = it.moveNext();
				string[] dataArray = sr.getFieldArray(this.user.getRolesTable().Role);
				foreach (string roles in dataArray)
				{
					string siteName = sr.getField(this.user.getRolesTable().Site);
					if (siteName == "_all")
					{
						siteName = this.myWords.Value("TEXT_ALLSITES");
					}
					newDataSet.appendRow();
					newDataSet.addField(this.user.getRolesTable().Site, siteName);
					newDataSet.addField(this.user.getRolesTable().Role, roles);
				}
			}
			return newDataSet;
		}

		/**
		 * Add a go back link to list roles
		 *
		 * @param XmlBlockCollection this._mainBlock
		 */
		protected void AddListLink(XmlBlockCollection block)
		{
			XmlParagraphCollection para = new XmlParagraphCollection();
			this._mainBlock.addXmlnukeObject(para);
			//this.url.addParam("action", UsersGroupsActions.Listing);
			XmlAnchorCollection link = new XmlAnchorCollection("admin:manageusersgroups", "");
			link.addXmlnukeObject(new XmlnukeText(this.myWords.Value("LINK_LISTROLES")));
			para.addXmlnukeObject(link);
		}
		/**
		 * Add EditList to site
		 *
		 * @param XmlBlockCollection this._mainBlock
		 * @param string site
		 * @param AnyDataSet dataset
		 */
		protected void AddEditListToSite(XmlBlockCollection block, string site, AnyDataSet dataset)
		{
			XmlParagraphCollection para = new XmlParagraphCollection();
			this._mainBlock.addXmlnukeObject(para);
			this.url.addParam("editsite", site);
			this.url.addParam("site", this._context.Site);

			XmlEditList editList = new XmlEditList(this._context, this.myWords.Value("EDITLIST_TITLE", new string[] { site }), this.url.getUrl(), true, false, true, true);
			editList.setDataSource(dataset.getIterator());
			//editList.setPageSize(10, 0);
			//editList.setEnablePage(true);

			EditListField listField = new EditListField(true);
			listField.editlistName = "";
			listField.fieldData = "role";
			listField.fieldType = EditListFieldType.TEXT;
			editList.addEditListField(listField);

			listField = new EditListField(true);
			listField.editlistName = this.myWords.Value("EDITLIST_SITES");
			listField.fieldData = this.user.getRolesTable().Site;
			listField.fieldType = EditListFieldType.TEXT;
			editList.addEditListField(listField);

			listField = new EditListField(true);
			listField.editlistName = this.myWords.Value("EDITLIST_ROLES");
			listField.fieldData = this.user.getRolesTable().Role;
			listField.fieldType = EditListFieldType.TEXT;
			editList.addEditListField(listField);

			para.addXmlnukeObject(editList);
		}
	}

}