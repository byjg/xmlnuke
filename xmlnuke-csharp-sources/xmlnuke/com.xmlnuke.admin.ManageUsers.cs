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
using com.xmlnuke.international;
using com.xmlnuke.module;
using com.xmlnuke.admin;
using com.xmlnuke.classes;
using com.xmlnuke.anydataset;
using com.xmlnuke.processor;

namespace com.xmlnuke.admin
{
	/// <summary>
	/// Summary description for com.
	/// </summary>
	public class ManageUsers : NewBaseAdminModule
	{
		protected LanguageCollection myWords;

		protected string url = "admin:ManageUsers";

		public ManageUsers()
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

		override public classes.IXmlnukeDocument CreatePage()
		{
			base.CreatePage(); // Doesnt necessary get PX, because PX is protected!

			IUsersBase users = this.getUsersDatabase();

			//anydataset.SingleRow user;

			string action = this._action.ToLower();
			string uid = this._context.ContextValue("valueid");

			this.myWords = this.WordCollection();
			this.setTitlePage(this.myWords.Value("TITLE"));
			this.setHelp(this.myWords.Value("DESCRIPTION"));

			this.addMenuOption(this.myWords.Value("NEWUSER"), url, null);
			this.addMenuOption(this.myWords.Value("ADDROLE"), "admin:manageusersgroups", null);


			// --------------------------------------
			// CHECK ACTION
			// --------------------------------------
			bool exec = false;
			if ((action != "") && (action != "move"))
			{
				XmlParagraphCollection message = new XmlParagraphCollection();

				if (action == "newuser")
				{
					if (!users.addUser(this._context.ContextValue("name"), this._context.ContextValue("login"), this._context.ContextValue("email"), this._context.ContextValue("password")))
					{
						message.addXmlnukeObject(new XmlnukeText(this.myWords.Value("USEREXIST"), true, false, false));
					}
					else
					{
						if (this.isUserAdmin())
						{
							SingleRow user = users.getUserName(this._context.ContextValue("login"));
							user.setField(users.getUserTable().Admin, this._context.ContextValue("admin"));
							users.Save();
						}
						message.addXmlnukeObject(new XmlnukeText(this.myWords.Value("CREATED"), true, false, false));
					}
					exec = true;
				}

				if (action == "update")
				{
					SingleRow user = users.getUserId(uid);
					user.setField(users.getUserTable().Name, this._context.ContextValue("name"));
					user.setField(users.getUserTable().Email, this._context.ContextValue("email"));
					if (this.isUserAdmin())
					{
						user.setField(users.getUserTable().Admin, this._context.ContextValue("admin"));
					}
					users.Save();
					message.addXmlnukeObject(new XmlnukeText(this.myWords.Value("UPDATE"), true, false, false));
					exec = true;
				}

				if (action == "changepassword")
				{
					SingleRow user = users.getUserId(uid);
					user.setField(users.getUserTable().Password, users.getSHAPassword(this._context.ContextValue("password")));
					users.Save();
					message.addXmlnukeObject(new XmlnukeText(this.myWords.Value("CHANGEPASSWORD"), true, false, false));
					exec = true;
				}

				if (action == "delete")
				{
					if (users.removeUserName(uid))
					{
						users.Save();
						message.addXmlnukeObject(new XmlnukeText(this.myWords.Value("DELETE"), true, false, false));
						uid = "";
					}
					else
					{
						message.addXmlnukeObject(new XmlnukeText(this.myWords.Value("ERRO"), true, false, false));
					}
					exec = true;
				}

				if (action == "addsite")
				{

					users.addPropertyValueToUser(uid, this._context.ContextValue("sites"), UserProperty.Site);
					users.Save();
					message.addXmlnukeObject(new XmlnukeText(this.myWords.Value("SITEADDED"), true, false, false));
					exec = true;
				}

				if (action == "removesite")
				{
					users.removePropertyValueFromUser(uid, this._context.ContextValue("sites"), UserProperty.Site);
					users.Save();
					message.addXmlnukeObject(new XmlnukeText(this.myWords.Value("SITERMOVED"), true, false, false));
					exec = true;
				}

				if (action == "addrole")
				{
					users.addPropertyValueToUser(uid, this._context.ContextValue("role"), UserProperty.Role);
					users.Save();
					message.addXmlnukeObject(new XmlnukeText(this.myWords.Value("ROLEADDED"), true, false, false));
					exec = true;
				}

				if (action == "removerole")
				{
					users.removePropertyValueFromUser(uid, this._context.ContextValue("role"), UserProperty.Role);
					users.Save();
					message.addXmlnukeObject(new XmlnukeText(this.myWords.Value("ROLEREMOVED"), true, false, false));
					exec = true;
				}
				if (action == "addcustomvalue")
				{
					users.addPropertyValueToUser(uid, this._context.ContextValue("customvalue"), this._context.ContextValue("customfield"));
					users.Save();
					message.addXmlnukeObject(new XmlnukeText(this.myWords.Value("CUSTOMFIELDUPDATED"), true, false, false));
					exec = true;
				}
				if (action == "removecustomvalue")
				{
					users.removePropertyValueFromUser(uid, null, this._context.ContextValue("customfield"));
					users.Save();
					message.addXmlnukeObject(new XmlnukeText(this.myWords.Value("CUSTOMFIELDREMOVED"), true, false, false));
					exec = true;
				}

				if (exec)
				{
					XmlBlockCollection blockMessage = new XmlBlockCollection(this.myWords.Value("WORKINGAREA"), BlockPosition.Center);
					blockMessage.addXmlnukeObject(message);
					this.defaultXmlnukeDocument.addXmlnukeObject(blockMessage);
				}
			}


			// --------------------------------------
			// LIST USERS
			// --------------------------------------

			XmlBlockCollection block = new XmlBlockCollection(this.myWords.Value("USERS"), BlockPosition.Center);

			IIterator it;
			IteratorFilter itf = new IteratorFilter();
			if (!this.isUserAdmin())
			{
				itf.addRelation("admin", Relation.NotEqual, "yes");
			}
			if (this._context.ContextValue("pesquser") != "") 
			{
				itf.startGroup();
				itf.addRelationOr(users.getUserTable().Username, Relation.Contains, this._context.ContextValue("pesquser"));
				itf.addRelationOr(users.getUserTable().Name, Relation.Contains, this._context.ContextValue("pesquser"));
				itf.addRelationOr(users.getUserTable().Email, Relation.Contains, this._context.ContextValue("pesquser")); 
				itf.endGroup();
			}
			it = users.getIterator(itf);

			XmlFormCollection formpesq = new XmlFormCollection(this._context, this.url, this.myWords.Value("TITLEPESQUSER"));
			XmlInputTextBox textbox = new XmlInputTextBox(this.myWords.Value("PESQUSER"), "pesquser", this._context.ContextValue("pesquser"));
			textbox.setDataType(INPUTTYPE.TEXT);
			formpesq.addXmlnukeObject(textbox);
			textbox.setRequired(true);

			XmlInputButtons boxButton = new XmlInputButtons();
			boxButton.addSubmit(this.myWords.Value("GOSEARCH"), "");
			formpesq.addXmlnukeObject(boxButton);
			block.addXmlnukeObject(formpesq);

			XmlEditList editlist = new XmlEditList(this._context, this.myWords.Value("USERS"), url, true, true, true, true);
			editlist.setDataSource(it);
			editlist.setPageSize(20, 0);
			editlist.setEnablePage(true);

			EditListField field = new EditListField(true);
			field.fieldData = users.getUserTable().Id;
			editlist.addEditListField(field);

			field = new EditListField(true);
			field.fieldData = users.getUserTable().Username;
			field.editlistName = this.myWords.Value("TXT_LOGIN");
			editlist.addEditListField(field);

			field = new EditListField(true);
			field.fieldData = users.getUserTable().Name;
			field.editlistName = this.myWords.Value("TXT_NAME");
			editlist.addEditListField(field);

			field = new EditListField(true);
			field.fieldData = users.getUserTable().Email;
			field.editlistName = this.myWords.Value("TXT_EMAIL");
			editlist.addEditListField(field);

			field = new EditListField(true);
			field.fieldData = users.getUserTable().Admin;
			field.editlistName = this.myWords.Value("TITADM");
			editlist.addEditListField(field);

			block.addXmlnukeObject(editlist);
			this.defaultXmlnukeDocument.addXmlnukeObject(block);


			// --------------------------------------
			// EDIT AREA
			// --------------------------------------

			if ((action == "new") || (action == "newuser") || (action == "") || (action == "move") || (action == "delete"))
			{
				this.NewUser();
			}
			else
			{
				this.EditUser(users, uid);
			}

			return this.defaultXmlnukeDocument.generatePage();
		}

		public void NewUser()
		{
			XmlBlockCollection block = new XmlBlockCollection(this.myWords.Value("NEWUSER"), BlockPosition.Center);
			XmlFormCollection form = new XmlFormCollection(this._context, url, "");
			form.setJSValidate(true);
			form.addXmlnukeObject(new XmlInputHidden("action", "newuser"));
			form.addXmlnukeObject(new XmlInputHidden("curpage", this._context.ContextValue("curpage")));

			XmlInputTextBox textbox = new XmlInputTextBox(this.myWords.Value("LABEL_LOGIN"), "login", "");
			textbox.setDataType(INPUTTYPE.TEXT);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);

			textbox = new XmlInputTextBox(this.myWords.Value("LABEL_NAME"), "name", "");
			textbox.setDataType(INPUTTYPE.TEXT);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);

			textbox = new XmlInputTextBox(this.myWords.Value("LABEL_EMAIL"), "email", "");
			textbox.setDataType(INPUTTYPE.EMAIL);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);

			textbox = new XmlInputTextBox(this.myWords.Value("LABEL_PASSWORD"), "password", "");
			textbox.setInputTextBoxType(InputTextBoxType.PASSWORD);
			textbox.setDataType(INPUTTYPE.TEXT);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);

			XmlInputCheck check = new XmlInputCheck(this.myWords.Value("FORMADMINISTRADOR"), "admin", "yes");
			check.setReadOnly(!this.isUserAdmin());
			form.addXmlnukeObject(check);

			XmlInputButtons boxButton = new XmlInputButtons();
			boxButton.addSubmit(this.myWords.Value("TXT_CREATE"), "");
			form.addXmlnukeObject(boxButton);

			block.addXmlnukeObject(form);
			this.defaultXmlnukeDocument.addXmlnukeObject(block);
		}

		public void EditUser(IUsersBase users, string uid)
		{
			SingleRow user = users.getUserId(uid);
			XmlBlockCollection block = new XmlBlockCollection(this.myWords.Value("EDITUSER") + user.getField(users.getUserTable().Username), BlockPosition.Center);

			if (!this.isUserAdmin() && (user.getField(users.getUserTable().Admin) == "yes"))
			{
				XmlParagraphCollection p = new XmlParagraphCollection();
				p.addXmlnukeObject(new XmlnukeText(this.myWords.Value("CANNOTEDITADMIN")));
				block.addXmlnukeObject(p);
				this.defaultXmlnukeDocument.addXmlnukeObject(block);
				return;
			}

			XmlnukeTabView tabview = new XmlnukeTabView();
			block.addXmlnukeObject(tabview);

			// -------------------------------------------------------------------
			// EDITAR USURIO
			XmlFormCollection form = new XmlFormCollection(this._context, url, "");
			form.setJSValidate(true);
			form.addXmlnukeObject(new XmlInputHidden("action", "update"));
			form.addXmlnukeObject(new XmlInputHidden("curpage", this._context.ContextValue("curpage")));
			form.addXmlnukeObject(new XmlInputHidden("valueid", uid));
			XmlInputTextBox textbox = new XmlInputTextBox(this.myWords.Value("LABEL_LOGIN"), "login", user.getField(users.getUserTable().Username));
			textbox.setDataType(INPUTTYPE.TEXT);
			textbox.setRequired(true);
			textbox.setReadOnly(true);
			form.addXmlnukeObject(textbox);
			textbox = new XmlInputTextBox(this.myWords.Value("LABEL_NAME"), "name", user.getField(users.getUserTable().Name));
			textbox.setDataType(INPUTTYPE.TEXT);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);
			textbox = new XmlInputTextBox(this.myWords.Value("LABEL_EMAIL"), "email", user.getField(users.getUserTable().Email));
			textbox.setDataType(INPUTTYPE.EMAIL);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);
			form.addXmlnukeObject(new XmlInputLabelField(this.myWords.Value("LABEL_PASSWORD"), this.myWords.Value("FORMPASSWORDNOTVIEW")));
			XmlInputCheck check = new XmlInputCheck(this.myWords.Value("FORMADMINISTRADOR"), "admin", "yes");
			check.setChecked(user.getField(users.getUserTable().Admin) == "yes");
			form.addXmlnukeObject(check);
			XmlInputButtons boxButton = new XmlInputButtons();
			boxButton.addSubmit(this.myWords.Value("TXT_UPDATE"), "");
			form.addXmlnukeObject(boxButton);
			tabview.addTabItem(this.myWords.Value("TABEDITUSER"), form);

			// -------------------------------------------------------------------
			// ALTERAR SENHA
			form = new XmlFormCollection(this._context, url, "");
			form.setJSValidate(true);
			form.addXmlnukeObject(new XmlInputHidden("action", "changepassword"));
			form.addXmlnukeObject(new XmlInputHidden("curpage", this._context.ContextValue("curpage")));
			form.addXmlnukeObject(new XmlInputHidden("valueid", uid));
			textbox = new XmlInputTextBox(this.myWords.Value("FORMNEWPASSWORD"), "password", "");
			textbox.setInputTextBoxType(InputTextBoxType.PASSWORD);
			textbox.setDataType(INPUTTYPE.TEXT);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);
			boxButton = new XmlInputButtons();
			boxButton.addSubmit(this.myWords.Value("TXT_CHANGE"), "");
			form.addXmlnukeObject(boxButton);
			tabview.addTabItem(this.myWords.Value("TABCHANGEPASSWD"), form);

			// -------------------------------------------------------------------
			// REMOVER USUARIO
			form = new XmlFormCollection(this._context, url, "");
			form.setJSValidate(true);
			form.addXmlnukeObject(new XmlInputHidden("action", "delete"));
			form.addXmlnukeObject(new XmlInputHidden("curpage", this._context.ContextValue("curpage")));
			form.addXmlnukeObject(new XmlInputHidden("valueid", uid));
			boxButton = new XmlInputButtons();
			boxButton.addSubmit(this.myWords.Value("BUTTONREMOVE"), "");
			form.addXmlnukeObject(boxButton);
			tabview.addTabItem(this.myWords.Value("TABREMOVEUSER"), form);


			// -------------------------------------------------------------------
			// REMOVER SITE DO USUARIO
			XmlParagraphCollection para = new XmlParagraphCollection();
			form = new XmlFormCollection(this._context, url, this.myWords.Value("FORMEDITSITES"));
			form.setJSValidate(true);
			form.addXmlnukeObject(new XmlInputHidden("action", "removesite"));
			form.addXmlnukeObject(new XmlInputHidden("curpage", this._context.ContextValue("curpage")));
			form.addXmlnukeObject(new XmlInputHidden("valueid", uid));
			string[] usersites = users.returnUserProperty(uid, UserProperty.Site);
			NameValueCollection userSites = new NameValueCollection();
			if (usersites != null)
			{
				foreach (string site in usersites)
				{
					userSites.Add(site, site);
				}
			}
			form.addXmlnukeObject(new XmlEasyList(EasyListType.SELECTLIST, "sites", this.myWords.Value("FORMUSERSITES"), userSites));
			boxButton = new XmlInputButtons();
			boxButton.addSubmit(this.myWords.Value("TXT_REMOVE"), "");
			form.addXmlnukeObject(boxButton);
			para.addXmlnukeObject(form);

			// -------------------------------------------------------------------
			// ADICIONAR SITE AO USUARIO
			form = new XmlFormCollection(this._context, url, "");
			form.setJSValidate(true);
			form.addXmlnukeObject(new XmlInputHidden("action", "addsite"));
			form.addXmlnukeObject(new XmlInputHidden("curpage", this._context.ContextValue("curpage")));
			form.addXmlnukeObject(new XmlInputHidden("valueid", uid));
			string[] existingsites = this._context.ExistingSites();
			NameValueCollection existingSites = new NameValueCollection();
			foreach (string site in existingsites)
			{
				string osite = com.xmlnuke.util.FileUtil.ExtractFileName(site);
				existingSites.Add(osite, osite);
			}
			form.addXmlnukeObject(new XmlEasyList(EasyListType.SELECTLIST, "sites", this.myWords.Value("FORMSITES"), existingSites));
			boxButton = new XmlInputButtons();
			boxButton.addSubmit(this.myWords.Value("TXT_ADD"), "");
			form.addXmlnukeObject(boxButton);
			para.addXmlnukeObject(form);
			tabview.addTabItem(this.myWords.Value("TABMANSITE"), para);




			// -------------------------------------------------------------------
			// REMOVER PAPEL DO USUARIO
			para = new XmlParagraphCollection();
			form = new XmlFormCollection(this._context, url, this.myWords.Value("FORMEDITROLES"));
			form.setJSValidate(true);
			form.addXmlnukeObject(new XmlInputHidden("action", "removerole"));
			form.addXmlnukeObject(new XmlInputHidden("curpage", this._context.ContextValue("curpage")));
			form.addXmlnukeObject(new XmlInputHidden("valueid", uid));
			string[] userroles = users.returnUserProperty(uid, UserProperty.Role);
			NameValueCollection userRoles = new NameValueCollection();
			if (userroles != null)
			{
				foreach (string role in userroles)
				{
					userRoles.Add(role, role);
				}
			}
			form.addXmlnukeObject(new XmlEasyList(EasyListType.SELECTLIST, "role", this.myWords.Value("FORMUSERROLES"), userRoles));
			boxButton = new XmlInputButtons();
			boxButton.addSubmit(this.myWords.Value("TXT_REMOVE"), "");
			form.addXmlnukeObject(boxButton);
			para.addXmlnukeObject(form);

			// -------------------------------------------------------------------
			// ADICIONAR PAPEL AO USUARIO
			form = new XmlFormCollection(this._context, url, "");
			form.setJSValidate(true);
			form.addXmlnukeObject(new XmlInputHidden("action", "addrole"));
			form.addXmlnukeObject(new XmlInputHidden("curpage", this._context.ContextValue("curpage")));
			form.addXmlnukeObject(new XmlInputHidden("valueid", uid));
			NameValueCollection roleData = this.getAllRoles(users);
			XmlEasyList selectRole = new XmlEasyList(EasyListType.SELECTLIST, "role", this.myWords.Value("FORMROLES"), roleData);
			form.addXmlnukeObject(selectRole);

			boxButton = new XmlInputButtons();
			boxButton.addSubmit(this.myWords.Value("TXT_ADD"), "");
			form.addXmlnukeObject(boxButton);
			para.addXmlnukeObject(form);
			tabview.addTabItem(this.myWords.Value("TABMANROLE"), para);

			//------------------------------------------------------------------------
			// CUSTOM FIELDS
			//------------------------------------------------------------------------

			XmlnukeSpanCollection block2 = new XmlnukeSpanCollection();

			XmlTableCollection table = new XmlTableCollection();
			XmlTableRowCollection row = new XmlTableRowCollection();

			XmlTableColumnCollection col = new XmlTableColumnCollection();
			col.addXmlnukeObject(new XmlnukeText(this.myWords.Value("ACTION"), true));
			row.addXmlnukeObject(col);

			col = new XmlTableColumnCollection();
			col.addXmlnukeObject(new XmlnukeText(this.myWords.Value("GRIDFIELD"), true));
			row.addXmlnukeObject(col);

			col = new XmlTableColumnCollection();
			col.addXmlnukeObject(new XmlnukeText(this.myWords.Value("GRIDVALUE"), true));
			row.addXmlnukeObject(col);
			table.addXmlnukeObject(row);

			string[] fields = user.getFieldNames();

			foreach (string fldName in fields)
			{
				row = new XmlTableRowCollection();

				col = new XmlTableColumnCollection();
				if ((fldName != users.getUserTable().Name) && (fldName != users.getUserTable().Username) && (fldName != users.getUserTable().Email) && (fldName != users.getUserTable().Password) && (fldName != users.getUserTable().Created) && (fldName != users.getUserTable().Admin) && (fldName != users.getUserTable().Id))
				{
					XmlAnchorCollection href = new XmlAnchorCollection("admin:ManageUsers?action=removecustomvalue&customfield=" + fldName + "&valueid=" + uid, "");
					href.addXmlnukeObject(new XmlnukeText(this.myWords.Value("TXT_REMOVE")));
					col.addXmlnukeObject(href);
				}
				else
				{
					col.addXmlnukeObject(new XmlnukeText("---"));
				}
				row.addXmlnukeObject(col);

				col = new XmlTableColumnCollection();
				col.addXmlnukeObject(new XmlnukeText(fldName));
				row.addXmlnukeObject(col);

				col = new XmlTableColumnCollection();
				col.addXmlnukeObject(new XmlnukeText(user.getField(fldName)));
				row.addXmlnukeObject(col);
				table.addXmlnukeObject(row);
			}

			XmlParagraphCollection paragraph = new XmlParagraphCollection();
			paragraph.addXmlnukeObject(table);
			block2.addXmlnukeObject(paragraph);

			table = new XmlTableCollection();
			row = new XmlTableRowCollection();

			col = new XmlTableColumnCollection();

			form = new XmlFormCollection(this._context, url, this.myWords.Value("GRIDVALUE"));
			form.setJSValidate(true);
			form.addXmlnukeObject(new XmlInputHidden("action", "addcustomvalue"));
			form.addXmlnukeObject(new XmlInputHidden("curpage", this._context.ContextValue("curpage")));
			form.addXmlnukeObject(new XmlInputHidden("valueid", uid));
			textbox = new XmlInputTextBox(this.myWords.Value("FORMFIELD"), "customfield", "", 20);
			textbox.setInputTextBoxType(InputTextBoxType.TEXT);
			textbox.setDataType(INPUTTYPE.TEXT);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);
			textbox = new XmlInputTextBox(this.myWords.Value("FORMVALUE"), "customvalue", "", 40);
			textbox.setInputTextBoxType(InputTextBoxType.TEXT);
			textbox.setDataType(INPUTTYPE.TEXT);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);
			boxButton = new XmlInputButtons();
			boxButton.addSubmit(this.myWords.Value("TXT_ADD"), "");
			form.addXmlnukeObject(boxButton);
			col.addXmlnukeObject(form);

			row.addXmlnukeObject(col);
			table.addXmlnukeObject(row);
			paragraph = new XmlParagraphCollection();
			paragraph.addXmlnukeObject(table);
			block2.addXmlnukeObject(paragraph);
			tabview.addTabItem(this.myWords.Value("TABCUSTOMVALUE"), block2);

			this.defaultXmlnukeDocument.addXmlnukeObject(block);
		}

		protected NameValueCollection getAllRoles(IUsersBase users)
		{
			NameValueCollection dataArray = new NameValueCollection();
			IIterator it = users.getRolesIterator(this._context.Site);
			while (it.hasNext())
			{
				SingleRow sr = it.moveNext();
				string[] dataArrayRoles = sr.getFieldArray(users.getRolesTable().Role);
				if (dataArrayRoles.Length > 0)
				{
					foreach (string roles in dataArrayRoles)
					{
						dataArray[roles] = roles;
					}
				}
			}
			return dataArray;
		}
	}
}