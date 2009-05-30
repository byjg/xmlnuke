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
using com.xmlnuke.module;

namespace com.xmlnuke.admin
{
	/// <summary>
	/// Summary description for com.
	/// </summary>
	public class ManageCache : BaseAdminModule
	{
		public ManageCache()
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
			return new string[] { "MANAGER", "OPERATOR" };
		}

		override public classes.IXmlnukeDocument CreatePage()
		{
			base.CreatePage(); // Doesnt necessary get PX, because PX is protected!

			this.setHelp("Erase all cache from current site.");
			//this.addMenuOption("OK", "admin:ManageGroup?action=aqui");
			this.setTitlePage("Manage Cache System");
			this.addMenuOption("Click here to ERASE ALL cache.", "admin:ManageCache?action=erase");
			this.addMenuOption("Click here to LIST cache.", "admin:ManageCache?action=list");

			string action = this._action.ToLower();

			XmlNode block = px.addBlockCenter("Working Area");
			XmlNode paragraph;

			if (action == "erase")
			{
				util.FileUtil.DeleteFilesFromPath(this._cacheFile);
				util.FileUtil.DeleteFilesFromPath(new processor.XSLCacheFilenameProcessor("", this._context));
				paragraph = px.addParagraph(block);
				px.addBold(paragraph, "All cache is erased.");
			}

			if (action == "list")
			{
				string[] filelist = util.FileUtil.RetrieveFilesFromFolder(this._cacheFile.PathSuggested(), "*.*");
				paragraph = px.addUnorderedList(block);
				XmlNode item;
				foreach (string file in filelist)
				{
					item = px.addOptionList(paragraph);
					px.addText(item, util.FileUtil.ExtractFileName(file));
				}
			}

			return px;
		}

	}
}