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
using System.Collections;
using System.Collections.Specialized;
using com.xmlnuke.module;
using com.xmlnuke.international;
using com.xmlnuke.classes;
using com.xmlnuke.processor;

namespace com.xmlnuke.admin
{
	/// <summary>
	/// Summary description for com.
	/// </summary>
	public class ConfigSearch : NewBaseAdminModule
	{
		public ConfigSearch()
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
			
			LanguageCollection myWords = this.WordCollection();

			XmlBlockCollection block = new XmlBlockCollection(myWords.Value("TITLE"), BlockPosition.Center);
			this.setTitlePage(myWords.Value("TITLE"));
			this.setHelp(myWords.Value("DESCRIPTION"));
			this.defaultXmlnukeDocument.addXmlnukeObject(block);
			
			// configSearch File
			AnydatasetFilenameProcessor configSearchFile = new AnydatasetFilenameProcessor("_configsearch", this._context);

			ProcessPageFields fields = new ProcessPageFields();

			ProcessPageField field = ProcessPageFields.Factory("alias", myWords.Value("NODEALIAS"), 20, true, true);
			field.key = true;
			field.dataType = INPUTTYPE.UPPERASCII;
			fields.addProcessPageField(field);
			
			field = ProcessPageFields.Factory("nodetitle", myWords.Value("NODETITLE"), 20, true, true);
			field.maxLength = 500;
			fields.addProcessPageField(field);
			
			field = ProcessPageFields.Factory("nodeabstract", myWords.Value("NODEABSTRACT"), 20, true, true);
			field.maxLength = 500;
			fields.addProcessPageField(field);

			ProcessPageStateAnydata processor = new ProcessPageStateAnydata(this._context, fields, myWords.Value("TITLE"), "admin:configsearch", null, configSearchFile);
			block.addXmlnukeObject(processor);

			XmlParagraphCollection p = new XmlParagraphCollection();
			p.addXmlnukeObject(new XmlnukeText(myWords.Value("NOTE")));
			block.addXmlnukeObject(p);
			
			return this.defaultXmlnukeDocument;		
		}

	}
}