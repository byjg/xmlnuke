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
using System.Web.Mail;

using com.xmlnuke.engine;
using com.xmlnuke.classes;
using com.xmlnuke.anydataset;
using com.xmlnuke.processor;
using com.xmlnuke.international;
using com.xmlnuke.util;

namespace com.xmlnuke.module
{
	/// <summary>
	/// Guestbook is a sample module descendant from BaseModule class. 
	/// This class shows how to create a simple module to receive data and store it in a anydataset file.
	/// <p>
	/// Main features: 
	/// <ul>
	/// <li>Receive external parameters; </li>
	/// <li>Sample use cache; </li>
	/// <li>Anydataset to load/store data. </li>
	/// </ul>
	/// </p>
	/// <seealso cref="com.xmlnuke.module.IModule"/><seealso cref="com.xmlnuke.module.BaseModule"/>
	/// </summary>
	public class Guestbook : BaseModule
	{
		// Guestbook File
		private AnydatasetFilenameProcessor guestbookFile;

		// Cache File
		private XMLCacheFilenameProcessor cacheFile;

		// Default Constructor
		public Guestbook()
		{ }

		/// <summary>
		/// Setup the module receiving external parameters and assing it to private variables.
		/// </summary>
		/// <param name="xmlModuleName"></param>
		/// <param name="context"></param>
		/// <param name="customArgs"></param>
		override public void Setup(XMLFilenameProcessor xmlModuleName, Context context, object customArgs)
		{
			base.Setup(xmlModuleName, context, customArgs);
			this.guestbookFile = new AnydatasetFilenameProcessor("guestbook", context);
			this.cacheFile = new XMLCacheFilenameProcessor("guestbook", context);
		}

		/// <summary>
		/// Dynamic information about use cache or not.
		/// </summary>
		/// <returns>bool</returns>
		override public bool useCache()
		{
			if (this._action.ToLower() == "write")
			{
				FileUtil.DeleteFilesFromPath(this.cacheFile);
				return false;
			}
			else
			{
				return base.useCache(); // base.useCache always return true, except when receive reset or nocache parameters
			}
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
				myWords.addText("en-us", "TITLE", "Module Guestbook");

			}
			return myWords;
		}

		/**
		 * CreatePage is called from module processor only if doesnt use cache or doesnt exist cache file and decide the proper output XML.
		 *
		 * @return PageXml
		 */
		override public IXmlnukeDocument CreatePage()
		{
			LanguageCollection myWords = this.WordCollection();

			XmlnukeDocument document = new XmlnukeDocument(myWords.Value("TITLE", new string[] { this._context.ContextValue("SERVER_NAME") }), myWords.Value("ABSTRACT", new string[] { this._context.ContextValue("SERVER_NAME") }));
			XmlBlockCollection blockCenter;
			XmlParagraphCollection paragraph;

			AnyDataSet guestbook = new AnyDataSet(this.guestbookFile);

			if (this._action.ToLower() == "write")
			{
				if (XmlInputImageValidate.validateText(this._context))
				{
					string message = this._context.ContextValue("txtMessage");
					string name = this._context.ContextValue("txtName");
					if ((name == "") || (message == ""))
					{
						blockCenter = new XmlBlockCollection(myWords.Value("ERRORTITLE"), BlockPosition.Center);
						document.addXmlnukeObject(blockCenter);

						paragraph = new XmlParagraphCollection();
						blockCenter.addXmlnukeObject(paragraph);

						paragraph.addXmlnukeObject(new XmlnukeText(myWords.Value("ERRORMESSAGE"), true));
					}
					else
					{
						this.addMessageToDB(guestbook, this._context.ContextValue("txtName"), this._context.ContextValue("txtEmail"), this._context.ContextValue("txtMessage"));
					}
				}
				else
				{
					blockCenter = new XmlBlockCollection(myWords.Value("ERRORTITLE"), BlockPosition.Center);
					document.addXmlnukeObject(blockCenter);
					paragraph = new XmlParagraphCollection();
					paragraph.addXmlnukeObject(new XmlnukeText(myWords.Value("OBJECTIMAGEINVALID"), true));
					blockCenter.addXmlnukeObject(paragraph);
				}
			}

			blockCenter = new XmlBlockCollection(myWords.Value("MYGUEST"), BlockPosition.Center);
			document.addXmlnukeObject(blockCenter);

			IIterator iterator = guestbook.getIterator(null);
			while (iterator.hasNext())
			{
				SingleRow singleRow = iterator.moveNext();
				this.defineMessage(blockCenter, singleRow);
			}

			blockCenter = new XmlBlockCollection(myWords.Value("SIGN"), BlockPosition.Center);
			document.addXmlnukeObject(blockCenter);

			paragraph = new XmlParagraphCollection();
			blockCenter.addXmlnukeObject(paragraph);

			XmlFormCollection form = new XmlFormCollection(this._context, "module:guestbook?action=write", myWords.Value("FILL"));
			XmlInputTextBox textbox = new XmlInputTextBox(myWords.Value("LABEL_NAME"), "txtName", "", 30);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);

			textbox = new XmlInputTextBox(myWords.Value("LABEL_EMAIL"), "txtEmail", "", 30);
			textbox.setDataType(INPUTTYPE.EMAIL);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);

			XmlInputMemo memo = new XmlInputMemo(myWords.Value("LABEL_MESSAGE"), "txtMessage", "");
			memo.setSize(40, 4);
			form.addXmlnukeObject(memo);

			form.addXmlnukeObject(new XmlInputImageValidate(myWords.Value("TYPETEXTFROMIMAGE")));

			XmlInputButtons button = new XmlInputButtons();
			button.addSubmit(myWords.Value("TXT_SUBMIT"), "");
			form.addXmlnukeObject(button);

			paragraph.addXmlnukeObject(form);

			return document.generatePage();
		}


		/// <summary>
		/// Auxiliary function do add data to Anydataset and save it.
		/// </summary>
		/// <param name="anydata">Anydata object</param>
		/// <param name="fromName">Guestbook writer name</param>
		/// <param name="fromMail">Guestbook writer email</param>
		/// <param name="message">Guestbook message</param>
		private void addMessageToDB(AnyDataSet anydata, string fromName, string fromMail, string message)
		{
			anydata.insertRowBefore(0);
			anydata.addField("fromname", fromName);
			anydata.addField("frommail", fromMail);
			anydata.addField("message", message);
			anydata.addField("date", DateTime.Now);
			anydata.addField("ip", this._context.ContextValue("REMOTE_ADDR"));
			anydata.Save(this.guestbookFile);

			try
			{
				MailUtil.Mail(this._context,
								MailUtil.getFullEmailName(fromName, fromMail),
								MailUtil.getEmailFromID(this._context, "DEFAULT"),
								"[Xmlnuke Guestbook] Message Added",
								null, null,
								message);
			}
			catch
			{
				; // Just No actions
			}
		}

		/// <summary>
		/// Auxiliary function do setup each message from guestbook
		/// </summary>
		/// <param name="blockCenter">XmlBlockCollection</param>
		/// <param name="singleRow">SingleRow</param>
		private void defineMessage(XmlBlockCollection blockCenter, SingleRow singleRow)
		{
			XmlParagraphCollection paragraph = new XmlParagraphCollection();
			blockCenter.addXmlnukeObject(paragraph);

			paragraph.addXmlnukeObject(new XmlnukeBreakLine());
			string email = singleRow.getField("frommail");
			int emailStr = email.IndexOf('@');
			email = "xxxxx" + (emailStr >= 0 ? email.Substring(emailStr) : email);
			string text = singleRow.getField("fromname") + " (" + email + ")";
			paragraph.addXmlnukeObject(new XmlnukeText(text, true));

			paragraph.addXmlnukeObject(new XmlnukeBreakLine());
			paragraph.addXmlnukeObject(new XmlnukeText(singleRow.getField("date"), false, true));

			paragraph.addXmlnukeObject(new XmlnukeBreakLine());
			paragraph.addXmlnukeObject(new XmlnukeText(singleRow.getField("message")));

			paragraph.addXmlnukeObject(new XmlnukeBreakLine());
			paragraph.addXmlnukeObject(new XmlnukeText(singleRow.getField("ip"), false, true));
		}
	}

}