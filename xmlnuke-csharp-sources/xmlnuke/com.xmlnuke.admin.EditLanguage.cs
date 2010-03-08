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
using System.Collections.Generic;
using com.xmlnuke.module;
using com.xmlnuke.processor;
using com.xmlnuke.classes;
using com.xmlnuke.util;
using com.xmlnuke.anydataset;
using com.xmlnuke.international;

namespace com.xmlnuke.admin
{
    public class EditLanguage : NewBaseAdminModule, IEditListFormatter
    {
        protected LanguageCollection myWords;

        /// <summary>
        /// Default constructor
        /// </summary>
        public EditLanguage()
        { }

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

            this.myWords = this.WordCollection();
            this.setTitlePage(this.myWords.Value("TITLE"));
            this.setHelp(this.myWords.Value("DESCRIPTION"));
            this.addMenuOption(this.myWords.Value("NEWLANGUAGEFILE"), "admin:EditLanguage?action=new");
            this.addMenuOption(this.myWords.Value("VIEWSHAREDFILES"), "admin:EditLanguage?op=1");
            this.addMenuOption(this.myWords.Value("VIEWPRIVATEFILES"), "admin:EditLanguage");

            XmlBlockCollection block = new XmlBlockCollection(this.myWords.Value("WORKINGAREA"), BlockPosition.Center);
            this.defaultXmlnukeDocument.addXmlnukeObject(block);

            string op = this._context.ContextValue("op");
            string ed = this._context.ContextValue("ed");
            AnydatasetLangFilenameProcessor langDir = new AnydatasetLangFilenameProcessor("", this._context);

            string[] filelist;
            if (op == "")
            {
                filelist = FileUtil.RetrieveFilesFromFolder(langDir.PrivatePath(), "*" + langDir.Extension());
            }
            else
            {
                filelist = FileUtil.RetrieveFilesFromFolder(langDir.SharedPath(), "*" + langDir.Extension());
            }
            IIterator it = this.getIteratorFromList(filelist, langDir);

            if (this._action == "")
            {
                XmlEditList editlist = new XmlEditList(this._context, this.myWords.Value("FILELIST" + op), "admin:EditLanguage", true, false, true, false);

                EditListField field = new EditListField(true);
                field.editlistName = "#";
                field.fieldData = "singlename";
                editlist.addEditListField(field);

                field = new EditListField();
                field.editlistName = "Language Filename";
                field.fieldData = "singlename";
                editlist.addEditListField(field);

                editlist.setDataSource(it);
                editlist.addParameter("op", op);
                editlist.setEnablePage(true);
                editlist.setPageSize(20, 0);
                block.addXmlnukeObject(editlist);
            }
            else if ((this._action == ModuleAction.Edit) || (ed == "1"))
            {
                string file;
                if (ed == "1")
                {
                    file = this._context.ContextValue("file");
                }
                else
                {
                    file = this._context.ContextValue("valueid");
                }

                AnydatasetLangFilenameProcessor langDir2 = new AnydatasetLangFilenameProcessor(file, this._context);
                langDir2.FilenameLocation = ((op == "" ? ForceFilenameLocation.PrivatePath : ForceFilenameLocation.SharedPath));
                AnyDataSet anydata = new AnyDataSet(langDir2);

                IIterator it2 = anydata.getIterator();
                SingleRow sr = it2.moveNext();

                string[] arFields = sr.getFieldNames();

                int i = 0;
                ProcessPageFields processPageFields = new ProcessPageFields();
                foreach (string value in arFields)
                {
                    ProcessPageField process = ProcessPageFields.Factory(value, value, 40, (i < 4), true);
                    process.key = (i == 0);
                    if (value == "LANGUAGE")
                    {
                        process.saveDatabaseFormatter = this;
                    }
                    processPageFields.addProcessPageField(process);
                    i++;
                }

                ProcessPageStateAnydata processpage =
                    new ProcessPageStateAnydata(
                        this._context,
                        processPageFields,
                        this.myWords.Value("EDITLANGUAGE", file),
                        "admin:EditLanguage",
                        null,
                        langDir2
                    );
                processpage.addParameter("op", op);
                processpage.addParameter("ed", "1");
                processpage.addParameter("file", file);

                block.addXmlnukeObject(processpage);

            }
            else if (this._action == ModuleAction.Create)
            {
                XmlFormCollection form = new XmlFormCollection(this._context, "admin:EditLanguage", this.myWords.Value("NEWLANGUAGEFILE"));
                form.addXmlnukeObject(new XmlInputHidden("action", ModuleAction.CreateConfirm));
                form.addXmlnukeObject(new XmlInputHidden("op", op));
                form.addXmlnukeObject(new XmlInputTextBox(this.myWords.Value("NEWFILE"), "newfile", "", 30));
                form.addXmlnukeObject(new XmlInputMemo(this.myWords.Value("FIELDS"), "fields", "TITLE\r\nABSTRACT"));
                form.addXmlnukeObject(XmlInputButtons.CreateSubmitButton(this.myWords.Value("TXT_SUBMIT")));
                block.addXmlnukeObject(form);
            }
            else if (this._action == ModuleAction.CreateConfirm)
            {
                string file = this._context.ContextValue("newfile");
                AnydatasetLangFilenameProcessor langDir2 = new AnydatasetLangFilenameProcessor(file, this._context);
                langDir2.FilenameLocation = ((op == "" ? ForceFilenameLocation.PrivatePath : ForceFilenameLocation.SharedPath));
                AnyDataSet anydata = new AnyDataSet(langDir2);

                string[] fields = this._context.ContextValue("fields").Split('\n');

                NameValueCollection langs = this._context.LanguagesAvailable();
                foreach (string lang in langs.Keys)
                {
                    anydata.appendRow();
                    anydata.addField("LANGUAGE", lang);
                    foreach (string field in fields)
                    {
                        anydata.addField(field.Replace("\r", "").ToUpper(), "");
                    }
                }
                anydata.Save(langDir2);
                this._context.redirectUrl("admin:EditLanguage?ed=1&file=" + file);
            }


            string langfile = this._context.ContextValue("langfile");
            string contents = this._context.ContextValue("contents");
            //contents = stripslashes(contents);

            return this.defaultXmlnukeDocument;
        }

        /**
         *
         * @param array filelist
         * @param FilenameProcessor proc
         * @return IIterator
         */
        private IIterator getIteratorFromList(string[] filelist, FilenameProcessor proc)
        {
            Array.Sort<string>(filelist);
            List<string> arResult = new List<string>();

            foreach (string file in filelist)
            {
                string name = FileUtil.ExtractFileName(file);
                name = proc.removeLanguage(name);

                arResult.Add(name);
            }

            ArrayDataSet ds = new ArrayDataSet(arResult.ToArray(), "singlename");
            return ds.getIterator();

        }

        public string Format(SingleRow row, string fieldname, string value)
        {
            return this._context.ContextValue("field");
        }

    }
}