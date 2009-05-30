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
	public class SelectLanguage : BaseAdminModule
	{
		public SelectLanguage()
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

			this.setHelp("Use this option to select a language which admin tool will be working.");
			this.setTitlePage("Select the working language");

			XmlNode block = px.addBlockCenter("Selection Area");
			XmlNode paragraph;
			XmlNode list;
			XmlNode optlist;

			if (this._action == "createrepo")
			{
				ManageSites.createRepositoryForSite(this._context.XmlHashedDir(), "sample.en-us.xsl", this._context.CurrentSitePath, this._context.ContextValue("destlang"), this._context);
				paragraph = px.addParagraph(block);
				px.addBold(paragraph, "Created.");
			}


			// Show Availables Languages
			paragraph = px.addParagraph(block);
			px.addText(paragraph, "Select language from Languages Available below. Current Language: [param:lang]");
			list = px.addUnorderedList(paragraph);

			System.Collections.Specialized.NameValueCollection langAvail = _context.LanguagesAvailable();
			foreach (string key in langAvail)
			{
				optlist = px.addOptionList(list);
				px.addText(optlist, " [ ");
				px.addHref(optlist, "admin:engine?lang=" + key, "Select");
				px.addText(optlist, " | ");
				px.addHref(optlist, "admin:SelectLanguage?destlang=" + key + "&action=createrepo", "Create Repository");
				px.addText(optlist, " ] ");
				px.addText(optlist, key + ": " + langAvail[key]);
			}

			return px;
		}

	}
}