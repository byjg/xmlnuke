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
using com.xmlnuke.anydataset;
using com.xmlnuke.classes;
using com.xmlnuke.admin;
using com.xmlnuke.international;

namespace com.xmlnuke.module
{
	/// <summary>
	/// UserProfile is a default module descendant from BaseModule class. 
	/// This class shows/edit the profile from the current user.
	/// </summary>
	/// <seealso cref="com.xmlnuke.module.IModule"/><seealso cref="com.xmlnuke.module.BaseModule"/><seealso cref="com.xmlnuke.module.ModuleFactory"/>
	public class UserProfile : BaseModule
	{
		protected SingleRow _user;
		protected IUsersBase _users;
		protected string _url;
		protected XmlParagraphCollection _paragraph;
		
		public UserProfile()
		{}

		/// <summary>
		/// Return the LanguageCollection used in this module
		/// </summary>
		/// <returns>
		/// A <see cref="LanguageCollection"/>
		/// </returns>
		public override LanguageCollection WordCollection()
		{
			LanguageCollection myWords = base.WordCollection();
			return myWords;
		}

		/// <summary>
		/// Returns if use cache
		/// </summary>
		/// <returns>
		/// A <see cref="System.Boolean"/>
		/// </returns>
		public override bool useCache()
		{
			return false;
		}

		/// <summary>
		/// Requires Authentication
		/// </summary>
		/// <returns>
		/// A <see cref="System.Boolean"/>
		/// </returns>
		public override bool requiresAuthentication()
		{
			return true;
		}

		/// <summary>
		/// Access Granted
		/// </summary>
		/// <returns>
		/// A <see cref="System.Boolean"/>
		/// </returns>
		public override bool accessGranted()
		{
			return true;
		}

		public override IXmlnukeDocument CreatePage()
		{
			this._myWords = this.WordCollection();
			
			string title = this._myWords.Value("TITLE", this._context.ContextValue("SERVER_NAME") );
			string strAbstract = this._myWords.Value("ABSTRACT", this._context.ContextValue("SERVER_NAME") );
			XmlnukeDocument document = new XmlnukeDocument(title, strAbstract);
			
			this._url = "module:UserProfile";
					
			this._users = this.getUsersDatabase();
			
			this._user = this._users.getUserName( this._context.authenticatedUser() );
			
			string blockCenterTitle = this._myWords.Value("TITLE", this._user.getField(this._users.getUserTable().Username));
			XmlBlockCollection blockcenter = new XmlBlockCollection(blockCenterTitle, BlockPosition.Center );
			document.addXmlnukeObject(blockcenter);
			
			this._paragraph = new XmlParagraphCollection();
			blockcenter.addXmlnukeObject(this._paragraph);

			string action = this._context.ContextValue("action");

			switch (action)
			{
				case "update":
					this.update();
					break;
				case "changepassword":
					this.changePWD();
					break;				
			}		
			
			this.formUserInfo();
			
			this.formPasswordInfo();
			
			this.formRolesInfo();	

			return document.generatePage();
		}

		/// <summary>
		/// Update
		/// </summary>
		protected void update()
		{
			this._user.setField(this._users.getUserTable().Name, this._context.ContextValue("name") );
			this._user.setField(this._users.getUserTable().Email, this._context.ContextValue("email") );
			this._paragraph.addXmlnukeObject(new XmlnukeText(this._myWords.Value("UPDATEOK"), true));
			this._users.Save();
		}

		/// <summary>
		/// Change Password
		/// </summary>
		/// <returns>
		/// A <see cref="function"/>
		/// </returns>
		protected void changePWD()
		{
			if (this._user.getField(this._users.getUserTable().Password) != this._users.getSHAPassword(this._context.ContextValue("oldpassword")))
			{
				this._paragraph.addXmlnukeObject(new XmlnukeText(this._myWords.Value("CHANGEPASSOLDPASSFAILED") , true));
			}
			else
			{
				if (this._context.ContextValue("newpassword") != this._context.ContextValue("newpassword2"))
				{
					this._paragraph.addXmlnukeObject(new XmlnukeText(this._myWords.Value("CHANGEPASSNOTMATCH"), true));
				}
				else
				{
					this._user.setField(this._users.getUserTable().Password, this._users.getSHAPassword(this._context.ContextValue("newpassword")) );
					this._paragraph.addXmlnukeObject(new XmlnukeText(this._myWords.Value("CHANGEPASSOK"), true));
					this._users.Save();
				}
			}
		}

		/// <summary>
		/// Show the info of user in the form
		/// </summary>
		protected void formUserInfo()
		{
			XmlFormCollection form = new XmlFormCollection(this._context, this._url, this._myWords.Value("UPDATETITLE"));
			this._paragraph.addXmlnukeObject(form);		
			
			XmlInputHidden hidden = new XmlInputHidden("action", "update");
			form.addXmlnukeObject(hidden);
			
			XmlInputLabelField labelField = new XmlInputLabelField(this._myWords.Value("LABEL_LOGIN"), this._user.getField(this._users.getUserTable().Username));
			form.addXmlnukeObject(labelField);
			
			XmlInputTextBox textBox = new XmlInputTextBox(this._myWords.Value("LABEL_NAME"), "name",this._user.getField(this._users.getUserTable().Name));
			form.addXmlnukeObject(textBox);
			
			textBox = new XmlInputTextBox(this._myWords.Value("LABEL_EMAIL"), "email", this._user.getField(this._users.getUserTable().Email));
			form.addXmlnukeObject(textBox);
			
			XmlInputButtons button = new XmlInputButtons();
			button.addSubmit(this._myWords.Value("TXT_UPDATE"),"");
			form.addXmlnukeObject(button);
		}
		
		/// <summary>
		/// Form Password Info
		/// </summary>
		protected void formPasswordInfo()
		{
			XmlFormCollection form = new XmlFormCollection(this._context, this._url, this._myWords.Value("CHANGEPASSTITLE"));
			this._paragraph.addXmlnukeObject(form);
			
			XmlInputHidden hidden = new XmlInputHidden("action", "changepassword");
			form.addXmlnukeObject(hidden);
			
			XmlInputTextBox textbox = new XmlInputTextBox(this._myWords.Value("CHANGEPASSOLDPASS"), "oldpassword","");
			textbox.setInputTextBoxType(InputTextBoxType.PASSWORD );
			form.addXmlnukeObject(textbox);
			
			textbox = new XmlInputTextBox(this._myWords.Value("CHANGEPASSNEWPASS"), "newpassword","");
			textbox.setInputTextBoxType(InputTextBoxType.PASSWORD );
			form.addXmlnukeObject(textbox);
			
			textbox = new XmlInputTextBox(this._myWords.Value("CHANGEPASSNEWPASS2"), "newpassword2","");
			textbox.setInputTextBoxType(InputTextBoxType.PASSWORD );
			form.addXmlnukeObject(textbox);
			
			XmlInputButtons button = new XmlInputButtons();
			button.addSubmit(this._myWords.Value("TXT_CHANGE"),"");
			form.addXmlnukeObject(button);
		}
		
		/// <summary>
		/// Form Roles Info
		/// </summary>
		protected void formRolesInfo()
		{		
			XmlFormCollection form = new XmlFormCollection(this._context, this._url, this._myWords.Value("OTHERTITLE"));
			this._paragraph.addXmlnukeObject(form);

			XmlEasyList easyList = new XmlEasyList(EasyListType.SELECTLIST , "", this._myWords.Value("OTHERSITE"), this._users.returnUserProperty(this._context.authenticatedUserId(), UserProperty.Site));
			form.addXmlnukeObject(easyList);
			
			easyList = new XmlEasyList(EasyListType.SELECTLIST , "", this._myWords.Value("OTHERROLE"), this._users.returnUserProperty(this._context.authenticatedUserId(), UserProperty.Role));
			form.addXmlnukeObject(easyList);
			
	//		px.addSelect4(form, this._myWords.Value("OTHERSITE"), "", this._users.returnUserProperty(this._context.authenticatedUserId(), UserProperty.Site));
	//		this._px.addSelect4(form, , "", this._users.returnUserProperty(this._context.authenticatedUserId(), UserProperty.Role));
		}

	}
}