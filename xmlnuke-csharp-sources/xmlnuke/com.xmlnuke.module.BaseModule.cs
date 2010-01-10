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
using System.Reflection;
using com.xmlnuke.admin;
using com.xmlnuke.classes;
using com.xmlnuke.util;
using System.Collections;
using System.Collections.Specialized;

namespace com.xmlnuke.module
{
	public enum AccessLevel
	{
		OnlyAdmin,
		OnlyCurrentSite,
		OnlyRole,
		OnlyAuthenticated,
		CurrentSiteAndRole
	}

	public class ModuleAction
	{
		public const string Create = "new";
		public const string CreateConfirm = "action.CREATECONFIRM";
		public const string Edit = "edit";
		public const string EditConfirm = "action.EDITCONFIRM";
		public const string Listing = "action.LIST";
		public const string View = "view";
		public const string Delete = "delete";
		public const string DeleteConfirm = "action.DELETECONFIRM";
	}


	/// <summary>
	/// BaseModule class is the base for custom module implementation. This class uses cache, save to disk and other functionalities.
	/// All custom modules must inherits this class and need to have com.xmlnuke.module namespace.
	/// <seealso cref="com.xmlnuke.module.IModule"/><seealso cref="com.xmlnuke.module.ModuleFactory"/>
	/// </summary>
	public abstract class BaseModule : IModule
	{
		/// <summary>XMLNuke context</summary>
		protected engine.Context _context;
		/// <summary>Module name</summary>
		protected processor.XMLFilenameProcessor _xmlModuleName;
		/// <summary>Cache file module</summary>
		protected processor.XMLCacheFilenameProcessor _cacheFile;
		/// <summary>Action from Request["Action"]</summary>
		protected string _action;

		protected int _starttime;

		protected international.LanguageCollection _myWords;

		/// <summary>Internal state. If true, ignore USECACHE inside hasInCache</summary>
		protected bool _ignoreCache = false;

		public XmlnukeDocument defaultXmlnukeDocument;

		/// <summary>
		/// BaseModule constructor
		/// </summary>
		public BaseModule()
		{ }

		/// <summary>
		/// Add custom setup elements
		/// </summary>
		/// <param name="xmlModuleName"></param>
		/// <param name="context"></param>
		/// <param name="customArgs"></param>
		public virtual void Setup(processor.XMLFilenameProcessor xmlModuleName, engine.Context context, object customArgs)
		{
			_starttime = System.Environment.TickCount;
			_xmlModuleName = xmlModuleName;
			_context = context;
			_cacheFile = new processor.XMLCacheFilenameProcessor(_xmlModuleName.ToString(), this._context);
			_action = _context.ContextValue("action") + _context.ContextValue("acao");
			object[] args = new object[] { customArgs };
			this.GetType().InvokeMember("CustomSetup", BindingFlags.InvokeMethod, null, this, args);
			this.defaultXmlnukeDocument = new XmlnukeDocument();
		}

		/// <summary>
		/// CustomSetup Imodule interface
		/// </summary>
		/// <param name="customArg">Object</param>
		public virtual void CustomSetup(object customArg)
		{ }

		/// <summary>
		/// WordCollection Imodule interface
		/// </summary>
		/// <returns></returns>
		public virtual international.LanguageCollection WordCollection()
		{
			international.LanguageCollection lang = new international.LanguageCollection(this._context);
			processor.AnydatasetLangFilenameProcessor langFile = new processor.AnydatasetLangFilenameProcessor(_xmlModuleName.ToString().ToLower().Replace('.', '-'), _context);
			lang.LoadLanguages(langFile);
			return lang;
		}

		/// <summary>
		/// hasInCache Imodule interface
		/// </summary>
		/// <returns></returns>
		public virtual bool hasInCache()
		{
			return (!this._ignoreCache && util.FileUtil.Exists(_cacheFile.FullQualifiedNameAndPath()) && (!_context.NoCache || !_context.Reset));
		}

		/// <sumary>
		/// Create a digest filename to be used in CACHE. Usual for modules.
		/// </sumary>
		protected void validateDynamicCache(int timeInSeconds)
		{
			// Retrieve Basic XMLNuke paramenters
			SortedList chaves = new SortedList();
			chaves["site"] = this._context.Site;
			if (this._context.ContextValue("module") != "")
			{
				chaves["module"] = this._context.ContextValue("module");
			}
			else
			{
				chaves["xml"] = this._context.Xml;
			}
			chaves["xsl"] = this._context.Xml;
			chaves["lang"] = this._context.Language.Name;

			// Create array of parameters from request
			foreach (string key in this._context.getRequestedParams())
			{
				if (!String.IsNullOrEmpty(key))
				{
					string chave = key.ToLower();
					string valor = this._context.ContextValue(chave).ToLower();
					if ((key.IndexOf("imagefield_") == -1) &&
						(key != "phpsessid") &&
						(key != "reset") &&
						(key != "debug") &&
						(key != "nocache") &&
						(key != "x") &&
						(key != "y") &&
						(key != "site") &&
						(key != "xml") &&
						(key != "xsl") &&
						(key != "module") &&
						(key != "__clickevent") &&
						(key != "__postback")
						)
					{
						chaves.Add(chave, valor);
					}
				}
			}

			string str = "";
			foreach (string chave in chaves.Keys)
			{
				str += chave + "=" + chaves[chave] + "/";
			}

			IUsersBase users = this.getUsersDatabase();
			this._cacheFile = new processor.XMLCacheFilenameProcessor(users.getSHAPassword(str), this._context);

			// Test if cache exists
			string fileControl = this._cacheFile.FullQualifiedNameAndPath() + ".control";
			if (util.FileUtil.Exists(fileControl))
			{
				//Debug::PrintValue("Have Control File");
				DateTime horaMod = System.IO.File.GetLastWriteTime(fileControl);
				TimeSpan tempo = System.DateTime.Now - horaMod;
				if (tempo.Seconds < 30)
				{
					return;
				}
			}

			//this._context.Debug(str);
			string file = this._cacheFile.FullQualifiedNameAndPath();
			if (util.FileUtil.Exists(file))
			{
				DateTime horaMod = System.IO.File.GetLastWriteTime(file);
				TimeSpan tempo = System.DateTime.Now - horaMod;
				if (tempo.Seconds > timeInSeconds || _context.Reset || _context.NoCache)
				{
					//util.FileUtil.DeleteFile(this._cacheFile);
					util.FileUtil.QuickFileWrite(fileControl, horaMod.ToString("dd/MM/yyyy hh:mm"));
					this._ignoreCache = true;
				}
			}

		}

		/// <summary>
		/// useCache Imodule interface
		/// </summary>
		/// <returns>Default is True</returns>
		public virtual bool useCache()
		{
			return false;
		}

		/// <summary>
		/// getFromCache Imodule interface. Implement basic read cache file
		/// </summary>
		/// <returns></returns>
		public virtual string getFromCache()
		{
			if (hasInCache())
			{
				return util.FileUtil.QuickFileRead(_cacheFile.FullQualifiedNameAndPath());
			}
			else
			{
				return "";
			}
		}

		/// <summary>
		/// saveToCache IModule interface. Implements basic save cache file.
		/// </summary>
		/// <param name="content">XHtml string to be cached</param>
		public virtual void saveToCache(string content)
		{
			util.FileUtil.QuickFileWrite(_cacheFile.FullQualifiedNameAndPath(), content);
			this.deleteControlCache();
		}

		/// <summary>
		/// resetCache IModule interface. saveToCache Implements basic reset cache file.
		/// </summary>
		public virtual void resetCache()
		{
			this.deleteControlCache();
			util.FileUtil.DeleteFile(_cacheFile);
		}

		protected void deleteControlCache()
		{
			string fileControl = this._cacheFile.FullQualifiedNameAndPath() + ".control";
			if (util.FileUtil.Exists(fileControl))
			{
				util.FileUtil.DeleteFile(fileControl);
			}
		}

		/// <summary>
		/// PageXml IModule interface. 
		/// </summary>
		/// <returns>Empty PageXmL object</returns>
		public virtual classes.IXmlnukeDocument CreatePage()
		{
			return new XmlnukeDocument("Implement this method", "Implement this method");
		}

		/// <summary>
		/// requiresAuthentication IModule interface.
		/// </summary>
		/// <returns>False</returns>
		public virtual bool requiresAuthentication()
		{
			return false;
		}

		/// <summary>
		/// </summary>
		public virtual IUsersBase getUsersDatabase()
		{
            return this._context.getUsersDatabase(); // For Compatibility Reason
		}

		/// <summary>
		/// Base module have some basic tests, like check if user is admin or if user is from current site and have specific role. This method can be overrided to implement another validations.
		/// </summary>
		/// <returns>True</returns>
		public virtual bool accessGranted()
		{
			IUsersBase users = this.getUsersDatabase();
			anydataset.SingleRow currentUser = users.getUserName(_context.authenticatedUser());
			if (currentUser.getField(users.getUserTable().Admin) == "yes")
			{
				return true;
			}
			else
			{
				if (this.getAccessLevel() != AccessLevel.OnlyAdmin)
				{
					bool grantToSite = false;
					bool grantToRole = false;

					if (this.getAccessLevel() == AccessLevel.OnlyAuthenticated)
					{
						return true;
					}

					if ((this.getAccessLevel() == AccessLevel.OnlyCurrentSite) || (this.getAccessLevel() == AccessLevel.CurrentSiteAndRole))
					{
						grantToSite = users.checkUserProperty(_context.authenticatedUser(), _context.Site, admin.UserProperty.Site);
					}
					if ((this.getAccessLevel() == AccessLevel.OnlyRole) || (this.getAccessLevel() == AccessLevel.CurrentSiteAndRole))
					{
						string[] roles = this.getRole();
						if (roles != null)
						{
							foreach (string role in roles)
							{
								grantToRole = grantToRole || users.checkUserProperty(_context.authenticatedUser(), role, admin.UserProperty.Role);
							}
						}
					}

					if (this.getAccessLevel() == AccessLevel.CurrentSiteAndRole)
					{
						return (grantToSite && grantToRole);
					}
					else
					{
						return (grantToSite || grantToRole);
					}
				}
				else
				{
					return false;
				}
			}
		}

		public virtual AccessLevel getAccessLevel()
		{
			// For security reasons. Each module need set the proper access level.
			return AccessLevel.OnlyAdmin;
		}

		public virtual void processInsufficientPrivilege()
		{
			throw new com.xmlnuke.exceptions.InsufficientPrivilegeException("You do not have rights to access this feature");
		}

		public virtual string[] getRole()
		{
			return null;
		}

		public void finalizeModule()
		{
			int endtime = System.Environment.TickCount;
			int result = endtime - _starttime;
			if (this._context.getDebugInModule())
			{
				Debug.Print("Total Execution Time: " + result.ToString() + " ms ");
			}
		}


		protected NameValueCollection _checkedPermission = new NameValueCollection();

		public void CurrentUserHasPermission()
		{
			this.CurrentUserHasPermission(null);
		}

		public bool CurrentUserHasPermission(string[] permission)
		{
			if (permission == null || permission.Length == 0)
			{
				permission = this.getRole();
			}

			bool ok = false;
			string checkPerm = "";
			for (int i = 0; i < checkPerm.Length; i++)
			{
				checkPerm += permission[i] + "|";
			}
			if (this._checkedPermission[checkPerm] == null)
			{
				IUsersBase users = this.getUsersDatabase();

				foreach (string value in permission)
				{
					ok = ok || users.checkUserProperty(this._context.authenticatedUserId(), value, UserProperty.Role);
				}
				this._checkedPermission[checkPerm] = ok.ToString().ToLower();
			}
			else
			{
				ok = (this._checkedPermission[checkPerm] == "true" ? true : false);
			}

			return ok;
		}


		public void addMenuItem(string id, string title, string summary)
		{
			this.addMenuItem(id, title, summary, "__DEFAULT__", null);
		}

		public void addMenuItem(string id, string title, string summary, string group, string[] permission)
		{
			// Check Array Of Permission to put MENU
			bool ok = (permission == null ? true : this.CurrentUserHasPermission(permission));

			// If is OK, add the menu, otherwise, nothing to do. 
			if (ok)
			{
				this.defaultXmlnukeDocument.addMenuItem(id, title, summary, group);
			}
		}

		/// <summary>
		/// Method for process button click and events associated.
		/// </summary>
		public void processEvent()
		{
			if (this.isPostBack() && (this._context.ContextValue("__clickevent") != ""))
			{
				string[] events = this._context.ContextValue("__clickevent").Split('|');
				foreach (string eventName in events)
				{
					if (this._context.ContextValue(eventName) != "")
					{
						MethodInfo method = this.GetType().GetMethod(eventName + "_Event");
						if (method != null)
						{
							//object[] args = { 21 };
							method.Invoke(this, null);
						}
						else
						{
							throw new Exception("Method " + eventName + "_Event() does not exist");
						}
					}
				}
			}
		}

		/// <summary>
		/// Bind public string class parameters based on Request Get e Form
		/// </summary>
		protected void bindParameteres()
		{
			this.bindParameteres(this);
		}

		/// <summary>
		/// Bind public string class parameters based on Request Get e Form
		/// </summary>
		protected void bindParameteres(object instance)
		{
			Type t = instance.GetType();

			PropertyInfo[] pi = t.GetProperties();
			foreach (PropertyInfo prop in pi)
			{
				if (prop.CanWrite && (prop.PropertyType.FullName == "System.String") && (this._context.ContextValue(prop.Name) != ""))
				{
					prop.SetValue(this, this._context.ContextValue(prop.Name), null);
				}
			}
		}

		public bool isPostBack()
		{
			return (this._context.ContextValue("__postback") != "");
		}

	}
}