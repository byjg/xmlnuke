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
	public class ManageXMLDBIndex : BaseAdminModule
	{
		public ManageXMLDBIndex()
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

			this.setHelp("XMLDB Index enable you search any word existing in XML documents. Warning: This operation may take several minutes");
			//this.addMenuOption("OK", "admin:ManageGroup?action=aqui");
			this.setTitlePage("Manage XMLDB Index");

			string action = this._action.ToLower();

			XmlNode block = px.addBlockCenter("Working Area");
			XmlNode paragraph;

			// Delete a Group Node
			if (action == "")
			{
				paragraph = px.addParagraph(block);
				px.addHref(paragraph, "admin:ManageXMLDBIndex?action=recreate", "Click here to recreate XMLDB " + this._context.Language.Name.ToLower() + " Index.");
			}

			if (action == "recreate")
			{
				this._context.getXMLDataBase().recreateIndex();
				this._context.getXMLDataBase().saveIndex();
				paragraph = px.addParagraph(block);
				px.addBold(paragraph, "Index was created");
			}

			return px;
		}

	}
}