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

using com.xmlnuke.classes;
using com.xmlnuke.engine;
using com.xmlnuke.international;
using com.xmlnuke.processor;
using com.xmlnuke.util;

namespace com.xmlnuke.module
{
	/// <summary>
	/// SendPage is a sample module descendant from BaseModule class. 
	/// This class shows how to create a simple module to send a link from site for a friend.
	/// <p>
	/// Main features: 
	/// <ul>
	/// <li>Receive external parameters; </li>
	/// <li>Output different XML document for each parameter and action. </li>
	/// </ul>
	/// </p>
	/// <seealso cref="com.xmlnuke.module.IModule"/><seealso cref="com.xmlnuke.module.BaseModule"/>
	/// </summary>
	public class SendPage : BaseModule
	{
		private string _toName = "";

		private string _toEmail = "";

		private string _fromName = "";

		private string _fromEmail = "";

		private string _customMessage = "";

		private string _link = "";

		protected XmlnukeDocument _document;

		public SendPage()
		{
		}

		override public bool useCache()
		{
			return false;
		}

		override public void Setup(XMLFilenameProcessor xmlModuleName, Context context, object customArgs)
		{
			base.Setup(xmlModuleName, context, customArgs);

			this._link = this._context.ContextValue("link");
			this._toName = this._context.ContextValue("toname");
			this._toEmail = this._context.ContextValue("tomail");
			this._fromName = this._context.ContextValue("fromname");
			this._fromEmail = this._context.ContextValue("frommail");
			this._customMessage = this._context.ContextValue("custommessage");
			if (this._link == "")
			{
				this._link = this._context.ContextValue("HTTP_REFERER");
				if (this._link.IndexOf("sendpage") >= 0)
				{
					this._link = "";
				}
			}
		}

		override public LanguageCollection WordCollection()
		{
			LanguageCollection myWords = base.WordCollection();

			if (!(myWords.loadedFromFile()))
			{
				// English Words
				myWords.addText("en-us", "TITLE", "Module Send page");

				// Portuguese Words
				myWords.addText("pt-br", "TITLE", "Módulo de Envio de PÃ¡ginas");
			}
			return myWords;
		}

		/**
		 * CreatePage is called from module processor and decide the proper output XML.
		 *
		 * @param String showAction
		 * @param unknown_type showLink
		 * @param unknown_type showMessage
		 * @return PageXml
		 */
		override public IXmlnukeDocument CreatePage()
		{
			this._myWords = this.WordCollection();

			this._document = new XmlnukeDocument(this._myWords.Value("TITLE", new string[] { this._context.ContextValue("SERVER_NAME") }),
												 this._myWords.Value("ABSTRACT", new string[] { this._context.ContextValue("SERVER_NAME") }));

			if (this._link == "")
			{
				this.goBack(this._myWords.Value("ERRORINVALID"));
			}
			else if (this._action == "submit")
			{
				if (!XmlInputImageValidate.validateText(this._context))
				{
					this.goBack(this._myWords.Value("OBJECTIMAGEINVALID"));
				}
				else
				{
					if ((this._toName == "") || (this._toEmail == "") || (this._fromName == "") || (this._fromEmail == ""))
					{
						this.goBack(this._myWords.Value("ERROR"));
					}
					else
					{
						string custMessage = this._myWords.Value("MESSAGE", new string[] { this._toName, this._toEmail, this._link, this._fromName, this._customMessage });

						MailUtil.Mail
						(
							this._context,
							MailUtil.getFullEmailName(this._fromName, this._fromEmail),
							MailUtil.getFullEmailName(this._toName, this._toEmail),
							this._myWords.Value("SUBJECT"),
							null,
							MailUtil.getFullEmailName(this._fromEmail),
							custMessage
						);

						this.showMessage();
					}
				}
			}
			else
			{
				this.showForm();
			}

			return this._document.generatePage();
		}

		/**
		 * Show the form
		 *
		 */
		public void showForm()
		{
			XmlBlockCollection blockcenter = new XmlBlockCollection(this._myWords.Value("MSGFILL"), BlockPosition.Center);
			this._document.addXmlnukeObject(blockcenter);

			XmlParagraphCollection paragraph = new XmlParagraphCollection();
			blockcenter.addXmlnukeObject(paragraph);

			XmlFormCollection form = new XmlFormCollection(this._context, "module:sendpage", this._myWords.Value("CAPTION"));
			paragraph.addXmlnukeObject(form);

			XmlInputCaption caption = new XmlInputCaption(this._myWords.Value("INFO", new string[] { this._link }));
			form.addXmlnukeObject(caption);

			XmlInputHidden hidden = new XmlInputHidden("action", "submit");
			form.addXmlnukeObject(hidden);

			hidden = new XmlInputHidden("link", this._link);
			form.addXmlnukeObject(hidden);

			XmlInputTextBox textbox = new XmlInputTextBox(this._myWords.Value("FLDNAME"), "fromname", "", 40);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);

			textbox = new XmlInputTextBox(this._myWords.Value("FLDEMAIL"), "frommail", "", 40);
			textbox.setDataType(INPUTTYPE.EMAIL);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);

			textbox = new XmlInputTextBox(this._myWords.Value("FLDTONAME"), "toname", "", 40);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);

			textbox = new XmlInputTextBox(this._myWords.Value("FLDTOEMAIL"), "tomail", "", 40);
			textbox.setDataType(INPUTTYPE.EMAIL);
			textbox.setRequired(true);
			form.addXmlnukeObject(textbox);

			XmlInputMemo memo = new XmlInputMemo(this._myWords.Value("LABEL_MESSAGE"), "custommessage", "");
			form.addXmlnukeObject(memo);

			form.addXmlnukeObject(new XmlInputImageValidate(this._myWords.Value("TYPETEXTFROMIMAGE")));

			XmlInputButtons button = new XmlInputButtons();
			button.addSubmit(this._myWords.Value("TXT_SUBMIT"), "");
			form.addXmlnukeObject(button);
		}

		/**
		 * Go to the last page
		 *
		 */
		public void goBack(string showMessage)
		{
			XmlBlockCollection blockcenter = new XmlBlockCollection(this._myWords.Value("MSGERROR"), BlockPosition.Center);
			this._document.addXmlnukeObject(blockcenter);

			XmlParagraphCollection paragraph = new XmlParagraphCollection();
			blockcenter.addXmlnukeObject(paragraph);

			paragraph.addXmlnukeObject(new XmlnukeText(showMessage, true));

			XmlAnchorCollection anchor = new XmlAnchorCollection("javascript:history.go(-1)");
			anchor.addXmlnukeObject(new XmlnukeText(this._myWords.Value("TXT_BACK")));
			paragraph.addXmlnukeObject(anchor);
		}

		/**
		 * Show a message of the error
		 *
		 */
		public void showMessage()
		{
			XmlBlockCollection blockcenter = new XmlBlockCollection(this._myWords.Value("MSGOK"), BlockPosition.Center);
			this._document.addXmlnukeObject(blockcenter);

			XmlParagraphCollection paragraph = new XmlParagraphCollection();
			blockcenter.addXmlnukeObject(paragraph);

			paragraph.addXmlnukeObject(new XmlnukeText(_customMessage));
		}
	}

}