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
using com.xmlnuke.anydataset;
using com.xmlnuke.processor;
using com.xmlnuke.classes;
using System.Collections;
using System.Collections.Specialized;

namespace com.xmlnuke.admin
{
	/// <summary>
	/// Summary description for com.
	/// </summary>
	class ManagePoll : NewBaseAdminModule
	{	
		protected string _tblpoll = "";
		protected string _tblanswer = "";
		protected string _tbllastip = "";
		protected bool _isdb = false;
		protected string _connection = "";

		protected string _moduleUrl = "admin:managepoll";
		
		public ManagePoll()
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
			return new string[]{"MANAGER", "EDITOR"};
		}
	
		//Returns: classes.PageXml
		public override classes.IXmlnukeDocument CreatePage() 
		{
			base.CreatePage();
			
			this._myWords = this.WordCollection();
			this.setTitlePage(this._myWords.Value("TITLE"));
			this.setHelp(this._myWords.Value("DESCRIPTION"));

			XmlBlockCollection block = new XmlBlockCollection(this._myWords.Value("BLOCK_TITLE"), BlockPosition.Center);
			
			this.addMenuItem(this._moduleUrl, this._myWords.Value("MENULISTPOLLS"), "");
			this.addMenuItem("admin:managedbconn", this._myWords.Value("MENUMANAGEDBCONN"), "");
				
			// Create a NEW config file and SETUP Database
			AnydatasetFilenameProcessor configfile = new AnydatasetFilenameProcessor("_poll", this._context);
			if (!util.FileUtil.Exists(configfile))
			{
				this.CreateSetup(block);
			}
			else 
			{
				AnyDataSet anyconfig = new AnyDataSet(configfile);
				IIterator it = anyconfig.getIterator();
				if (it.hasNext())
				{
					SingleRow sr = it.moveNext();
					this._isdb = sr.getField("dbname") != "-anydata-";
					this._connection = sr.getField("dbname");
					this._tblanswer = sr.getField("tbl_answer");
					this._tblpoll = sr.getField("tbl_poll");
					this._tbllastip = sr.getField("tbl_lastip");
				}
				else 
				{
					this.CreateSetup(block);
					this.defaultXmlnukeDocument.addXmlnukeObject(block);
					return this.defaultXmlnukeDocument.generatePage();
				}
				
				if (this._context.ContextValue("op") == "")
				{
					this.ListPoll(block);
				}
				else if (this._context.ContextValue("op") == "answer")
				{
					string[] polldata = this._context.ContextValue("valueid").Split('|');
					this.ListAnswers(block, polldata[0], polldata[1]);
				}
				else if (this._context.ContextValue("op") == "answernav")
				{
					this.ListAnswers(block, this._context.ContextValue("curpoll"), this._context.ContextValue("curlang"));
				}
			}

			this.defaultXmlnukeDocument.addXmlnukeObject(block);
			return this.defaultXmlnukeDocument.generatePage();
		}

		
		/**
		 * Enter description here...
		 *
		 * @param XmlBlockCollection block
		 */
		protected void CreateSetup(XmlBlockCollection block)
		{
			if (this._action == ModuleAction.CreateConfirm)
			{
				XmlParagraphCollection p = new XmlParagraphCollection();
				if (this._context.ContextValue("type")!="-anydata-")
				{
					try 
					{
						string tblpoll = this._context.ContextValue("tbl_poll");
						string tblanswer = this._context.ContextValue("tbl_answer");
						string tbllastip = this._context.ContextValue("tbl_lastip");
						string suffix = this._context.ContextValue("tablesuffix");
						
						DBDataSet dbdata = new DBDataSet(this._context.ContextValue("type"), this._context);
						ArrayList results = new ArrayList();
						results.Add(this.CreateTable(dbdata, "create table tblpoll", "create table tblpoll (name varchar(15), lang char(5), question varchar(150), multiple char(1), showresults char(1), active char(1)) " + suffix));
						results.Add(this.CreateTable(dbdata, "create table tblanswer", "create table tblanswer (name varchar(15), lang char(5), code int, short varchar(10), answer varchar(50), votes int)" + suffix));
						//results.Add(this.CreateTable(dbdata, "create table tbllastip", "create table tbllastip (name varchar(15), ip varchar(15))" + suffix));
						results.Add(this.CreateTable(dbdata, "add primary key poll", "alter table tblpoll add constraint pk_poll primary key (name, lang);"));
						results.Add(this.CreateTable(dbdata, "add primary key answer", "alter table tblanswer add constraint pk_answer primary key (name, lang, code)"));
						//results.Add(this.CreateTable(dbdata, "add primary key lastip", "alter table tbllastip add constraint pk_lastip primary key (name, ip)"));
						results.Add(this.CreateTable(dbdata, "add check poll 1", "alter table tblpoll add constraint ck_poll_multiple check (multiple in ('Y', 'N'))"));
						results.Add(this.CreateTable(dbdata, "add check poll 2", "alter table tblpoll add constraint ck_poll_showresults check (showresults in ('Y', 'N'))"));
						results.Add(this.CreateTable(dbdata, "add check poll 3", "alter table tblpoll add constraint ck_poll_active check (active in ('Y', 'N'))"));
						results.Add(this.CreateTable(dbdata, "add foreign key answer", "alter table tblanswer add constraint pk_answer_poll foreign key (name) references tblpoll(name)"));
						//results.Add(this.CreateTable(dbdata, "add foreign key lastip", "alter table tbllastip add constraint pk_lastip_poll foreign key (name) references tblpoll(name)");
						
						block.addXmlnukeObject(new XmlEasyList(EasyListType.UNORDEREDLIST, "", this._myWords.Value("RESULTSQL"), results));
						
						AnyDataSet anypoll = new AnyDataSet(new AnydatasetFilenameProcessor("_poll", this._context));
						anypoll.appendRow();
						anypoll.addField("dbname", this._context.ContextValue("type"));
						anypoll.addField("tbl_poll", tblpoll);
						anypoll.addField("tbl_answer", tblanswer);
						anypoll.addField("tbl_lastip", tbllastip);
						anypoll.Save();
					}
					catch (Exception ex)
					{
						p.addXmlnukeObject(new XmlnukeText(this._myWords.Value("GOTERROR", ex.Message)));
					}
				}
				else 
				{
					AnyDataSet anypoll = new AnyDataSet(new AnydatasetFilenameProcessor("_poll", this._context));
					anypoll.appendRow();
					anypoll.addField("dbname", "-anydata-");
					anypoll.Save();
				}
				p.addXmlnukeObject(new XmlnukeBreakLine());
				p.addXmlnukeObject(new XmlnukeText(this._myWords.Value("CONFIGCREATED"), true));
				block.addXmlnukeObject(p);
			}
			else 
			{
				XmlParagraphCollection p = new XmlParagraphCollection();
				p.addXmlnukeObject(new XmlnukeText(this._myWords.Value("FIRSTTIMEMESSAGE")));
				block.addXmlnukeObject(p);
				
				XmlFormCollection form = new XmlFormCollection(this._context, this._moduleUrl, this._myWords.Value("CREATESETUP"));
				form.addXmlnukeObject(new XmlInputHidden("action", ModuleAction.CreateConfirm));
				NameValueCollection db = new NameValueCollection();
				db.Add("-anydata-", this._myWords.Value("NOTUSEDB"));
				AnydatasetFilenameProcessor anydatafile = new AnydatasetFilenameProcessor("_db", this._context);
				AnyDataSet anydata = new AnyDataSet(anydatafile);
				IIterator it = anydata.getIterator();
				while (it.hasNext())
				{
					SingleRow sr = it.moveNext();
					db.Add(sr.getField("dbname"), sr.getField("dbname"));
				}
				form.addXmlnukeObject(new XmlEasyList(EasyListType.SELECTLIST, "type", this._myWords.Value("FORMCONN"), db));
				
				XmlInputGroup inputGroup = new XmlInputGroup(this._context, "tabledetail", true);
				inputGroup.setVisible(false);
				
				XmlInputTextBox text = new XmlInputTextBox(this._myWords.Value("TABLENAME_POLL"), "tbl_poll", "xmlnuke_poll", 20);
				text.setRequired(true);
				inputGroup.addXmlnukeObject(text);
				
				text = new XmlInputTextBox(this._myWords.Value("TABLENAME_ANSWER"), "tbl_answer", "xmlnuke_answer", 20);
				text.setRequired(true);
				inputGroup.addXmlnukeObject(text);
				
				text = new XmlInputTextBox(this._myWords.Value("TABLENAME_LASTIP"), "tbl_lastip", "xmlnuke_lastip", 20);
				text.setRequired(true);
				inputGroup.addXmlnukeObject(text);
				
				text = new XmlInputTextBox(this._myWords.Value("TABLE_SUFFIX"), "tablesuffix", "TYPE INNODB", 30);
				text.setRequired(true);
				inputGroup.addXmlnukeObject(text);
				
				form.addXmlnukeObject(inputGroup);
				
				XmlInputButtons buttons = new XmlInputButtons();
				buttons.addSubmit(this._myWords.Value("CREATESETUPBTN"));
				form.addXmlnukeObject(buttons);
				
				block.addXmlnukeObject(form);
				
				string javascript = 
					"// ByJG  " +
					"fn_addEvent('type', 'change', enableFields); " +
					"void enableFields(e) { " +
			    	"	obj = document.getElementById('type'); " +
			    	"	showHide_tabledetail(obj.selectedIndex != 0); " +
					"} ";
				this.defaultXmlnukeDocument.addJavaScriptSource(javascript, true);
				
			}
				
		}
		
		/**
		 * Enter description here...
		 *
		 * @param DbDataSet dbdata
		 * @param string desc
		 * @param string sql
		 * @return unknown
		 */
		protected string CreateTable(DBDataSet dbdata, string desc, string sql)
		{
			string result = desc + ": ";
			try 
			{
				dbdata.execSQL(sql);
				result += "OK";
			}
			catch (Exception ex)
			{
				result += this._myWords.Value("GOTERROR", ex.Message);
			}
			return result;
		}
		
		
		/**
		 * Enter description here...
		 *
		 * @param XmlBlockCollection block
		 */
		protected void ListPoll(XmlBlockCollection block)
		{
			NameValueCollection yesno = new NameValueCollection();
			yesno.Add("Y", this._myWords.Value("YES"));
			yesno.Add("N", this._myWords.Value("NO"));
			
			ProcessPageFields processfields = new ProcessPageFields();
			
			ProcessPageField field = ProcessPageFields.Factory("name", this._myWords.Value("POLLNAME"), 15, true, true);
			field.key = true;
			field.dataType = INPUTTYPE.UPPERASCII;
			processfields.addProcessPageField(field);
			
			field = ProcessPageFields.Factory("lang", this._myWords.Value("POLLLANG"), 5, true, true);
			field.key = true;
			field.fieldXmlInput = XmlInputObjectType.SELECTLIST;
			field.arraySelectList = this._context.LanguagesAvailable();
			processfields.addProcessPageField(field);
			
			field = ProcessPageFields.Factory("question", this._myWords.Value("POLLQUESTION"), 150, true, true);
			field.maxLength = 150;
			field.size = 40;
			processfields.addProcessPageField(field);
			
			field = ProcessPageFields.Factory("multiple", this._myWords.Value("POLLMULTIPLE"), 1, true, true);
			field.fieldXmlInput = XmlInputObjectType.SELECTLIST;
			field.arraySelectList = yesno;
			processfields.addProcessPageField(field);
						
			field = ProcessPageFields.Factory("showresults", this._myWords.Value("POLLSHOWRESULTS"), 1, true, true);
			field.fieldXmlInput = XmlInputObjectType.SELECTLIST;
			field.arraySelectList = yesno;
			processfields.addProcessPageField(field);
						
			field = ProcessPageFields.Factory("active", this._myWords.Value("POLLACTIVE"), 1, true, true);
			field.fieldXmlInput = XmlInputObjectType.SELECTLIST;
			field.arraySelectList = yesno;
			processfields.addProcessPageField(field);
						
			CustomButtons buttons = new CustomButtons();
			buttons.alternateText = this._myWords.Value("SHOWEDITANSWERS");
			buttons.enabled = true;
			buttons.icon = "common/editlist/ic_subcategorias.gif";
			buttons.message = this._myWords.Value("SHOWEDITANSWERS");
			buttons.multiple = MultipleSelectType.ONLYONE;
			buttons.url = this._moduleUrl + "?op=answer";
			
			ProcessPageStateBase processpage;

			if (this._isdb)
			{
				processpage = 
					new ProcessPageStateDB(
						this._context, 
						processfields, 
						this._myWords.Value("AVAILABLEPOLLS"), 
						this._moduleUrl, 
						new CustomButtons[] { buttons }, 
						this._tblpoll, 
						this._connection
					);
			}
			else 
			{
				AnydatasetFilenameProcessor anydatafile = new AnydatasetFilenameProcessor("poll_list", this._context);
				processpage = 
					new ProcessPageStateAnydata(
						this._context, 
						processfields, 
						this._myWords.Value("AVAILABLEPOLLS"), 
						this._moduleUrl,
						new CustomButtons[] { buttons },
						anydatafile
					);
			}
				
			block.addXmlnukeObject(processpage);			
		}
	
		/**
		 * Enter description here...
		 *
		 * @param XmlBlockCollection block
		 * @param string pollname
		 */
		protected void ListAnswers(XmlBlockCollection block, string pollname, string lang)
		{
			NameValueCollection yesno = new NameValueCollection();
			yesno.Add("Y", this._myWords.Value("YES"));
			yesno.Add("N", this._myWords.Value("NO"));
						
			ProcessPageFields processfields = new ProcessPageFields();
			
			ProcessPageField field = ProcessPageFields.Factory("name", this._myWords.Value("POLLNAME"), 15, true, true);
			field.key = true;
			field.editable = false;
			field.defaultValue = pollname;
			processfields.addProcessPageField(field);
			
			field = ProcessPageFields.Factory("lang", this._myWords.Value("POLLLANG"), 5, true, true);
			field.key = true;
			field.editable = false;
			field.fieldXmlInput = XmlInputObjectType.SELECTLIST;
			field.arraySelectList = this._context.LanguagesAvailable();
			field.defaultValue = lang;
			processfields.addProcessPageField(field);
			
			field = ProcessPageFields.Factory("code", this._myWords.Value("ANSWERCODE"), 3, true, true);
			field.key = true;
			field.dataType = INPUTTYPE.NUMBER;
			processfields.addProcessPageField(field);
			
			field = ProcessPageFields.Factory("short", this._myWords.Value("SHORTTEXT"), 10, true, true);
			processfields.addProcessPageField(field);
			
			field = ProcessPageFields.Factory("answer", this._myWords.Value("ANSWERTEXT"), 50, true, true);
			processfields.addProcessPageField(field);
						
			field = ProcessPageFields.Factory("votes", this._myWords.Value("ANSWERVOTES"), 3, true, true);
			field.editable = false;
			field.defaultValue = "0";
			field.dataType = INPUTTYPE.NUMBER;
			processfields.addProcessPageField(field);

			ProcessPageStateBase processpage;

			if (this._isdb)
			{
				processpage = 
					new ProcessPageStateDB(
						this._context, 
						processfields, 
						this._myWords.Value("AVAILABLEANSWER", pollname), 
						this._moduleUrl, 
						null, 
						this._tblanswer, 
						this._connection
					);
				processpage.setFilter("name = '" + pollname + "' and lang='" + lang + "'");
			}
			else 
			{
				AnydatasetFilenameProcessor anydatafile = new AnydatasetFilenameProcessor("poll_" + pollname + "_" + lang, this._context);
				processpage = 
					new ProcessPageStateAnydata(
						this._context, 
						processfields, 
						this._myWords.Value("AVAILABLEANSWER", pollname), 
						this._moduleUrl, 
						null, 
						anydatafile
					);
			}
			processpage.addParameter("op", "answernav");
			processpage.addParameter("curpoll", pollname);
			processpage.addParameter("curlang", lang);
			
			block.addXmlnukeObject(processpage);	
		}
	}
}