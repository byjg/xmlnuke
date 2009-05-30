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

using com.xmlnuke.classes;
using com.xmlnuke.util;

namespace com.xmlnuke.module
{
	/// <summary>
	/// SendEmail is a sample module descendant from BaseModule class. 
	/// This class shows how to create a simple module to send a email from Xmlnuke site. 
	/// <p>
	/// Main features: 
	/// <ul>
	/// <li>Receive external parameters; </li>
	/// <li>Output different XML document for each parameter and action; </li>
	/// <li>Smtp send mail. </li>
	/// </ul>
	/// </p>
	/// <seealso cref="com.xmlnuke.module.IModule"/><seealso cref="com.xmlnuke.module.BaseModule"/>
	/// </summary>
	public class SendEmail : BaseModule
	{
		private string toName_ID = "";
		private string fromName = "";
		private string fromEmail = "";
		private string subject = "";
		private string message = "";
		private string redirect = "";
		private string extraMessage = "";

		/// <summary>
		/// Default constructor
		/// </summary>
		public SendEmail()
		{ }

		/// <summary>
		/// Returns if use cache
		/// </summary>
		/// <returns>False</returns>
		override public bool useCache()
		{
			return false;
		}

		/// <summary>
		/// Setup the module receiving external parameters and assing it to private variables.
		/// </summary>
		/// <param name="xmlModuleName">Module name</param>
		/// <param name="context">Xmlnuke conext</param>
		/// <param name="customArgs">Null</param>
		override public void Setup(processor.XMLFilenameProcessor xmlModuleName, engine.Context context, object customArgs)
		{
			base.Setup(xmlModuleName, context, customArgs);
			toName_ID = this._context.ContextValue("toname_id");
			fromName = this._context.ContextValue("name");
			fromEmail = this._context.ContextValue("email");
			subject = this._context.ContextValue("subject");
			message = this._context.ContextValue("message");
			redirect = this._context.ContextValue("redirect");
			extraMessage = "";
			string aux = this._context.ContextValue("extra_fields");
			if (aux != "")
			{
				string[] fields = aux.Split(';');
				foreach (string field in fields)
				{
					string[] detail = field.Split('=');
					string valor = this._context.ContextValue(detail[0]);
					this.extraMessage += detail[1] + ": " + valor + "\n";
				}
			}
		}

		/// <summary>
		/// Return the LanguageCollection used in this module
		/// </summary>
		/// <returns></returns>
		override public international.LanguageCollection WordCollection()
		{
			international.LanguageCollection myWords = base.WordCollection();

			if (!myWords.loadedFromFile())
			{
				// English Words
				myWords.addText("en-us", "TITLE", "XMLNuke Send email from %s - Response");
				// Portuguese Words
				myWords.addText("pt-br", "TITLE", "XMLNuke Enviar email do site %s - Resposta");
			}

			return myWords;
		}

		/// <summary>
		/// CreatePage is called from module processor and decide the proper output XML.
		/// </summary>
		/// <returns>XML object</returns>
		override public classes.IXmlnukeDocument CreatePage()
		{
			base.CreatePage();
			classes.PageXml px = new classes.PageXml();

			NameValueCollection ht = new NameValueCollection();
			bool hasError = false;
			international.LanguageCollection myWords = this.WordCollection();

			if (this.fromName == "")
			{
				ht.Add(myWords.Value("FLDNAME"), myWords.Value("ERRORBLANK"));
				hasError = true;
			}
			if (this.fromEmail == "")
			{
				ht.Add(myWords.Value("FLDEMAIL"), myWords.Value("ERRORBLANK"));
				hasError = true;
			}
			else
			{
				if (this.fromEmail.IndexOf("@") < 0)
				{
					ht.Add(myWords.Value("FLDEMAIL"), myWords.Value("FLDEMAIL") + " " + myWords.Value("ERRORINVALID"));
					hasError = true;
				}
			}
			if (this.subject == "")
			{
				ht.Add(myWords.Value("FLDSUBJECT"), myWords.Value("ERRORBLANK"));
				hasError = true;
			}
			if (this.message == "")
			{
				ht.Add(myWords.Value("FLDMESSAGE"), myWords.Value("ERRORBLANK"));
				hasError = true;
			}

			if (hasError)
			{
				return this.CreatePage(px, myWords.Value("MSGERROR"), ht);
			}
			else if (!XmlInputImageValidate.validateText(this._context))
			{
				XmlnukeDocument document = new XmlnukeDocument(myWords.Value("TITLE", new string[] { this._context.ContextValue("SERVER_NAME") }), myWords.Value("ABSTRACT", new string[] { this._context.ContextValue("SERVER_NAME") }));
				XmlBlockCollection blockcenter = new XmlBlockCollection(myWords.Value("MSGERROR"), BlockPosition.Center);
				document.addXmlnukeObject(blockcenter);

				XmlFormCollection form = new XmlFormCollection(this._context, "module:sendemail", myWords.Value("MSGERROR"));
				form.addXmlnukeObject(new XmlInputCaption(myWords.Value("RETRYVALIDATE")));
				form.addXmlnukeObject(new XmlInputHidden("toname_id", this._context.ContextValue("toname_id")));
				form.addXmlnukeObject(new XmlInputHidden("name", this._context.ContextValue("name")));
				form.addXmlnukeObject(new XmlInputHidden("email", this._context.ContextValue("email")));
				form.addXmlnukeObject(new XmlInputHidden("subject", this._context.ContextValue("subject")));
				form.addXmlnukeObject(new XmlInputHidden("message", this.extraMessage + this._context.ContextValue("message")));
				form.addXmlnukeObject(new XmlInputHidden("redirect", this._context.ContextValue("redirect")));
				form.addXmlnukeObject(new XmlInputImageValidate(""));
				XmlInputButtons buttons = new XmlInputButtons();
				buttons.addSubmit(myWords.Value("RETRY"), "");
				form.addXmlnukeObject(buttons);
				blockcenter.addXmlnukeObject(form);

				return document.generatePage();
			}
			else
			{
				MailUtil.Mail(this._context,
					MailUtil.getFullEmailName(this.fromName, this.fromEmail),
					MailUtil.getEmailFromID(this._context, this.toName_ID),
					this.subject,
					null,
					MailUtil.getFullEmailName(this.fromEmail),
					this.extraMessage + this.message);

				if (this.redirect != "")
				{
					// Redirect Here!!
					//Response.End
					return px;
				}
				else
				{
					ht.Add(myWords.Value("FLDNAME"), this.fromName + " <" + this.fromEmail + ">");
					ht.Add(myWords.Value("FLDSUBJECT"), this.subject);
					ht.Add(myWords.Value("FLDMESSAGE"), this.message);
					return this.CreatePage(px, myWords.Value("MSGOK"), ht);
				}
			}
		}

		/// <summary>
		/// Create the PageXml object from CreatePage() parameters
		/// </summary>
		/// <param name="px">PageXml created at CreatePage</param>
		/// <param name="title">Page header</param>
		/// <param name="ht">Action list</param>
		/// <returns>PageXml object</returns>
		private classes.PageXml CreatePage(classes.PageXml px, string title, NameValueCollection ht)
		{
			international.LanguageCollection myWords = this.WordCollection();

			px.Title = myWords.Value("TITLE", new string[] { this._context.ContextValue("SERVER_NAME") });
			px.Abstract = myWords.Value("ABSTRACT", new string[] { this._context.ContextValue("SERVER_NAME") });
			//px.Keyword = "email";

			XmlNode blockcenter = px.addBlockCenter(myWords.Value("TITRESP"));
			XmlNode paragraph = px.addParagraph(blockcenter);
			px.addText(paragraph, " ");
			paragraph = px.addParagraph(blockcenter);
			px.addBold(paragraph, title);
			paragraph = px.addParagraph(blockcenter);
			foreach (string key in ht)
			{
				px.BreakLine = false;
				px.addBold(paragraph, key);
				px.BreakLine = true;
				px.addText(paragraph, " " + ht[key]);
			}

			paragraph = px.addParagraph(blockcenter);
			px.addHref(paragraph, "javascript:history.go(-1)", myWords.Value("TXT_BACK"));

			return px;
		}
	}
}