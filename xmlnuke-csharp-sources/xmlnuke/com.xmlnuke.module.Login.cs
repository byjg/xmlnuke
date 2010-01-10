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
using System.Web.Security;
using com.xmlnuke;
using com.xmlnuke.anydataset;
using com.xmlnuke.international;
using com.xmlnuke.admin;
using com.xmlnuke.classes;

using com.xmlnuke.util;

namespace com.xmlnuke.module
{
	/// <summary>
	/// Login is a default module descendant from BaseModule class. 
	/// This class shows/edit the profile from the current user.
	/// </summary>
	/// <seealso cref="com.xmlnuke.module.IModule"/><seealso cref="com.xmlnuke.module.BaseModule"/><seealso cref="com.xmlnuke.module.ModuleFactory"/>
	public class Login : LoginBase
	{
		protected IUsersBase _users;

		protected string _urlReturn;

		private string _module = "login";

		/**
		*@desc Default constructor
		*/
		public Login()
		{ }

		/**
		*@return PageXml
		*/
		override public IXmlnukeDocument CreatePage()
		{
			this._users = this.getUsersDatabase();
			LanguageCollection myWords = this.WordCollection();
			this.defaultXmlnukeDocument.PageTitle = myWords.Value("TITLELOGIN");
			XmlBlockCollection blockcenter = new XmlBlockCollection(myWords.Value("TITLELOGIN"), BlockPosition.Center);
			this.defaultXmlnukeDocument.addXmlnukeObject(blockcenter);
			this._urlReturn = this._context.ContextValue("ReturnUrl");
			switch (this._action)
			{
				case ModuleActionLogin.LOGIN:
					this.MakeLogin(blockcenter);
					break;
				case ModuleActionLogin.FORGOTPASSWORD:
					this.ForgotPassword(blockcenter);
					break;
				case ModuleActionLogin.FORGOTPASSWORDCONFIRM:
					this.ForgotPasswordConfirm(blockcenter);
					break;
				case ModuleActionLogin.NEWUSER:
					this.CreateNewUser(blockcenter);
					break;
				case ModuleActionLogin.NEWUSERCONFIRM:
					this.CreateNewUserConfirm(blockcenter);
					break;
				default:
					this.FormLogin(blockcenter);
					break;
			}


			return this.defaultXmlnukeDocument.generatePage();
		}

		/**
		*@param XmlBlockCollection block
		*/
		protected void MakeLogin(XmlBlockCollection block)
		{
			LanguageCollection myWords = this.WordCollection();
			SingleRow user = this._users.validateUserName(this._context.ContextValue("loguser"), this._context.ContextValue("password"));
			if (user == null)
			{
			    XmlnukeUIAlert container = new XmlnukeUIAlert(this._context, UIAlert.BoxAlert);
                container.setAutoHide(5000);
				container.addXmlnukeObject(new XmlnukeText(myWords.Value("LOGINFAIL"), true));
				block.addXmlnukeObject(container);

				this.FormLogin(block);
			}
			else
			{
				this.updateInfo(user.getField(this._users.getUserTable().Username), user.getField(this._users.getUserTable().Id));
			}
		}
		/**
		*@param XmlBlockCollection block
		*/
		protected void FormLogin(XmlBlockCollection block)
		{
			LanguageCollection myWords = this.WordCollection();
			XmlParagraphCollection paragraph = new XmlParagraphCollection();

			XmlnukeManageUrl url = new XmlnukeManageUrl(URLTYPE.MODULE, this._module);
			url.addParam("action", ModuleActionLogin.LOGIN);
			url.addParam("ReturnUrl", this._urlReturn);
			XmlFormCollection form = new XmlFormCollection(this._context, url.getUrl(), myWords.Value("LOGINTITLE"));
			form.setJSValidate(true);

			XmlInputTextBox textbox = new XmlInputTextBox(myWords.Value("LABEL_NAME"), "loguser", this._context.ContextValue("loguser"), 20);
			textbox.setRequired(true);
			textbox.setInputTextBoxType(InputTextBoxType.TEXT);
			form.addXmlnukeObject(textbox);

			textbox = new XmlInputTextBox(myWords.Value("LABEL_PASSWORD"), "password", "", 20);
			textbox.setInputTextBoxType(InputTextBoxType.PASSWORD);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);

			XmlInputButtons button = new XmlInputButtons();
			button.addSubmit(myWords.Value("TXT_LOGIN"), "submit_button");

			form.addXmlnukeObject(button);
			paragraph.addXmlnukeObject(form);

			XmlInputLabelObjects label = new XmlInputLabelObjects(myWords.Value("LOGINPROBLEMSMESSAGE"));
			url = new XmlnukeManageUrl(URLTYPE.MODULE, this._module);
			url.addParam("action", ModuleActionLogin.FORGOTPASSWORD);
			url.addParam("ReturnUrl", this._urlReturn);
			XmlAnchorCollection link = new XmlAnchorCollection(url.getUrl(), null);
			link.addXmlnukeObject(new XmlnukeText(myWords.Value("LOGINFORGOTMESSAGE")));
			label.addXmlnukeObject(link);
			label.addXmlnukeObject(new XmlnukeBreakLine());
			url = new XmlnukeManageUrl(URLTYPE.MODULE, this._module);
			url.addParam("action", ModuleActionLogin.NEWUSER);
			url.addParam("ReturnUrl", this._urlReturn);
			link = new XmlAnchorCollection(url.getUrl(), null);
			link.addXmlnukeObject(new XmlnukeText(myWords.Value("LOGINCREATEUSERMESSAGE")));
			label.addXmlnukeObject(link);
			form.addXmlnukeObject(label);

			block.addXmlnukeObject(paragraph);
		}
		/**
		*@param XmlBlockCollection block
		*/
		protected void ForgotPassword(XmlBlockCollection block)
		{
			LanguageCollection myWords = this.WordCollection();
			XmlParagraphCollection paragraph = new XmlParagraphCollection();
			XmlnukeManageUrl url = new XmlnukeManageUrl(URLTYPE.MODULE, this._module);
			url.addParam("action", ModuleActionLogin.FORGOTPASSWORDCONFIRM);
			url.addParam("ReturnUrl", this._urlReturn);

			XmlFormCollection form = new XmlFormCollection(this._context, url.getUrl(), myWords.Value("FORGOTPASSTITLE"));

			XmlInputTextBox textbox = new XmlInputTextBox(myWords.Value("LABEL_EMAIL"), "email", this._context.ContextValue("email"), 40);
			textbox.setInputTextBoxType(InputTextBoxType.TEXT);
			textbox.setDataType(INPUTTYPE.EMAIL);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);

			XmlInputButtons button = new XmlInputButtons();
			button.addSubmit(myWords.Value("FORGOTPASSBUTTON"), "submit_button");
			form.addXmlnukeObject(button);
			paragraph.addXmlnukeObject(form);
			block.addXmlnukeObject(paragraph);
		}
		/**
		*@param XmlBlockCollection block
		*/
		protected void ForgotPasswordConfirm(XmlBlockCollection block)
		{
			LanguageCollection myWords = this.WordCollection();

            XmlnukeUIAlert container = new XmlnukeUIAlert(this._context, UIAlert.BoxInfo);
            container.setAutoHide(5000);
			block.addXmlnukeObject(container);

			SingleRow user = this._users.getUserEMail(this._context.ContextValue("email"));

			if (user == null)
			{
				container.addXmlnukeObject(new XmlnukeText(myWords.Value("FORGOTUSERFAIL"), true, false, false));
				this.ForgotPassword(block);
			}
			else
			{
				string newpassword = this.getRandomPassword();
				user.setField(this._users.getUserTable().Password, this._users.getSHAPassword(newpassword));
				this.sendWelcomeMessage(myWords, user.getField(this._users.getUserTable().Name), user.getField(this._users.getUserTable().Username), user.getField(this._users.getUserTable().Email), newpassword);
				this._users.Save();
				container.addXmlnukeObject(new XmlnukeText(myWords.Value("FORGOTUSEROK"), true, false, false));
				this.FormLogin(block);
			}
		}
		/**
		*@param XmlBlockCollection block
		*/
		protected void CreateNewUser(XmlBlockCollection block)
		{
			LanguageCollection myWords = this.WordCollection();
			XmlParagraphCollection paragraph = new XmlParagraphCollection();
			XmlnukeManageUrl url = new XmlnukeManageUrl(URLTYPE.MODULE, this._module);
			url.addParam("action", ModuleActionLogin.NEWUSERCONFIRM);
			url.addParam("ReturnUrl", this._urlReturn);

			XmlFormCollection form = new XmlFormCollection(this._context, url.getUrl(), myWords.Value("CREATEUSERTITLE"));

			XmlInputTextBox textbox = new XmlInputTextBox(myWords.Value("LABEL_LOGIN"), "loguser", this._context.ContextValue("loguser"), 20);
			textbox.setInputTextBoxType(InputTextBoxType.TEXT);
			textbox.setDataType(INPUTTYPE.TEXT);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);

			textbox = new XmlInputTextBox(myWords.Value("LABEL_NAME"), "name", this._context.ContextValue("name"), 20);
			textbox.setInputTextBoxType(InputTextBoxType.TEXT);
			textbox.setDataType(INPUTTYPE.TEXT);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);

			textbox = new XmlInputTextBox(myWords.Value("LABEL_EMAIL"), "email", this._context.ContextValue("email"), 20);
			textbox.setInputTextBoxType(InputTextBoxType.TEXT);
			textbox.setDataType(INPUTTYPE.EMAIL);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);

			XmlInputLabelField label = new XmlInputLabelField("", myWords.Value("CREATEUSERPASSWORDMSG"));
			form.addXmlnukeObject(label);
			form.addXmlnukeObject(new XmlInputImageValidate(myWords.Value("TYPETEXTFROMIMAGE")));

			XmlInputButtons button = new XmlInputButtons();
			button.addSubmit(myWords.Value("CREATEUSERBUTTON"), "submit_button");
			form.addXmlnukeObject(button);
			paragraph.addXmlnukeObject(form);
			block.addXmlnukeObject(paragraph);
		}
		/**
		*@param XmlBlockCollection block
		*/
		protected void CreateNewUserConfirm(XmlBlockCollection block)
		{
			LanguageCollection myWords = this.WordCollection();

            XmlnukeUIAlert container = new XmlnukeUIAlert(this._context, UIAlert.BoxAlert);
            container.setAutoHide(5000);
            block.addXmlnukeObject(container);

			string newpassword = this.getRandomPassword();

			if (!XmlInputImageValidate.validateText(this._context))
			{
				container.addXmlnukeObject(new XmlnukeText(myWords.Value("OBJECTIMAGEINVALID"), true));
				this.CreateNewUser(block);
			}
			else
			{
				if (!this._users.addUser(this._context.ContextValue("name"), this._context.ContextValue("loguser"), this._context.ContextValue("email"), newpassword))
				{
					container.addXmlnukeObject(new XmlnukeText(myWords.Value("CREATEUSERFAIL"), true, false, false));
					this.CreateNewUser(block);
				}
				else
				{
					this.sendWelcomeMessage(myWords, this._context.ContextValue("name"), this._context.ContextValue("loguser"), this._context.ContextValue("email"), newpassword);
					this._users.Save();
					container.addXmlnukeObject(new XmlnukeText(myWords.Value("CREATEUSEROK"), true, false, false));
                    container.setUIAlertType(UIAlert.BoxInfo);
					this.FormLogin(block);
				}
			}
		}
	}
}