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

using com.xmlnuke.engine;
using com.xmlnuke.classes;
using com.xmlnuke.anydataset;
using com.xmlnuke.processor;
using com.xmlnuke.international;
using com.xmlnuke.util;

namespace com.xmlnuke.module
{

	public class Download : BaseModule
	{

		protected AnyDataSet _download;

		protected string _file;

		protected string _category;

		protected XmlParagraphCollection _paragraph;


		/// <summary>
		/// Default Constructor
		/// </summary>
		public Download()
		{ }

		override public bool useCache()
		{
			return false;
		}


		/// <summary>
		/// Return the LanguageCollection used in this module
		/// </summary>
		override public LanguageCollection WordCollection()
		{
			LanguageCollection myWords = base.WordCollection();
			if (!myWords.loadedFromFile())
			{
				// English Words
				myWords.addText("en-us", "TITLE", "Module Download");
				// Portuguese Words
				myWords.addText("pt-br", "TITLE", "Módulo de Download");
			}
			return myWords;
		}

		/// <summary>
		/// CreatePage is called from module processor and decide the proper output XML.
		/// </summary>
		override public IXmlnukeDocument CreatePage()
		{
			this._myWords = this.WordCollection();

			XmlnukeDocument document = new XmlnukeDocument(this._myWords.Value("TITLE"), this._myWords.Value("ABSTRACT"));

			XmlBlockCollection blockcenter = new XmlBlockCollection(this._myWords.Value("BLOCKTITLE"), BlockPosition.Center);
			document.addXmlnukeObject(blockcenter);

			this._paragraph = new XmlParagraphCollection();
			blockcenter.addXmlnukeObject(this._paragraph);

			this._category = this._context.ContextValue("cat");
			this._file = this._context.ContextValue("file");

			AnydatasetFilenameProcessor downloadFile = new AnydatasetFilenameProcessor("_download", this._context);
			this._download = new AnyDataSet(downloadFile);

			if (this._file != "")
			{
				this.showForm();
			}
			else if (this._category == "")
			{
				this.showCategories();
			}
			else
			{
				this.showFiles();
			}
			return document.generatePage();
		}

		/**
		 * Show files
		 *
		 */
		public void showFiles()
		{
			this._paragraph.addXmlnukeObject(new XmlnukeText(this._myWords.Value("SELECTFILE"), true));

			XmlListCollection listCollection = new XmlListCollection(XmlListType.UnorderedList);
			this._paragraph.addXmlnukeObject(listCollection);

			IteratorFilter iteratorFilter = new IteratorFilter();
			iteratorFilter.addRelation("TYPE", Relation.Equal, "FILE");
			iteratorFilter.addRelation("cat_id", Relation.Equal, this._category);
			IIterator iterator = this._download.getIterator(iteratorFilter);
			while (iterator.hasNext())
			{
				SingleRow singleRow = iterator.moveNext();

				XmlnukeSpanCollection objectLineList = new XmlnukeSpanCollection();
				listCollection.addXmlnukeObject(objectLineList);

				objectLineList.addXmlnukeObject(new XmlnukeText(this.getField(singleRow, "name"), true));
				objectLineList.addXmlnukeObject(new XmlnukeBreakLine());
				objectLineList.addXmlnukeObject(new XmlnukeText(this.getField(singleRow, "description")));

				objectLineList.addXmlnukeObject(new XmlnukeBreakLine());
				objectLineList.addXmlnukeObject(new XmlnukeText("( "));
				XmlAnchorCollection link = new XmlAnchorCollection("module:Download?file=" + singleRow.getField("file_id"));
				link.addXmlnukeObject(new XmlnukeText(this._myWords.Value("SELECTFORDOWNLOAD"), true));
				objectLineList.addXmlnukeObject(link);
				objectLineList.addXmlnukeObject(new XmlnukeText(" | "));
				link = new XmlAnchorCollection(this.getField(singleRow, "seemore"));
				link.addXmlnukeObject(new XmlnukeText(this._myWords.Value("MOREINFO"), true));
				objectLineList.addXmlnukeObject(link);
				objectLineList.addXmlnukeObject(new XmlnukeText(" )"));
			}

			this._paragraph.addXmlnukeObject(new XmlnukeBreakLine());
			this._paragraph.addXmlnukeObject(new XmlnukeBreakLine());
			XmlnukeText text = new XmlnukeText(this._myWords.Value("TXT_BACK"));
			XmlAnchorCollection link2 = new XmlAnchorCollection("module:Download");
			link2.addXmlnukeObject(text);
			this._paragraph.addXmlnukeObject(link2);
		}

		/**
		 * Show Categories
		 *
		 */
		public void showCategories()
		{
			this._paragraph.addXmlnukeObject(new XmlnukeText(this._myWords.Value("SELECTCATEGORY"), true));

			XmlListCollection listCollection = new XmlListCollection(XmlListType.UnorderedList);
			this._paragraph.addXmlnukeObject(listCollection);

			IteratorFilter iteratorFilter = new IteratorFilter();
			iteratorFilter.addRelation("TYPE", Relation.Equal, "CATEGORY");
			IIterator iterator = this._download.getIterator(iteratorFilter);
			while (iterator.hasNext())
			{
				SingleRow singleRow = iterator.moveNext();

				XmlnukeSpanCollection objectList = new XmlnukeSpanCollection();
				listCollection.addXmlnukeObject(objectList);

				XmlAnchorCollection anchor = new XmlAnchorCollection("module:Download?cat=" + singleRow.getField("cat_id"));
				anchor.addXmlnukeObject(new XmlnukeText(this.getField(singleRow, "name"), true));
				objectList.addXmlnukeObject(anchor);
				objectList.addXmlnukeObject(new XmlnukeBreakLine());
				objectList.addXmlnukeObject(new XmlnukeText(" " + this.getField(singleRow, "description")));
			}
		}

		/**
		 * Show Form
		 *
		 */
		public void showForm()
		{
			IteratorFilter iteratorFilter = new IteratorFilter();
			iteratorFilter.addRelation("TYPE", Relation.Equal, "FILE");
			iteratorFilter.addRelation("file_id", Relation.Equal, this._file);
			IIterator iterator = this._download.getIterator(iteratorFilter);
			if (iterator.hasNext())
			{
				SingleRow singleRow = iterator.moveNext();
				if (this._action != "download")
				{
					XmlFormCollection form = new XmlFormCollection(this._context, "module:Download?file=" + this._file, this._myWords.Value("FORMTITLE"));
					XmlInputCaption caption = new XmlInputCaption(this._myWords.Value("FORMWARNING"));
					form.addXmlnukeObject(caption);

					XmlInputLabelField label = new XmlInputLabelField(this._myWords.Value("FORMFILE"), this.getField(singleRow, "name"));
					form.addXmlnukeObject(label);

					XmlInputTextBox textbox = new XmlInputTextBox(this._myWords.Value("LABEL_NAME"), "txtName", "", 40);
					form.addXmlnukeObject(textbox);

					textbox = new XmlInputTextBox(this._myWords.Value("LABEL_EMAIL"), "txtEmail", "", 40);
					textbox.setDataType(INPUTTYPE.EMAIL);
					form.addXmlnukeObject(textbox);

					XmlInputHidden hidden = new XmlInputHidden("action", "download");
					form.addXmlnukeObject(hidden);

					XmlInputButtons button = new XmlInputButtons();
					button.addSubmit(this._myWords.Value("FORMSUBMIT"), "");
					form.addXmlnukeObject(button);

					this._paragraph.addXmlnukeObject(form);
				}
				else
				{
					try
					{
						string message = this._myWords.Value("EMAILMESSAGE", new string[] { singleRow.getField("name") });
						System.Net.Mail.MailAddress emailto = MailUtil.getEmailFromID(this._context, singleRow.getField("emailto"));
						if (emailto != null)
						{
							MailUtil.Mail(
									this._context,
									MailUtil.getFullEmailName(this._context.ContextValue("txtName"), this._context.ContextValue("txtEmail")),
									emailto,
									this._myWords.Value("EMAILSUBJECT", new string[] { singleRow.getField("name") }),
									null, null,
									message);
						}
					}
					catch
					{
						// Just No actions 
					}
					this._context.redirectUrl(singleRow.getField("url"));
				}
			}
			else
			{
				this._paragraph.addXmlnukeObject(new XmlnukeText(this._myWords.Value("FILEERROR")));
			}
		}

		private string getField(SingleRow singleRow, string fieldName)
		{
			string result = singleRow.getField(fieldName + "_" + this._context.Language.Name.ToLower());
			if (result == "")
			{
				result = singleRow.getField(fieldName);
			}
			return result;
		}
	}
}