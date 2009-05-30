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
using com.xmlnuke.classes;
using com.xmlnuke.processor;
using System.Collections.Specialized;
using com.xmlnuke.anydataset;

using System.Data;
using System.Data.Common;

namespace com.xmlnuke.admin
{

	/// <summary>
	/// Summary description for com.
	/// </summary>
	class ManageDBConn : NewBaseAdminModule
	{
		public ManageDBConn()
		{
		}

		public override bool useCache()
		{
			return false;
		}
		
		public override AccessLevel getAccessLevel() 
		{
			return AccessLevel.CurrentSiteAndRole;
		}
	
		public override string[] getRole()
		{
			return new string[]{"MANAGER"};
		}
	
		//Returns: classes.PageXml
		public override classes.IXmlnukeDocument CreatePage() 
		{
			base.CreatePage();
			
			this._myWords = this.WordCollection();
			this.setTitlePage(this._myWords.Value("TITLE"));
			this.setHelp(this._myWords.Value("DESCRIPTION"));

			XmlBlockCollection block = new XmlBlockCollection(this._myWords.Value("BLOCK_TITLE"), BlockPosition.Center);
			
			AnydatasetFilenameProcessor anydatafile = new AnydatasetFilenameProcessor("_db", this._context);
			
			if (this._action != "test")
			{
				
				ProcessPageFields processfields = new ProcessPageFields();
				
				ProcessPageField field = new ProcessPageField();
				field.fieldName = "dbname";
				field.editable = true;
				field.key = true;
				field.visibleInList = true;
				field.dataType = INPUTTYPE.TEXT;
				field.fieldXmlInput = XmlInputObjectType.TEXTBOX;
				field.fieldCaption = this._myWords.Value("DBNAME");
				field.size = 20;
				field.maxLength = 20;
				field.required = true;
				field.newColumn = true;
				processfields.addProcessPageField(field);
				
				field = new ProcessPageField();
				field.fieldName = "dbtype";
				field.editable = true;
				field.key = false;
				field.visibleInList = true;
				field.dataType = INPUTTYPE.TEXT;
				field.fieldXmlInput = XmlInputObjectType.SELECTLIST;
				field.fieldCaption = this._myWords.Value("DBTYPE");
				field.size = 15;
				field.maxLength = 15;
				field.required = true;
				NameValueCollection dbTypes = new NameValueCollection();
				DataTable dtFactories = DbProviderFactories.GetFactoryClasses();
				dbTypes.Add("", "");
				foreach(DataRow row in dtFactories.Rows)
				{
					dbTypes.Add(row["InvariantName"].ToString(), row["Description"].ToString());
				}
				field.arraySelectList = dbTypes;
				field.defaultValue = "";
				field.newColumn = true;
				processfields.addProcessPageField(field);

				field = new ProcessPageField();
				field.fieldName = "dbconnectionstring";
				field.editable = true;
				field.key = false;
				field.visibleInList = true;
				field.dataType = INPUTTYPE.TEXT;
				field.fieldXmlInput = XmlInputObjectType.TEXTBOX;
				field.fieldCaption = this._myWords.Value("DBCONNECTIONSTRING");
				field.size = 50;
				field.maxLength = 250;
				field.required = true;
				field.defaultValue = "Data Source=server;User Id=user;Password=password;Database=banco";
				field.newColumn = true;
				processfields.addProcessPageField(field);
				
				CustomButtons buttons = new CustomButtons();
				buttons.action = "test";
				buttons.alternateText = this._myWords.Value("TESTALTERNATETEXT");
				buttons.enabled = true;
				buttons.icon = "common/editlist/ic_selecionar.gif";
				buttons.message = this._myWords.Value("TESTMESSAGETEXT");
				buttons.multiple = MultipleSelectType.ONLYONE;

				ProcessPageStateAnydata processpage = 
					new ProcessPageStateAnydata(
						this._context, 
						processfields, 
						this._myWords.Value("AVAILABLELANGUAGES"), 
						"admin:managedbconn", 
						new CustomButtons[] { buttons }, 
						anydatafile
					);
				processpage.addParameter("site", this._context.ContextValue("site"));				
				block.addXmlnukeObject(processpage);
			}
			else 
			{
				XmlParagraphCollection p = new XmlParagraphCollection();
				string db = this._context.ContextValue("valueid");
				if (db == "")
				{
					p.addXmlnukeObject(new XmlnukeText(this._myWords.Value("ERRORDBEMPTY")));
				}
				else 
				{
					try 
					{
						DBDataSet dbdataset = new DBDataSet(db, this._context);
						dbdataset.TestConnection();
						p.addXmlnukeObject(new XmlnukeText(this._myWords.Value("SEEMSOK")));
					}
					catch (Exception ex)
					{
						p.addXmlnukeObject(new XmlnukeText(this._myWords.Value("GOTERROR", ex.Message)));
					}
				}
				block.addXmlnukeObject(p);
				p = new XmlParagraphCollection();
				XmlAnchorCollection href = new XmlAnchorCollection("admin:managedbconn");
				href.addXmlnukeObject(new XmlnukeText(this._myWords.Value("GOBACK")));
				p.addXmlnukeObject(href);
				block.addXmlnukeObject(p);
			}

			this.defaultXmlnukeDocument.addXmlnukeObject(block);
			return this.defaultXmlnukeDocument.generatePage();
		}

	}
}