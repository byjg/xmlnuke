/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  CSharp Specification: Joao Gilberto Magalhaes, joao at byjg dot com
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

using com.xmlnuke.classes;
using com.xmlnuke.module;
using com.xmlnuke.anydataset;
using com.xmlnuke.processor;
using com.xmlnuke.international;
using com.xmlnuke.util;
using System.Collections.Generic;

namespace com.xmlnuke.admin
{
	abstract public class NewBaseAdminModule : BaseModule, IAdmin
	{
		protected XmlBlockCollection _mainBlock;

		protected XmlParagraphCollection _help;

		protected XmlParagraphCollection _menu;

		public NewBaseAdminModule()
		{ }

		override public IXmlnukeDocument CreatePage()
		{
			this._mainBlock = new XmlBlockCollection("Menu", BlockPosition.Center);
			this._help = new XmlParagraphCollection();
			//this._menu = new XmlParagraphCollection();
			this._mainBlock.addXmlnukeObject(this._help);
			//this._mainBlock.addXmlnukeObject(this._menu);
			this.defaultXmlnukeDocument.addXmlnukeObject(this._mainBlock);
			//XmlnukeManageUrl url = new XmlnukeManageUrl(URLTYPE.ADMIN, "");
			//url.addParam("site", this._context.Site);
			//XmlAnchorCollection link = new XmlAnchorCollection(url.getUrl(), "");
			//link.addXmlnukeObject(new XmlnukeText("Menu"));
			//this._menu.addXmlnukeObject(link);
			//this.defaultXmlnukeDocument.setMenuTitle("Menu");
			LanguageCollection lang = this.CreateMenuAdmin();

            this.defaultXmlnukeDocument.PageTitle = "XMLNuke";
            this.defaultXmlnukeDocument.Abstract = lang.Value("CONTROLPANEL_TITLE");
            this.defaultXmlnukeDocument.addMetaTag("controlpaneltitle", lang.Value("CONTROLPANEL_TITLE"));

            return null;
		}

		override public sealed bool requiresAuthentication()
		{
			return true;
		}

        override public international.LanguageCollection WordCollection()
        {
            if (this._words == null)
            {
                this._words = LanguageFactory.GetLanguageCollection(this._context, LanguageFileTypes.ADMINMODULE, this._moduleName);
            }
            return this._words;
        }
        
        public void admin()
		{ }

		protected void addMenuOption(string strMenu, string strLink)
		{
			this.defaultXmlnukeDocument.addMenuItem(strLink, strMenu, "");
		}

		protected void addMenuOption(string strMenu, string strLink, string target)
		{
			this.defaultXmlnukeDocument.addMenuItem(strLink, strMenu, "");
		}

		protected void setHelp(string strHelp)
		{
			this._help.addXmlnukeObject(new XmlnukeText(strHelp));
		}

		protected void setTitlePage(string strTitle)
		{
			this._mainBlock.setTitle(strTitle);
		}


		protected bool isUserAdmin()
		{
			IUsersBase user = this.getUsersDatabase();
			com.xmlnuke.anydataset.SingleRow sr = user.getUserId(this._context.authenticatedUserId());
			return (sr.getField(user.getUserTable().Admin) == "yes");
		}


		protected IIterator GetAdminGroups()
		{
			return this.GetAdminGroups("");
		}
		protected IIterator GetAdminGroups(string group)
		{
			string[] keys;
			if (!String.IsNullOrEmpty(group))
			{
                if (this.GetAdminModulesList().ContainsKey(group))
                {
                    keys = new string[] { group };
                }
                else
                {
                    keys = new string[] {};
                }
			}
			else
			{
                keys = new string[this.GetAdminModulesList().Keys.Count];
                int i = 0;
                foreach (string val in this.GetAdminModulesList().Keys)
                {
                    keys[i++] = val;
                }
			}

            ArrayDataSet arr = new ArrayDataSet(keys, "name");
            return arr.getIterator();
		}

		protected Dictionary<string, string[]> GetAdminModules(string group)
		{
            Dictionary<string,Dictionary<string, string[]>> arr = this.GetAdminModulesList();
            return arr[group];
        }

        protected Dictionary<string,Dictionary<string, string[]>> _adminModulesList = null;


        protected Dictionary<string, Dictionary<string, string[]>> GetAdminModulesList()
		{
            if (this._adminModulesList == null)
            {
                this._adminModulesList = new Dictionary<string,Dictionary<string, string[]>>();

                string rowNode = "group/module";
			    NameValueCollection colNode = new NameValueCollection();
                colNode["group"] = "../@name";
                colNode["name"] = "@name";
			    colNode["icon"] = "icon";
			    colNode["url"] = "url";
                colNode["command"] = "@command";

                AdminModulesXMLFilenameProcessor xmlProcessor = new AdminModulesXMLFilenameProcessor(this._context);
                for (int i = 0; i < 2; i++)
                {
                    if (i == 0)
                    {
                        xmlProcessor.FilenameLocation = ForceFilenameLocation.SharedPath;
                    }
                    else
                    {
                        xmlProcessor.FilenameLocation =ForceFilenameLocation.PrivatePath;
                    }

                    string configFile = xmlProcessor.FullQualifiedNameAndPath();
                    if (FileUtil.Exists(configFile))
                    {
                        string config = FileUtil.QuickFileRead(configFile);
                        XmlDataSet dataset = new XmlDataSet(this._context, config, rowNode, colNode);
                        foreach (SingleRow sr in dataset.getIterator())
                        {
                            if (!this._adminModulesList.ContainsKey(sr.getField("group")))
                            {
                                if (sr.getField("command") != "@hidegroup")
                                {
                                    this._adminModulesList[sr.getField("group")] = new Dictionary<string, string[]>();
                                }
                                else
                                {
                                    this._adminModulesList.Remove(sr.getField("group"));
                                }
                            }
                            this._adminModulesList[sr.getField("group")][sr.getField("name")] = new string[] { sr.getField("icon"), sr.getField("url") };
                        }
                    }
                }
            }

            return this._adminModulesList;
		}


		protected LanguageCollection CreateMenuAdmin()
		{
			// Load Language file for Module Object
			LanguageCollection lang = LanguageFactory.GetLanguageCollection(this._context, LanguageFileTypes.ADMININTERNAL, null);

			// Create a Menu Item for GROUPS and MODULES. 
			// This menu have CP_ before GROUP NAME
			IIterator itGroup = this.GetAdminGroups();

			while (itGroup.hasNext())
			{
				SingleRow srGroup = itGroup.moveNext();
				this.defaultXmlnukeDocument.addMenuGroup(lang.Value("GROUP_" + srGroup.getField("name").ToUpper()), "CP_" + srGroup.getField("name"));

                Dictionary<string, string[]> arrModule = this.GetAdminModules(srGroup.getField("name"));

				foreach (string name in arrModule.Keys)
				{
                    string url = arrModule[name][1];
                    string icon = arrModule[name][0];

                    if (!String.IsNullOrEmpty(url))
                    {
                        this.defaultXmlnukeDocument.addMenuItem(
                            url,
                            lang.Value("MODULE_TITLE_" + name.ToUpper()),
                            lang.Value("MODULE_ABSTRACT_" + name.ToUpper()),
                            "CP_" + srGroup.getField("name"),
                            icon);
                    }
				}
			}

            return lang;
		}

	}
}