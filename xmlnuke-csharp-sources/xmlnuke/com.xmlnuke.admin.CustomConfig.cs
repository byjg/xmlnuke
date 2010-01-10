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
using System.Collections.Specialized;
using com.xmlnuke.module;
using com.xmlnuke.classes;
using com.xmlnuke.anydataset;
using com.xmlnuke.processor;

namespace com.xmlnuke.admin
{
	/// <summary>
	/// Summary description for com.
	/// </summary>
	public class CustomConfig : NewBaseAdminModule
	{
		public CustomConfig()
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
			return new string[] { "MANAGER" };
		}

		override public classes.IXmlnukeDocument CreatePage()
		{
			base.CreatePage(); // Doesnt necessary get PX, because PX is protected!

			com.xmlnuke.international.LanguageCollection myWords = this.WordCollection();

			this.setHelp(myWords.Value("DESCRIPTION"));
			//this.addMenuOption("OK", "admin:ManageGroup?action=aqui");
			this.setTitlePage(myWords.Value("TITLE"));
			//this.addMenuOption("Click here to ERASE ALL cache.", "admin:CustomConfig?action=erase");
			//this.addMenuOption("Click here to LIST cache.", "admin:CustomConfig?action=list");

			string action = this._action.ToLower();

			XmlBlockCollection block = new XmlBlockCollection(myWords.Value("WORKINGAREA"), BlockPosition.Center);

			if (action == "update")
			{
				System.Collections.Specialized.NameValueCollection nv = new System.Collections.Specialized.NameValueCollection();
				nv.Add("xmlnuke.SMTPSERVER", this._context.ContextValue("smtpserver"));
                nv.Add("xmlnuke.LANGUAGESAVAILABLE", this.createLanguageString());
                nv.Add("xmlnuke.SHOWCOMPLETEERRORMESSAGES", this._context.ContextValue("showcompleterrormessages"));
				nv.Add("xmlnuke.LOGINMODULE", this._context.ContextValue("loginmodule"));
				nv.Add("xmlnuke.USERSDATABASE", this._context.ContextValue("usersdatabase"));
				nv.Add("xmlnuke.USERSCLASS", this._context.ContextValue("usersclass"));
				nv.Add("xmlnuke.DEBUG", this._context.ContextValue("txtdebug"));
        		nv.Add("xmlnuke.CAPTCHACHALLENGE", this._context.ContextValue("captchachallenge"));
	    		nv.Add("xmlnuke.CAPTCHALETTERS", this._context.ContextValue("captchaletters"));
				nv.Add("xmlnuke.ENABLEPARAMPROCESSOR", this._context.ContextValue("enableparamprocessor"));
				nv.Add("xmlnuke.USEFULLPARAMETER", this._context.ContextValue("usefullparameter"));
				this._context.updateCustomConfig(nv);
				XmlParagraphCollection paragraph = new XmlParagraphCollection();
				paragraph.addXmlnukeObject(new XmlnukeText(myWords.Value("UPDATED"), true));
				block.addXmlnukeObject(paragraph);
			}

			NameValueCollection truefalse = new NameValueCollection();
			truefalse.Add("", "Use Default");
			truefalse.Add("true", "True");
			truefalse.Add("false", "False");

			NameValueCollection easyhard = new NameValueCollection();
            easyhard.Add("easy", "Easy");
            easyhard.Add("hard", "Hard");

			NameValueCollection nletters = new NameValueCollection();
            nletters.Add("5", "5"); 
            nletters.Add("6", "6"); 
            nletters.Add("7", "7"); 
            nletters.Add("8", "8"); 
            nletters.Add("9", "9");
            nletters.Add("10", "10");

			XmlFormCollection form = new XmlFormCollection(this._context, "admin:CustomConfig", myWords.Value("FORMTITLE"));
			form.addXmlnukeObject(new XmlInputHidden("action", "update"));
			form.addXmlnukeObject(new XmlInputLabelField("xmlnuke.ROOTDIR", this._context.ContextValue("xmlnuke.ROOTDIR")));
			form.addXmlnukeObject(new XmlInputLabelField("xmlnuke.USEABSOLUTEPATHSROOTDIR", this._context.ContextValue("xmlnuke.USEABSOLUTEPATHSROOTDIR")));
			form.addXmlnukeObject(new XmlInputLabelField("xmlnuke.URLMODULE", this._context.ContextValue("xmlnuke.URLMODULE")));
			form.addXmlnukeObject(new XmlInputLabelField("xmlnuke.URLXMLNUKEADMIN", this._context.ContextValue("xmlnuke.URLXMLNUKEADMIN")));
			form.addXmlnukeObject(new XmlInputLabelField("xmlnuke.URLXMLNUKEENGINE", this._context.ContextValue("xmlnuke.URLXMLNUKEENGINE")));
			form.addXmlnukeObject(new XmlInputLabelField("xmlnuke.DEFAULTSITE", this._context.ContextValue("xmlnuke.DEFAULTSITE")));
			form.addXmlnukeObject(new XmlInputLabelField("xmlnuke.DEFAULTPAGE", this._context.ContextValue("xmlnuke.DEFAULTPAGE")));
			form.addXmlnukeObject(new XmlInputLabelField("xmlnuke.ALWAYSUSECACHE", this._context.ContextValue("xmlnuke.ALWAYSUSECACHE")));
            form.addXmlnukeObject(new XmlInputTextBox("xmlnuke.SMTPSERVER", "smtpserver", this._context.ContextValue("xmlnuke.SMTPSERVER"), 30));
			this.generateLanguageInput(form);
			form.addXmlnukeObject(new XmlEasyList(EasyListType.SELECTLIST, "showcompleterrormessages", "xmlnuke.SHOWCOMPLETEERRORMESSAGES", truefalse, this._context.ContextValue("xmlnuke.SHOWCOMPLETEERRORMESSAGES")));
			form.addXmlnukeObject(new XmlInputTextBox("xmlnuke.LOGINMODULE", "loginmodule", this._context.ContextValue("xmlnuke.LOGINMODULE"), 30));
			form.addXmlnukeObject(new XmlEasyList(EasyListType.SELECTLIST, "usersdatabase", "xmlnuke.USERSDATABASE", this.getStringConnectionsArray(), this._context.ContextValue("xmlnuke.USERSDATABASE")));
			form.addXmlnukeObject(new XmlInputTextBox("xmlnuke.USERSCLASS", "usersclass", this._context.ContextValue("xmlnuke.USERSCLASS"), 30));
			form.addXmlnukeObject(new XmlEasyList(EasyListType.SELECTLIST, "txtdebug", "xmlnuke.DEBUG", truefalse, this._context.ContextValue("xmlnuke.DEBUG")));
    		form.addXmlnukeObject(new XmlEasyList(EasyListType.SELECTLIST, "captchachallenge", "xmlnuke.CAPTCHACHALLENGE", easyhard, this._context.ContextValue("xmlnuke.CAPTCHACHALLENGE")));
	    	form.addXmlnukeObject(new XmlEasyList(EasyListType.SELECTLIST, "captchaletters", "xmlnuke.CAPTCHALETTERS", nletters, this._context.ContextValue("xmlnuke.CAPTCHALETTERS")));
			form.addXmlnukeObject(new XmlEasyList(EasyListType.SELECTLIST, "enableparamprocessor", "xmlnuke.ENABLEPARAMPROCESSOR", truefalse, this._context.ContextValue("xmlnuke.ENABLEPARAMPROCESSOR")));
			form.addXmlnukeObject(new XmlEasyList(EasyListType.SELECTLIST, "usefullparameter", "xmlnuke.USEFULLPARAMETER", truefalse, this._context.ContextValue("xmlnuke.USEFULLPARAMETER")));
            form.addXmlnukeObject(new XmlInputLabelField("xmlnuke.EXTERNALSITEDIR", this._context.ContextValue("xmlnuke.EXTERNALSITEDIR")));

			XmlInputButtons boxButton = new XmlInputButtons();
			boxButton.addSubmit(myWords.Value("TXT_SAVE"));
			form.addXmlnukeObject(boxButton);

			block.addXmlnukeObject(form);
			this.defaultXmlnukeDocument.addXmlnukeObject(block);

			return this.defaultXmlnukeDocument;
		}

		protected NameValueCollection getLangArray()
		{
			NameValueCollection ret = new NameValueCollection();
			ret.Add("", "");
			ret.Add("pt-br=Português (Brasil)", "pt-br=Português (Brasil)");
			ret.Add("en-us=English (United States)", "en-us=English (United States)");
			ret.Add("fr-fr=Français", "fr-fr=Français");
			ret.Add("it-it=Italiano", "it-it=Italiano");
			ret.Add("ar-dz=جزائري عربي", "ar-dz=جزائري عربي");
			ret.Add("bg-bg=Български", "bg-bg=Български");
			ret.Add("ca-es=Català", "ca-es=Català");
			ret.Add("cs-cz=Ceština", "cs-cz=Ceština");
			ret.Add("da-dk=Dansk", "da-dk=Dansk");
			ret.Add("de-de=Deutsch", "de-de=Deutsch");
			ret.Add("el-gr=Ελληνικά", "el-gr=Ελληνικά");
			ret.Add("en-gb=English (Great Britain)", "en-gb=English (Great Britain)");
			ret.Add("es-es=Español", "es-es=Español");
			ret.Add("et-ee=Eesti", "et-ee=Eesti");
			ret.Add("fi-fi=Suomi", "fi-fi=Suomi");
			ret.Add("gl-gz=Galego", "gl-gz=Galego");
			ret.Add("he-il=עברית", "he-il=עברית");
			ret.Add("hu-hu=Magyar", "hu-hu=Magyar");
			ret.Add("id-id=Bahasa Indonesia", "id-id=Bahasa Indonesia");
			ret.Add("is-is=Íslenska", "is-is=Íslenska");
			ret.Add("ja-jp=Japanese", "ja-jp=Japanese");
			ret.Add("lv-lv=Latviešu", "lv-lv=Latviešu");
			ret.Add("nl-nl=Nederlands", "nl-nl=Nederlands");
			ret.Add("no-no=Norsk", "no-no=Norsk");
			ret.Add("pl-pl=Polski", "pl-pl=Polski");
			ret.Add("pt-pt=Português (Portugal)", "pt-pt=Português (Portugal)");
			ret.Add("ro-ro=Română", "ro-ro=Română");
			ret.Add("ru-ru=Русский", "ru-ru=Русский");
			ret.Add("sk-sk=Slovenčina", "sk-sk=Slovenčina");
			ret.Add("sv-se=Svenska (Sverige)", "sv-se=Svenska (Sverige)");
			ret.Add("th-th=Thai", "th-th=Thai");
			ret.Add("uk-ua=Українська", "uk-ua=Українська");
			ret.Add("zh-cn=Chinese (Simplified)", "zh-cn=Chinese (Simplified)");
			ret.Add("zh-tw=Chinese (Traditional)", "zh-tw=Chinese (Traditional)");

			return ret;
		}

		/**
		 * Write Languages Available function
		 *
		 * @param XmlFormCollection form
		 */
		protected void generateLanguageInput(XmlFormCollection form)
		{
			string[] curValueArray = this._context.ContextValue("xmlnuke.LANGUAGESAVAILABLE").Split('|');

			int key = 0;
			foreach (string value in curValueArray)
			{
				form.addXmlnukeObject(new XmlEasyList(EasyListType.SELECTLIST, "languagesavailable" + (key++).ToString(), "xmlnuke.LANGUAGESAVAILABLE", this.getLangArray(), value));
			}
			form.addXmlnukeObject(new XmlEasyList(EasyListType.SELECTLIST, "languagesavailable" + (key++).ToString(), "xmlnuke.LANGUAGESAVAILABLE", this.getLangArray()));
			form.addXmlnukeObject(new XmlEasyList(EasyListType.SELECTLIST, "languagesavailable" + (key).ToString(), "xmlnuke.LANGUAGESAVAILABLE", this.getLangArray()));
			form.addXmlnukeObject(new XmlInputHidden("languagesavailable", key.ToString()));
		}

		protected string createLanguageString()
		{
			string key = "languagesavailable";
			int qty = Convert.ToInt32(this._context.ContextValue(key));
			string value = "";
			for (int i = 0; i <= qty; i++)
			{
				if (this._context.ContextValue(key + i.ToString()) != "") value += (value != "" ? "|" : "") + this._context.ContextValue(key + i.ToString());
			}
			return value;
		}

		/**
		 * Enter description here...
		 *
		 */
		protected NameValueCollection getStringConnectionsArray()
		{
			AnydatasetFilenameProcessor processor = new AnydatasetFilenameProcessor("_db", this._context);
			processor.UseFileFromAnyLanguage();
			AnyDataSet anydata = new AnyDataSet(processor);
			IIterator it = anydata.getIterator();
			NameValueCollection ret = new NameValueCollection();
			ret.Add("", "-- Default UsersAnydataSet --");
			while (it.hasNext())
			{
				SingleRow sr = it.moveNext();
				ret.Add(sr.getField("dbname"), sr.getField("dbname"));
			}

			return ret;
		}
	}
}