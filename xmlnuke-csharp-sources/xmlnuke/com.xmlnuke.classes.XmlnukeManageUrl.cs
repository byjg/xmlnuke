/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  C# Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
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
using System.Collections.Specialized;
using com.xmlnuke;
using com.xmlnuke.engine;


namespace com.xmlnuke.classes
{
	public enum URLTYPE
	{
		ADMIN,
		ADMINENGINE,
		ENGINE,
		MODULE,
		HTTP,
		FTP,
		JAVASCRIPT
	}

	/**
	*this Class manager url to Xmlnuke engine
	*@package com.xmlnuke
	*/
	public class XmlnukeManageUrl
	{

		protected URLTYPE _urltype;
		protected string _target;
		private NameValueCollection _parameters;

		public XmlnukeManageUrl(URLTYPE urltype)
			: this(urltype, "")
		{ }

		/**
		*@param URLTYPE urltype
		*@param string target
		*@desc XmlnukeManageUrl Constructor 
		*If URLTYPE is MODULE target must to be the Module name target
		*If URLTYPE is ENGINE or ADMIN target must to be NULL
		*If URLTYPE is HTTP target must to be the full URL whitout "http://"
		*If URLTYPE is JAVASCRIPT target must to be the javascript command whitout "http://javascript:"
		*/
		public XmlnukeManageUrl(URLTYPE urltype, string target)
		{
			this._parameters = new NameValueCollection();
			this._urltype = urltype;
			string[] arr = target.Split('?');
			this._target = arr[0];
			if (arr.Length == 2)
			{
				string[] parameters = arr[1].Split('&');
				foreach (string value in parameters)
				{
					string[] paramPart = value.Split('=');
					if (paramPart.Length == 2)
					{
						this.addParam(paramPart[0], paramPart[1]);
					}
					else if (paramPart[0] != "")
					{
						this.addParam(paramPart[0], "");
					}
				}
			}
		}


		/**
		*@desc Add a param to url
		*@param string param
		*@param string value
		*@return void
		*/
		public void addParam(string param, string value)
		{
			if (this._parameters[param] == null)
			{
				this._parameters.Add(param, value);
			}
			else
			{
				this._parameters[param] = value;
			}
		}

		public void addParam(string param, int value)
		{
			this.addParam(param, value.ToString());
		}

		/**
		*@desc Build URL link based on xmlnuke model. 
		*@desc Note: target must be the follow values:
		*@desc  - site if URLTYPE is equal to ENGINE or ADMIN
		*@desc  - module is URLTYPE is equal to MODULE
		*@desc  - Full URL (without protocol) if any other.
		*@return string
		*/
		public string getUrl()
		{
			string url = "";

			if (this._urltype == URLTYPE.ENGINE || this._urltype == URLTYPE.ADMINENGINE)
			{
				url = this.getUrlPrefix(this._urltype);
			}
			else
			{
				url = this._target;

				if (this._urltype == URLTYPE.MODULE || this._urltype == URLTYPE.JAVASCRIPT || this._urltype == URLTYPE.ADMIN)
				{
					string urlPrefix = this.getUrlPrefix(this._urltype);
					if (!url.StartsWith(this.getUrlPrefix(URLTYPE.ADMIN)) && (url.IndexOf(urlPrefix) == -1))
					{
						url = urlPrefix + url;
					}
				}
			}

			char separator = (this._target.Contains("?") ? '&' : '?');

			int count = 0;
			foreach (string param in this._parameters)
			{
				if (count > 0)
				{
					separator = '&';
				}
				count++;
				url += separator + param + '=' + XmlnukeManageUrl.encodeParam(this._parameters[param]);
			}
			//return url.Replace("&", "&amp;");
			return url;
		}

		/**
		*@param Context context
		*@return string
		*@desc Build full URL.
		*/
		public string getUrlFull(Context context)
		{
			string parameter = "";
			string separator = "";
			string xml = "";
			string xsl = "";
			string site = "";
			string lang = "";

			int count = 0;

			foreach (string param in this._parameters)
			{
				if (count > 0)
				{
					separator = "&";
				}
				string value = this._parameters[param];

				if (param == "xml")
				{
					xml = value;
				}
				else if (param == "xsl")
				{
					xsl = value;
				}
				else if (param == "site")
				{
					site = value;
				}
				else if (param == "lang")
				{
					lang = value;
				}
				else
				{
					parameter += separator + param + '=' + XmlnukeManageUrl.encodeParam(value);
					count++;
				}
			}

			string fullurl = "";

			switch (this._urltype)
			{
				case URLTYPE.ENGINE:
					{
						fullurl = context.bindXmlnukeUrl(site, xml, xsl, lang);
						break;
					}
				case URLTYPE.ADMINENGINE:
					{
						fullurl = context.UrlXmlNukeAdmin + ((parameter != "") ? "?" : "") + parameter;
						break;
					}
				case URLTYPE.ADMIN:
				case URLTYPE.MODULE:
					{
						fullurl = context.bindModuleUrl(this._target + ((parameter != "") ? "?" : "") + parameter, site, xsl, lang);
						break;
					}
				default:
					{
						if (!this._target.Contains(this.getUrlPrefix(this._urltype)))
						{
							fullurl = this.getUrlPrefix(this._urltype) + context.ContextValue("HTTP_HOST") + this._target;
						}
						else
						{
							fullurl = this._target;
						}
						fullurl += (fullurl.Contains("?") ? "&" : "?") + parameter;
						break;
					}
			}

			return fullurl;

		}
		/**
		*@param string param
		*@return string
		*@desc Build the full URL link
		*/
		public static string encodeParam(string param)
		{
			if (!string.IsNullOrEmpty(param))
			{
				// Maybe I use UrlEncode
				return param.Replace("/", "%2f").Replace("?", "%3f").Replace("&", "%26").Replace("=", "%3d");
			}
			else
			{
				return "";
			}
		}

		/**
		* @param string parameter
		* @access public
		* @return string
		*/
		public static string decodeParam(string param)
		{
			// Maybe I use UrlDecode
			return param.Replace("%2f", "/").Replace("%3f", "?").Replace("%26", "&").Replace("%3d", "=");
		}

		private string getUrlPrefix(URLTYPE url)
		{
			string ret = "";
			switch (url)
			{
				case URLTYPE.ADMIN:
					{
						ret = "admin:";
						break;
					}
				case URLTYPE.ADMINENGINE:
					{
						ret = "admin:engine";
						break;
					}
				case URLTYPE.ENGINE:
					{
						ret = "engine:xmlnuke";
						break;
					}
				case URLTYPE.MODULE:
					{
						ret = "module:";
						break;
					}
				case URLTYPE.HTTP:
					{
						ret = "http://";
						break;
					}
				case URLTYPE.FTP:
					{
						ret = "ftp://";
						break;
					}
				case URLTYPE.JAVASCRIPT:
					{
						ret = "javascript:";
						break;
					}
			}

			return ret;
		}

	}
}