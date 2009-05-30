/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
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

using com.xmlnuke.classes;
using com.xmlnuke.module;
using com.xmlnuke.anydataset;
using com.xmlnuke.processor;
using com.xmlnuke.international;
using com.xmlnuke.util;


namespace com.xmlnuke.admin
{

	public class ControlPanel : NewBaseAdminModule
	{
		protected string _group = "";
		public string Group
		{
			set { this._group = value; }
			get { return this._group; }
		}

		public ControlPanel()
		{
		}

		public override bool useCache()
		{
			return false;
		}
		public override AccessLevel getAccessLevel()
		{
			return AccessLevel.OnlyAuthenticated;
		}

		public override IXmlnukeDocument CreatePage()
		{
			if (this._context.ContextValue("logout") != "")
			{
				this._context.redirectUrl("admin:controlpanel");
			}

			base.CreatePage();
			LanguageCollection mywords = this.WordCollection();

			this.bindParameteres();

			this.setTitlePage(mywords.Value("CONTROLPANEL"));
			this.setHelp(mywords.Value("CONTROLPANEL_HELP"));

			IIterator it = this.GetAdminGroups(this.Group);
			if (it.hasNext())
			{
				SingleRow sr = it.moveNext();

				XmlnukeStringXML xmlObj = new XmlnukeStringXML("<listmodules group=\"CP_" + sr.getField("name") + "\" />");
				this.defaultXmlnukeDocument.addXmlnukeObject(xmlObj);
				//Debug.PrintValue(sr.getField("name"));
			}
			else
			{
				throw new Exception("Admin Group not found!");
			}

			XmlBlockCollection block = new XmlBlockCollection(mywords.Value("BLOCKINFO_TITLE"), BlockPosition.Center);
			XmlParagraphCollection paragraph = new XmlParagraphCollection();
			block.addXmlnukeObject(paragraph);

			paragraph.addXmlnukeObject(new XmlnukeText(mywords.Value("INFO_USER", new string[] { this._context.authenticatedUser(), this._context.authenticatedUserId() }), false, false, false, true));
			paragraph.addXmlnukeObject(new XmlnukeText(mywords.Value("INFO_SITE", this._context.Site), false, false, false, true));

			this.defaultXmlnukeDocument.addXmlnukeObject(block);

			return this.defaultXmlnukeDocument.generatePage();
		}

	}
}