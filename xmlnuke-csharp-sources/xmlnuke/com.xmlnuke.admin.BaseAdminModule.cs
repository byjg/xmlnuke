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
	/// This class inherits from BaseModule and implements some functions to aid the 
	/// development of admin modules.
	/// </summary>
	/// <seealso cref="T:com.xmlnuke.module.BaseModule">BaseModule</seealso>
	public abstract class BaseAdminModule : com.xmlnuke.module.BaseModule, com.xmlnuke.admin.IAdmin
	{

		protected classes.PageXml px;
		protected XmlNode mainBlock;
		private XmlNode help;
		private XmlNode menu;

		/// <summary>
		/// BaseAdminModule Constructor
		/// </summary>
		public BaseAdminModule()
		{ }

		public void admin()
		{ }

		/// <summary>
		/// Implements some base XML options used for ALL Admin Modules.
		/// </summary>
		/// <returns>XML object</returns>
		override public classes.IXmlnukeDocument CreatePage()
		{
			px = new classes.PageXml();

			px.Title = "XMLNuke Administration Tool";
			px.Abstract = "XMLNuke Administration Tool";

			mainBlock = px.addBlockCenter("Menu");
			help = px.addParagraph(mainBlock);
			menu = px.addParagraph(mainBlock);
			px.addHref(menu, "admin:engine?site=" + this._context.Site + "&lang=" + this._context.Language, "Menu");

			return px;
		}

		/// <summary>
		/// Admin Modules always requires authentication. This method is sealed.
		/// </summary>
		/// <returns>True</returns>
		override public sealed bool requiresAuthentication()
		{
			return true;
		}

		protected void addMenuOption(string strMenu, string strLink)
		{
			px.addMenuItem(strLink, strMenu, "");
		}

		protected void addMenuOption(string strMenu, string strLink, string target)
		{
			px.addMenuItem(strLink, strMenu, "");
		}

		protected void setHelp(string strHelp)
		{
			px.addText(help, strHelp);
		}

		protected void setTitlePage(string strTitle)
		{
			XmlNode tit = px.getDomObject().SelectSingleNode("page/blockcenter[title='Menu']/title");
			px.Title = px.Title + " - " + strTitle;
			px.Abstract = px.Title;
			tit.InnerText = strTitle;
		}

	}
}