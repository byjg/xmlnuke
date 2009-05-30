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
using System.Web;
using System.Configuration;
using System.Globalization;
using System.Collections;
using System.Collections.Specialized;
using com.xmlnuke.anydataset;

namespace com.xmlnuke.engine
{
	/// <summary>
	/// Context class get data from HttpContext class and Web.Config file and put all in propeties and methods to make easy access their contents
	/// </summary>
	public class Context
	{
		// Object HttpContext from .NET
		private HttpContext _context = null;

		// XmlNuke Version
		private string _XmlNukeVersion = "XMLNuke 3.x C# Edition";

		// Config values
		private NameValueCollection _config = null;
		private string _xml = "";
		private string _xsl = "";
		private CultureInfo _lang = null;
		private string _site = "";
		private bool _reset = false;
		private bool _nocache = false;
		private db.XmlNukeDB _xmlnukedb;
		private string _appNameInMemory;
		/// <summary>It is necessary, because the Random value was returned the same value (because uses the same seed).</summary>
		private Random rnd;

		private string _xmlnukepath = "";

		protected bool _debug;

		protected ArrayList _requestedParams;

		protected string _contentType = "";

		/// <summary>
		/// Context construtor. Read data from HttpContext class and assign default values to main arguments (XML, XSL, SITE and LANG) if doesn't exists.
		/// Process Web.Config and put into NameValueCollection the make easy access it.
		/// </summary>
		/// <param name="context">HttpContext from WebForm</param>
		public Context(HttpContext context)
		{
			_context = context;
			_config = new NameValueCollection();


			System.Configuration.AppSettingsSection webConfig = null;
			object appSettings = context.GetSection("appSettings");

			if (appSettings is NameValueCollection)
			{
				this.AddCollectionToConfig((NameValueCollection)appSettings);
			}
			else if (appSettings is System.Configuration.AppSettingsSection)
			{
				webConfig = (System.Configuration.AppSettingsSection)appSettings;
				if (webConfig != null)
				{
					foreach (string key in webConfig.Settings.AllKeys)
					{
						string value = webConfig.Settings[key].Value;
						_config[key] = value;
					}
				}
			}
			else
			{
				throw new Exception("Something is very Bad!!! GetSection neither is NameValueCollection neither is a AppSettingsSection");
			}

			this._xsl = getParameter("xsl");
			if (this._xsl == "")
			{
				this._xsl = _config["xmlnuke.DEFAULTPAGE"];
			}

			this._xml = getParameter("xml");
			if (this._xml == "")
			{
				this._xml = "home";
			}

			this._site = getParameter("site");
			if (this._site == "")
			{
				this._site = _config["xmlnuke.DEFAULTSITE"];
			}

			_xmlnukepath = _config["xmlnuke.ROOTDIR"];

			this._reset = (bool)(getParameter("reset") != "");
			this._nocache = ((bool)(getParameter("nocache") != "") || (this._config["xmlnuke.ALWAYSUSECACHE"] == "false"));

			this.AddCollectionToConfig(context.Request.QueryString);
			this.AddCollectionToConfig(context.Request.Form);
			this.AddCollectionToConfig(context.Request.ServerVariables);
			this.AddSessionToConfig(context.Session.Contents);
			this.AddCookieToConfig(context.Request.Cookies);

			this._requestedParams = new ArrayList();
			if (context.Request.QueryString.Keys.Count > 0)
			{
				this._requestedParams.AddRange(context.Request.QueryString.Keys);
			}
			if (context.Request.Form.Count > 0)
			{
				this._requestedParams.AddRange(context.Request.Form);
			}

			this.AddPairToConfig("SELFURLREAL", context.Request.ServerVariables["SCRIPT_NAME"] + "?" + context.Request.QueryString + "&");
			this.AddPairToConfig("SELFURL", context.Request.ServerVariables["URL"] + "?" + context.Request.QueryString + "&");
			this.AddPairToConfig("ROOTDIR", _xmlnukepath + "/" + _site);
			this.AddPairToConfig("SITE", _site);
			this.AddPairToConfig("XMLNUKE", this._XmlNukeVersion);
			//this.AddPairToConfig("USERNAME", this.authenticatedUser());
			//this.AddPairToConfig("USERID", this.authenticatedUserId());
			this.AddPairToConfig("ENGINEEXTENSION", "aspx");

			this.readCustomConfig();
			this._debug = _config["xmlnuke.DEBUG"] == "true";

			string lang = getParameter("lang");
			NameValueCollection langAvail = this.LanguagesAvailable();
			if (lang == "")
			{
				// Mono 0.24 doesnt implements SESSION
				//lang = (string)context.Session["lang"];
				//if (lang == null)
				//{

				// Try find in XMLNuke paramenter least one value defined in Browser Accept Language
				// Rules For Determine a default Language:
				// 1- Browser Defined is equals to LanguagesAvailable
				// 2- Major Language (e.g. pt-br, major is pt) matches to first major language in Available Language
				lang = context.Request.ServerVariables["http_accept_language"];
				if (lang != null) // MONO 0.26 doesnt understand HTTP_ACCEPT_LANGUAGE
				{
					string[] langOpt = lang.Split(new Char[] { ',', ';' });
					int i = 0;
					lang = null;
					while ((lang == null) && (i < langOpt.Length))
					{
						string langTmp = langOpt[i++];
						lang = langAvail[langTmp];
						if (lang == null)
						{
							string langMajor = langTmp.Split('-')[0];
							for (int j = 0; (j < langAvail.Count) && (lang == null); j++)
							{
								if (langMajor == langAvail.Keys[j].Split('-')[0])
								{
									lang = langAvail.Keys[j];
								}
							}
						}
						else
						{
							lang = langOpt[--i];
						}
					}
				}

				// If not found, use Default language. Default language is the FIRST Parameter!
				if (lang == null)
				{
					lang = langAvail.Keys[0];
				}
				//}
			}
			else
			{
				// if the current language isnt exists, then select the FIRST Parameter.
				if ((langAvail[lang] == null) || (langAvail[lang] == ""))
				{
					lang = langAvail.Keys[0];
				}
			}
			_lang = international.LocaleFactory.GetLocale(lang);
			//context.Session["lang"] = _lang.Name.ToLower();

			this.AddPairToConfig("LANGUAGE", _lang.Name.ToLower());
			string langStr = "";
			foreach (string key in langAvail)
			{
				langStr += "<a href='" + bindXmlnukeUrl(this.Site, this.Xml, this.Xsl, key) + "'>" + langAvail[key] + "</a> | ";
			}
			this.AddPairToConfig("LANGUAGESELECTOR", langStr.Substring(0, langStr.Length - 2));

			// Adjusts to Run with XMLNukeDB
			_appNameInMemory = "db_" + this.Site + "_" + this.Language.Name.ToLower();
			if (_context.Application.Get(_appNameInMemory) == null)
			{
				_xmlnukedb = new com.xmlnuke.db.XmlNukeDB(this.XmlHashedDir(), this.XmlPath, this.Language.Name.ToLower());
				_xmlnukedb.loadIndex();
			}
			else
			{
				_xmlnukedb = (db.XmlNukeDB)_context.Application.Get(_appNameInMemory);
			}

			rnd = new Random();

			if (this.ContextValue("logout") != "")
			{
				this.MakeLogout();
			}
		}

		/// <summary>
		/// Look for a param name into the HttpContext Request already processed.
		/// </summary>
		/// <param name="paramName">Param to be looked for</param>
		/// <returns>Return the param value if exists or an empty string if doesnt exists</returns>
		private string getParameter(string paramName)
		{
			if (_context.Request.Form[paramName] != null)
			{
				return _context.Request.Form[paramName];
			}
			else if (_context.Request.QueryString[paramName] != null)
			{
				return _context.Request.QueryString[paramName];
			}
			else
			{
				return "";
			}
		}

		/// <summary>
		/// Return the current XML page argument
		/// </summary>
		public string Xml
		{
			get
			{
				return _xml;
			}
			set
			{
				_xml = value;
			}
		}
		/// <summary>
		/// Return the current XSL page argument
		/// </summary>
		public string Xsl
		{
			get
			{
				return _xsl;
			}
			set
			{
				_xsl = value;
			}
		}
		/// <summary>
		/// Return the current Site page argument
		/// </summary>
		public string Site
		{
			get
			{
				return _site;
			}
			set
			{
				_site = value;
			}
		}
		/// <summary>
		/// Return the current Language page argument
		/// </summary>
		public CultureInfo Language
		{
			get
			{
				return _lang;
			}
		}
		/// <summary>
		/// Return the current Reset page argument
		/// </summary>
		public bool Reset
		{
			get
			{
				return _reset;
			}
			set
			{
				_reset = value;
			}
		}
		/// <summary>
		/// Return the current NoCache page argument
		/// </summary>
		public bool NoCache
		{
			get
			{
				return _nocache;
			}
			set
			{
				_nocache = value;
			}
		}

		/// <summary>
		/// Return the phisical directory from xmlnuke.ROOTDIR param from Web.Config file.
		/// </summary>
		private string XmlNukePath
		{
			get
			{
				if (_config["xmlnuke.USEABSOLUTEPATHSROOTDIR"] == "true")
				{
					return util.FileUtil.AdjustSlashes(_xmlnukepath) + util.FileUtil.Slash();
				}
				else
				{
					return _context.Server.MapPath(_xmlnukepath) + util.FileUtil.Slash();
				}
			}
		}

		/// <summary>
		/// Return the phisical directory from xmlnuke.ROOTDIR param from Web.Config file.
		/// </summary>
		public string SharedRootPath
		{
			get
			{
				return this.XmlNukePath + "shared" + util.FileUtil.Slash();
			}
		}

		/// <summary>
		/// Return the root directory where all sites are located.
		/// </summary>
		public string SiteRootPath
		{
			get
			{
				return this.XmlNukePath + "sites" + util.FileUtil.Slash();
			}
		}

		/// <summary>
		/// Return the root directory where the current site pages are located.
		/// </summary>
		public string CurrentSitePath
		{
			get
			{
				NameValueCollection externalSiteArray = this.getExternalSiteDir();
				string externalSite = externalSiteArray[this.Site];

				if (!String.IsNullOrEmpty(externalSite))
				{
					return externalSite + util.FileUtil.Slash();
				}
				else 
				{
					return this.SiteRootPath + this.Site + util.FileUtil.Slash();
				}
			}
		}

		/// <summary>
		/// Return the root directory where the current site XML pages are located.
		/// </summary>
		public string XmlPath
		{
			get
			{
				return this.CurrentSitePath + "xml" + util.FileUtil.Slash();
			}
		}

		/// <summary>
		/// Return the root directory where the current site XSL pages are located.
		/// </summary>
		public string XslPath
		{
			get
			{
				return this.CurrentSitePath + "xsl" + util.FileUtil.Slash();
			}
		}

		/// <summary>
		/// Return the root directory where the current site CACHE pages are located.
		/// </summary>
		public string CachePath
		{
			get
			{
				return this.CurrentSitePath + "cache" + util.FileUtil.Slash();
			}
		}

		/// <summary>
		/// Return the root directory where the current site OFFLINE pages are located.
		/// </summary>
		public string OfflinePath
		{
			get
			{
				return this.CurrentSitePath + "offline" + util.FileUtil.Slash();
			}
		}

		/// <summary>
		/// Return the virtual path from xmlnuke.URLXMLNUKEENGINE param from Web.Config file.
		/// </summary>
		public string UrlXmlNukeEngine
		{
			get
			{
				return this.joinUrlBase(_config["xmlnuke.URLXMLNUKEENGINE"]);
			}
		}

		/// <summary>
		/// Return the virtual path from xmlnuke.URLMODULE param from Web.Config file.
		/// </summary>
		public string UrlModule
		{
			get
			{
				return this.joinUrlBase(_config["xmlnuke.URLMODULE"]);
			}
		}

		/// <summary>
		/// Return the virtual path from xmlnuke.URLXMLNUKEADMIN param from Web.Config file.
		/// </summary>
		public string UrlXmlNukeAdmin
		{
			get
			{
				return this.joinUrlBase(_config["xmlnuke.URLXMLNUKEADMIN"]);
			}
		}

		public string UrlBase()
		{
			return _config["xmlnuke.URLBASE"];
		}


		public string joinUrlBase(string url)
		{
			string urlBase = this.UrlBase();
			if (urlBase != "")
			{
				if (url[0] == '/')
				{
					int i = urlBase.IndexOf('/');
					if (i >= 0)
					{
						urlBase = urlBase.Substring(0, i);
					}
				}
				else
				{
					if (urlBase[urlBase.Length - 1] != '/')
					{
						urlBase += "/";
					}
				}
			}

			return urlBase + url;
		}
	
	

		/// <summary>
		/// Return the absolute virtual path from relatives virtual paths.
		/// </summary>
		public string VirtualPathAbsolute(string relativePath)
		{
			if ((relativePath[0] == '/') || (System.Text.RegularExpressions.Regex.IsMatch(relativePath, @"^https?:\/\/")))
			{
				return relativePath;
			}

			string result = this._context.Request.Path;
			int iPath = result.LastIndexOf("/");
			if (iPath >= 0)
			{
				result = result.Substring(0, iPath);
			}

			if (relativePath[0] == '~')
			{
				return result + relativePath.Substring(1);
			}
			else
			{
				return result + "/" + relativePath;
			}
		}

		public bool showCompleteErrorMessage()
		{
			return (_config["xmlnuke.SHOWCOMPLETEERRORMESSAGES"].ToLower() == "true");
		}

		/// <summary>
		/// Access the Context collection and returns the value from a key.
		/// </summary>
		public string ContextValue(string key)
		{
			string result = _config[key];
			if (result == null)
			{
				return "";
			}
			else
			{
				return result;
			}
		}

		/// <summary>
		/// Return the languages available from xmlnuke.LANGUAGESAVAILABLE from Web.Config file.
		/// </summary>
		public NameValueCollection LanguagesAvailable()
		{
			string[] pairs = ContextValue("xmlnuke.LANGUAGESAVAILABLE").Split('|');
			NameValueCollection result = new NameValueCollection();

			foreach (string pair in pairs)
			{
				if (!String.IsNullOrEmpty(pair))
				{
					string[] values = pair.Split('=');
					result.Add(values[0], values[1]);
				}
			}

			if (result.Count == 0)
			{
				result.Add("en-us", "English");
			}
			return result;
		}

		/// <summary>
		/// Return XmlNuke version.
		/// </summary>
		public string XmlNukeVersion
		{
			get
			{
				return this._XmlNukeVersion;
			}
		}

		/// <summary>
		/// Return all exists sites and your full paths.
		/// </summary>
		public string[] ExistingSites()
		{
			string[] defaultSites = util.FileUtil.RetrieveSubFolders(this.XmlNukePath + "sites");
        
			NameValueCollection externalSite = this.getExternalSiteDir();
			if (externalSite.Keys.Count > 0)
			{
				string[] allSites = new string[defaultSites.Length + externalSite.Keys.Count];
				for (int i = 0; i < defaultSites.Length; i++)
				{
					allSites[i] = defaultSites[i];
				}
				for (int i = 0; i < externalSite.Keys.Count; i++)
				{
					allSites[defaultSites.Length + i] = externalSite.Keys[i];
				}
				return allSites;
			}
			else
			{
				return defaultSites;
			}
		 
		}

	    protected NameValueCollection _externalSiteArray = null;
    	
	    /**
	     * @return array()
	     */
		protected NameValueCollection getExternalSiteDir()
		{
			if (this._externalSiteArray == null)
			{
				this._externalSiteArray = new NameValueCollection();
				string externalSiteDir = this.ContextValue("xmlnuke.EXTERNALSITEDIR");
				if (externalSiteDir != "")
				{
					string[] externalSiteDirArray = externalSiteDir.Split('|');
					foreach (string siteItem in externalSiteDirArray)
					{
						string[] siteArray = siteItem.Split('=');
						this._externalSiteArray[siteArray[0]] = siteArray[1];
					}
				}
			}
			return this._externalSiteArray;
		}

		/// <summary>
		/// Get information about current context is authenticated.
		/// </summary>
		/// <returns>True if authenticated; false otherwise</returns>
		public bool IsAuthenticated()
		{
			// If exists more than one source of authentication, the site must have the
			// same Connection String used in the authentication, otherwise the user is not
			// valid.
			return
				((this.getSession("LoginUser") != "") &&
				  (this.ContextValue("xmlnuke.USERSDATABASE") == this.getSession("LoginConnection"))
				 );
		}

		/// <summary>
		/// Get the authenticated user name
		/// </summary>
		/// <returns></returns>
		public string authenticatedUser()
		{
			if (this.IsAuthenticated())
			{
				return this.getSession("LoginUser");
			}
			else
			{
				return "";
			}
		}

		public string authenticatedUserId()
		{
			if (this.IsAuthenticated())
			{
				return this.getSession("LoginUserId");
			}
			else
			{
				return "";
			}
		}

		public void MakeLogin(string user, string userid)
		{
			// Create the authentication ticket and store the roles in the
			// custom UserData property of the authentication ticket
			/*
			FormsAuthenticationTicket authTicket = new
			        FormsAuthenticationTicket(
		                        1,                          // version
	        	                usernamevalid,              // user name
	                	        DateTime.Now,               // creation
	                        	DateTime.Now.AddMinutes(20),// Expiration
		                        false,                      // Persistent
		                        roles );                    // User data

			// Encrypt the ticket.
				string encryptedTicket = FormsAuthentication.Encrypt(authTicket);

			// Create a cookie and add the encrypted ticket to the
			// cookie as data.
			this._context.addCookie(FormsAuthentication.FormsCookieName, encryptedTicket);

			// Redirect the user to the originally requested page
			*/
			this.setSession("LoginUser", user);
			this.setSession("LoginUserId", userid);
			this.setSession("LoginConnection", this.ContextValue("xmlnuke.USERSDATABASE"));
		}

		public void MakeLogout()
		{
			this._context.Session.Abandon();
		}

		/// <summary>
		/// Add each element from an existing collection to _config variable
		/// </summary>
		/// <param name="collection">Collection to be added</param>
		private void AddCollectionToConfig(NameValueCollection collection)
		{
			foreach (string key in collection)
			{
				this.AddPairToConfig(key, collection[key]);
			}
		}

		private void AddSessionToConfig(System.Web.SessionState.HttpSessionState collection)
		{
			foreach (string key in collection.Keys)
			{
				this.AddPairToConfig("session." + key, collection[key].ToString());
			}
		}

		private void AddCookieToConfig(System.Web.HttpCookieCollection collection)
		{
			foreach (string key in collection.Keys)
			{
				this.AddPairToConfig("cookie." + key, collection[key].ToString());
			}
		}

		/// <summary>
		/// Add a single element to _config collection
		/// </summary>
		/// <param name="key"></param>
		/// <param name="value"></param>
		private void AddPairToConfig(string key, string value)
		{
			if (_config[key] == null)
			{
				_config.Add(key, value);
			}
			else
			{
				_config[key] = value;
			}
		}

		public db.XmlNukeDB getXMLDataBase()
		{
			return _xmlnukedb;
		}

		public void persistXMLDataBaseInMemory()
		{
			_context.Application.Lock();
			try
			{
				_context.Application.Set(_appNameInMemory, _xmlnukedb);
			}
			finally
			{
				_context.Application.UnLock();
			}
		}

		public void redirectUrl(string url)
		{
			processor.ParamProcessor processor = new processor.ParamProcessor(this);
			url = processor.GetFullLink(url);
			this._context.Response.Redirect(url);
		}

		public void addCookie(string name, string value)
		{
			this.addCookie(name, value, new TimeSpan(30, 0, 0, 0), null, null);
		}

		/// <summary>
		/// Add a cookie
		/// </summary>
		/// <param name="name"></param>
		/// <param name="value"></param>
		/// <param name="expires">Expires in TimeSpan (ex. TimeSpan(0,0,1,0) )</param>
		/// <param name="path"></param>
		/// <param name="domain"></param>
		public void addCookie(string name, string value, TimeSpan expires, string path, string domain)
		{
			HttpCookie cookie = new HttpCookie(name, value);
			cookie.Expires = DateTime.Now + expires;
			if (path != null)
			{
				cookie.Path = path;
			}
			if (domain != null)
			{
				cookie.Domain = domain;
			}
			this._context.Response.Cookies.Add(cookie);
			this.AddPairToConfig("cookie." + name, value);
		}

		public void removeCookie(string name)
		{
			this._context.Response.Cookies.Remove("name");
			this._config.Remove("cookie." + name);
		}

		public string getCookie(string name)
		{
			return this.ContextValue("cookie." + name);
		}

		public void setSession(string name, string value)
		{
			if (value == null)
			{
				this.removeSession(name);
				return;
			}
			if (this._context.Session[name] == null)
			{
				this._context.Session.Add(name, value);
			}
			else
			{
				this._context.Session[name] = value;
			}
			this.AddPairToConfig("session." + name, value);
		}

		public void removeSession(string name)
		{
			this._context.Session["name"] = null;
			this._context.Session.Remove("name");
			this._config.Remove("session." + name);
		}

		public string getSession(string name)
		{
			return this.ContextValue("session." + name);
		}

		public string bindModuleUrl(string modulename)
		{
			return this.bindModuleUrl(modulename, this.Site, this.Xsl, this.Language.Name.ToLower());
		}

		public string bindModuleUrl(string modulename, string xsl)
		{
			return this.bindModuleUrl(modulename, this.Site, xsl, this.Language.Name.ToLower());
		}

		public string bindModuleUrl(string modulename, string site, string xsl, string lang)
		{
			string queryString = "";

			int queryStart = modulename.IndexOf('?');
			if (queryStart> -1)
			{
				queryString = "&" + modulename.Substring(queryStart+1);
				modulename = modulename.Substring(0, queryStart);
			}

			if (modulename.IndexOf("module:") == 0)
			{
				modulename = modulename.Substring(7);
			}
			else if (modulename.IndexOf("admin:") == 0)
			{
				modulename = "com.xmlnuke.admin." + modulename.Substring(6);
			}

			bool fullLink = (this.ContextValue("xmlnuke.USEFULLPARAMETER") == "true");
			if (!fullLink)
			{
				if (site == this.ContextValue("xmlnuke.DEFAULTSITE"))
				{
					site = "";
				}
				if (xsl == this.ContextValue("xmlnuke.DEFAULTPAGE"))
				{
					xsl = "";
				}

				NameValueCollection langAvail = this.LanguagesAvailable();
				if (lang == langAvail.Keys[0])
				{
					lang = "";
				}
			}

			string url = this.UrlModule + "?module=" + modulename.Replace("?", "&");
			url += queryString;
			if (site != "")
			{
				url += "&site=" + site;
			}
			if (xsl != "")
			{
				url += "&xsl=" + xsl;
			}
			if (lang != "")
			{
				url += "&lang=" + lang;
			}

			return url;
		}

		public string bindXmlnukeUrl(string xml)
		{
			return this.bindXmlnukeUrl(this.Site, xml, this.Xsl, this.Language.Name.ToLower());
		}

		public string bindXmlnukeUrl(string xml, string xsl)
		{
			return this.bindXmlnukeUrl(this.Site, xml, xsl, this.Language.Name.ToLower());
		}

		public string bindXmlnukeUrl(string site, string xml, string xsl, string lang)
		{
			return this.UrlXmlNukeEngine + "?site=" + site + "&xml=" + xml + "&xsl=" + xsl + "&lang=" + lang;
		}

		public void updateCustomConfig(NameValueCollection options)
		{
			processor.AnydatasetFilenameProcessor configFile = new processor.AnydatasetFilenameProcessor("customconfig", this);
			string phyFile = this.CurrentSitePath + configFile.FullQualifiedName();
			anydataset.AnyDataSet config = new anydataset.AnyDataSet(phyFile);
			anydataset.Iterator it = config.getIterator();
			if (it.hasNext())
			{
				anydataset.SingleRow sr = it.moveNext();
				config.removeRow(sr.getDomObject());
			}

			config.appendRow();
			foreach (string key in options)
			{
				if (options[key] != "")
				{
					this.AddPairToConfig(key, options[key]);
					config.addField(key, options[key]);
				}
			}
			config.Save(phyFile);
		}

		private void readCustomConfig()
		{
			//  | 
			//  |  Attention: FilenameProcessor not used because readCustomConfig is fired before 
			//  |  setting current language...  
			//  v
			//processor.AnydatasetFilenameProcessor configFile = new processor.AnydatasetFilenameProcessor("customconfig", this);
			string phyFile = this.CurrentSitePath + "customconfig.anydata.xml"; // <--- argh!!
			if (util.FileUtil.Exists(phyFile))
			{
				anydataset.AnyDataSet config = new anydataset.AnyDataSet(phyFile);
				anydataset.Iterator it = config.getIterator();
				if (it.hasNext())
				{
					anydataset.SingleRow sr = it.moveNext();
					string[] fieldNames = sr.getFieldNames();
					foreach (string field in fieldNames)
					{
						if (sr.getField(field) != "")
						{
							this.AddPairToConfig(field, sr.getField(field));
						}
					}
				}
			}
		}

		/// <summary>
		/// Get config debug in module
		/// </summary>
		/// <returns>bool</returns>
		public bool getDebugInModule()
		{
			bool configDebug = this._debug;
			bool requestDebug = (this.ContextValue("debug") == "true");
			return (configDebug && requestDebug);
		}

		/// <summary>
		/// Set debug in module with true or false
		/// </summary>
		/// <param name="debug"></param>
		public void setDebugInModule(bool debug)
		{
			this._debug = debug;
		}

		public int getRandomNumber(int maxValue)
		{
			return rnd.Next(maxValue);
		}

		public ArrayList getRequestedParams()
		{
			return this._requestedParams;
		}

		public string SystemRootPath()
		{
			string modulename = this._context.Server.MapPath(this._context.Request.ServerVariables["PATH_INFO"]);
			string path = util.FileUtil.ExtractFilePath(modulename);
			return path + util.FileUtil.Slash();
		}

		public ArrayList getUploadFileNames()
		{
			throw new Exception("getUploadFileNames not implemented!!");
		}

		/// <summary>
		/// Process a document Upload. 
		/// Original code from: Harry Kimpel
		/// Article: Upload Files with ASP.NET/HTML Forms and Pure ASP.NET using C#
		/// </summary>
		/// <param name="filenameProcessor"></param>
		/// <returns></returns>
		public ArrayList processUpload(processor.UploadFilenameProcessor filenameProcessor, bool useProcessorForName)
		{
			return this.processUpload(filenameProcessor, useProcessorForName, null);
		}
		public ArrayList processUpload(processor.UploadFilenameProcessor filenameProcessor, bool useProcessorForName, string field)
		{
			ArrayList result = new ArrayList();

			string sPostData = "";
			// get form-data
			byte[] biData = this._context.Request.BinaryRead(this._context.Request.TotalBytes);

			// convert byte-array to its string representation
			sPostData = System.Text.Encoding.Default.GetString(biData, 0, biData.Length);

			// we get the content type and try to pull out the boundary information
			string sContentType = this._context.Request.ContentType;
			string[] arrContentType = sContentType.Split(new char[] { ';' });

			if (arrContentType[0] == "multipart/form-data")
			{
				// parse the boundary information
				string[] arrBoundary = arrContentType[1].Trim().Split(new char[] { '=' });
				string sBoundary = "--" + arrBoundary[1].Trim();

				// on a Windows98/IE5.0x machine the boundary ends with the enctype,
				// so we just cut that off
				if (sBoundary.IndexOf(",multipart/form-data") >= 0)
				{
					sBoundary = sBoundary.Replace(",multipart/form-data", "");
				}

				// loop for every item in the forms collection (enclosed by the boundary)
				string sTemp = sPostData;
				int iStartBoundary = sTemp.IndexOf(sBoundary, 0);
				int i = 0;
				bool bEndForm = false;
				if (iStartBoundary >= 0)
				{
					// start: loop for form items
					do
					{
						string sFormField = "";
						string sFormValue = "";

						// parse the item's name and general information
						int iStartField = iStartBoundary + sBoundary.Length + 2;
						string sCRLF = sTemp.Substring(iStartBoundary + sBoundary.Length, 2);
						int iStartCRLF = sTemp.IndexOf(sCRLF, iStartField);
						int iStartNextBoundary = sTemp.IndexOf(sBoundary, iStartField);
						sFormField = sTemp.Substring(iStartField, iStartCRLF - iStartField);

						// is the current item the <input type="file">?
						if (sFormField.IndexOf("filename=") >= 0)
						{
							// then the next line contains the content-type of the uploaded file
							int iStartFieldContentType = iStartCRLF + 2;
							iStartCRLF = sTemp.IndexOf(sCRLF, iStartFieldContentType);
							string sFieldContentType = sTemp.Substring(iStartFieldContentType, iStartCRLF - iStartFieldContentType);

							sFormField += "; " + sFieldContentType;
						}

						// parse the item's value
						int iStartValue = iStartCRLF + 4;
						iStartCRLF = sTemp.IndexOf(sCRLF, iStartValue);
						int iEndValue = iStartNextBoundary - iStartValue - 2;
						sFormValue = sTemp.Substring(iStartValue, iEndValue);

						// is the current item the <input type="file">?
						if (sFormField.IndexOf("filename=") >= 0 && (String.IsNullOrEmpty(field) || (!String.IsNullOrEmpty(field) && sFormField.IndexOf(" name=\"" + field + "\"") >= 0)))
						{
							string sFileName;

							if (!useProcessorForName)
							{
								// parse the name of the uploaded file (without the path)
								int iStartFileName = sFormField.IndexOf("filename=");
								sFileName = sFormField.Substring(iStartFileName + 10, sFormField.Length - iStartFileName - 10);
								int iEndFileName = sFileName.IndexOf(";");
								sFileName = sFileName.Substring(0, iEndFileName - 1);
								int iPos = sFileName.LastIndexOf("\\");
								iPos++;
								sFileName = sFileName.Substring(iPos, sFileName.Length - iPos);
							}
							else
							{
								sFileName = filenameProcessor.FullQualifiedName();
							}

							// parse the content-type of the uploaded file
							//int iStartFileContentType = sFormField.IndexOf("Content-Type:");
							//string sFileContentType = sFormField.Substring(iStartFileContentType + 14, sFormField.Length - iStartFileContentType - 14);

							// get the binary data of the uploaded file
							byte[] bFileContent = System.Text.Encoding.Default.GetBytes(sFormValue);

							// we could save the uploaded file here
							if (sFileName.Trim() != "")
							{
								string path = filenameProcessor.PathSuggested();
								System.IO.Directory.CreateDirectory(path);
								result.Add(path + util.FileUtil.Slash() + sFileName);
								System.IO.FileStream fs = new System.IO.FileStream(path + util.FileUtil.Slash() + sFileName, System.IO.FileMode.Create, System.IO.FileAccess.Write);
								try
								{
									fs.Write(bFileContent, 0, bFileContent.Length);
								}
								finally
								{
									fs.Close();
								}
							}
						}

						// cut off the current item from the rest of the post data
						sTemp = sTemp.Substring(iStartValue + iEndValue + 2, sTemp.Length - iStartValue - iEndValue - 2);

						// look for the next item
						iStartBoundary = sTemp.IndexOf(sBoundary, 0);

						// the last item ends with two additional hyphens
						if (sTemp.Substring(0, sBoundary.Length + 2) == sBoundary + "--")
						{
							bEndForm = true;
						}

						// increment the counter
						i++;
					} while (iStartBoundary >= 0 && !bEndForm);
					// end of: loop for form items
				}
			}

			return result;
		}

		public string getSuggestedContentType()
		{
			if (this._contentType == "")
			{
				string contentType = "text/html";
				if (this.ContextValue("xmlnuke.CHECKCONTENTTYPE") == "true")
				{
					processor.AnydatasetSetupFilenameProcessor filename = new processor.AnydatasetSetupFilenameProcessor("contenttype", this);
					AnyDataSet anydataset = new AnyDataSet(filename);
					IteratorFilter itf = new IteratorFilter();
					itf.addRelation("xsl", Relation.Equal, this.Xsl);
					IIterator it = anydataset.getIterator(itf);
					if (it.hasNext())
					{
						SingleRow sr = it.moveNext();
						contentType = sr.getField("content-type");
					}
				}
				this._contentType = (contentType == "") ? "text/html" : contentType;
			}
			return (this._contentType);
		}

		public string[] getAllFormKeys()
		{
			return this._context.Request.Form.AllKeys;
		}

		public void Debug()
		{
			util.Debug.Print(this._config);
		}

		public bool CacheHashedDir()
		{
			return (this.ContextValue("xmlnuke.CACHESTORAGEMETHOD").ToUpper() == "HASHED");
		}

		public bool XmlHashedDir()
		{
			return (this.ContextValue("xmlnuke.XMLSTORAGEMETHOD").ToUpper() == "HASHED");
		}

		public string[] getPostVariables()
		{
			return HttpContext.Current.Request.Form.AllKeys;
		}
	}
}
