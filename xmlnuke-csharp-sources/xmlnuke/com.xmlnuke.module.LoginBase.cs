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
	public class ModuleActionLogin
	{
		public const string LOGIN = "action.LOGIN";
		public const string NEWUSER = "action.NEWUSER";
		public const string NEWUSERCONFIRM = "action.NEWUSERCONFIRM";
		public const string FORGOTPASSWORD = "action.FORGOTPASSWORD";
		public const string FORGOTPASSWORDCONFIRM = "action.FORGOTPASSWORDCONFIRM";
	}

	/// <summary>
	/// Login is a default module descendant from BaseModule class. 
	/// This class shows/edit the profile from the current user.
	/// </summary>
	/// <seealso cref="com.xmlnuke.module.IModule"/><seealso cref="com.xmlnuke.module.BaseModule"/><seealso cref="com.xmlnuke.module.ModuleFactory"/>
	public abstract class LoginBase : BaseModule
	{
		/**
		*@return LanguageCollection
		*@desc Return the LanguageCollection used in this module
		*/
		override public LanguageCollection WordCollection()
		{
			return base.WordCollection(); ;
		}

		/**
		*@return bool
		*@desc Returns if use cache
		*/
		override public bool useCache()
		{
			return false;
		}

		protected void updateInfo(string usernamevalid, string userid)
		{
			this._context.MakeLogin(usernamevalid, userid);

			string Url = FormsAuthentication.GetRedirectUrl(usernamevalid, false);
			this._context.redirectUrl(Url);
		}

		public string getRandomPassword()
		{
			int type, number;
			string password = "";
			for (int i = 0; i < 7; i++)
			{
				type = this._context.getRandomNumber(21) % 3;
				number = this._context.getRandomNumber(26);
				if (type == 1)
				{
					password += (char)(48 + (number % 10));
				}
				else
				{
					if (type == 2)
					{
						password += (char)(65 + number);
					}
					else
					{
						password += (char)(97 + number);
					}
				}
			}
			return password;
		}

		protected void sendWelcomeMessage(international.LanguageCollection myWords, string name, string user, string email, string password)
		{
			string path = this._context.ContextValue("SCRIPT_NAME");
			path = path.Substring(0, path.LastIndexOf("/") + 1);
			string url = this._context.ContextValue("SERVER_NAME") + path;
			string body = myWords.Value("WELCOMEMESSAGE", new string[] { name, this._context.ContextValue("SERVER_NAME"), user, password, url + this._context.bindModuleUrl("UserProfile") });
			MailUtil.Mail(this._context,
				MailUtil.getEmailFromID(this._context, "DEFAULT"),
				MailUtil.getFullEmailName(name, email),
				this._context.ContextValue("SERVER_NAME") + ": Confirmao de Cadastro",
				null,
				null,
				body);
		}

	}
}