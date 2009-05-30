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
using com.xmlnuke.anydataset;

namespace com.xmlnuke.admin
{
	/// <summary>
	/// Summary description for com.
	/// </summary>
	public class Download : NewBaseAdminModule
	{
		public Download()
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
			return new string[] { "MANAGER", "EDITOR" };
		}

		override public classes.IXmlnukeDocument CreatePage()
		{
			base.CreatePage(); // Doesnt necessary get PX, because PX is protected!

			LanguageCollection myWords = this.WordCollection(); 
			bool forceReset = false;

			// Get parameters to define type of edit
			string catId = this._context.ContextValue("ci");
			string type = this._context.ContextValue("t");
			if (type == "") 
			{
				if (this._action == ProcessPageStateBase.ACTION_VIEW)
				{
					type = "FILE";
					catId = this._context.ContextValue("valueid");
					string[] catIdAr = catId.Split('|');
					catId = catIdAr[1];
					forceReset = true;
				}
				else
				{
					type = "CATEGORY";
				}
			}

			XmlBlockCollection block = new XmlBlockCollection(myWords.Value("TITLE"), BlockPosition.Center);
			this.setTitlePage(myWords.Value("TITLE"));
			this.setHelp(myWords.Value("DESCRIPTION"));
			this.defaultXmlnukeDocument.addXmlnukeObject(block);
			
			// Download File
			AnydatasetFilenameProcessor downloadFile = new AnydatasetFilenameProcessor("_download", this._context);

			ProcessPageFields fields = new ProcessPageFields();

			// Create Process Page Fields
			ProcessPageField field = ProcessPageFields.Factory("TYPE", myWords.Value("FORMTYPE"), 20, false, true);
			field.key = true;
			field.editable = false;
			field.dataType = INPUTTYPE.UPPERASCII;
			field.defaultValue = type;
			fields.addProcessPageField(field);
		
			field = ProcessPageFields.Factory("cat_id", myWords.Value("FORMCATEGORY"), 20, true, true);
			field.key = (type == "CATEGORY");
			field.editable = (type == "CATEGORY");
			field.defaultValue = catId;
			fields.addProcessPageField(field);
			
			if (type == "FILE")
			{
				field = ProcessPageFields.Factory("file_id", myWords.Value("FORMFILE"), 20, true, true);
				field.key = (type == "FILE");
				fields.addProcessPageField(field);
			}
			
			field = ProcessPageFields.Factory("name", myWords.Value("LABEL_NAME"), 20, true, true);
			field.maxLength = 40;
			fields.addProcessPageField(field);

			field = ProcessPageFields.Factory("description", myWords.Value("FORMDESCRIPTION"), 40, true, true);
			field.maxLength = 500;
			fields.addProcessPageField(field);

			NameValueCollection langs = this._context.LanguagesAvailable();
			foreach (string key in langs)
			{
				string desc = langs[key];
				field = ProcessPageFields.Factory("name_" + key, myWords.Value("LABEL_NAME") + desc, 20, false, false);
				field.maxLength = 40;
				fields.addProcessPageField(field);
			
				field = ProcessPageFields.Factory("description_" + key, myWords.Value("FORMDESCRIPTION") + desc, 40, false, false);
				field.maxLength = 500;
				fields.addProcessPageField(field);
			}
			
			if (type == "FILE")
			{
				field = ProcessPageFields.Factory("url", myWords.Value("FORMURL"), 40, true, true);
				field.maxLength = 40;
				fields.addProcessPageField(field);
				
				field = ProcessPageFields.Factory("seemore", myWords.Value("FORMSEEMORE"), 40, false, true);
				field.maxLength = 40;
				fields.addProcessPageField(field);
				
				field = ProcessPageFields.Factory("emailto", myWords.Value("FORMEMAILTO"), 40, false, true);
				field.maxLength = 50;
				field.dataType = INPUTTYPE.EMAIL;
				fields.addProcessPageField(field);
			}
			
			// Write custom message
			if ((this._action == ProcessPageStateBase.ACTION_LIST) || (this._action == ProcessPageStateBase.ACTION_MSG) || forceReset)
			{
				XmlParagraphCollection p = new XmlParagraphCollection();
				if (type == "CATEGORY")
				{
					p.addXmlnukeObject(new XmlnukeText(myWords.Value("NOTE_CATEGORY")));
				}
				else
				{
					XmlAnchorCollection href = new XmlAnchorCollection("admin:download");
					href.addXmlnukeObject(new XmlnukeText(myWords.Value("NOTE_FILE", catId)));
					p.addXmlnukeObject(href);
				}
				block.addXmlnukeObject(p);
			}
			
			// Show Process Page State
			IteratorFilter itf = new IteratorFilter();
			itf.addRelation("TYPE", Relation.Equal, type);
			if (type == "FILE")
			{
				itf.addRelation("cat_id", Relation.Equal, catId);
			}

			ProcessPageStateAnydata processor = new ProcessPageStateAnydata(this._context, fields, myWords.Value("TITLE_" + type, catId), "admin:download", null, downloadFile, itf);
			if (forceReset)
			{
				processor.forceCurrentAction(ProcessPageStateBase.ACTION_LIST);
			}
			if (type == "FILE")
			{
				processor.addParameter("t", type);
				processor.addParameter("ci", catId);
			}
			block.addXmlnukeObject(processor);
			
			return this.defaultXmlnukeDocument;
		}

	}
}