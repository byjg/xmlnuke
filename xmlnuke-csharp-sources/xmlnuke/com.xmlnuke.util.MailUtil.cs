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
using System.Text.RegularExpressions;
using System.Net.Mail;
using System.Collections;

using com.xmlnuke.classes;

namespace com.xmlnuke.util
{
	public class MailUtil
	{
		/// <summary>
		/// Send email using Smtp send mail object.
		/// </summary>
		/// <param name="context">XmlNuke context</param>
		/// <param name="fromEmail">Sender email</param>
		/// <param name="toEmail">Recipient email</param>
		/// <param name="subject">Subject message</param>
		/// <param name="cc">Carbon copy recipient email</param>
		/// <param name="bcc">Blind carbon copy recipient email</param>
		/// <param name="body">Body message</param>
		/// <param name="htmlemail">Send HTML email to user</param>
		public static void Mail(engine.Context context, MailAddress fromEmail, MailAddress toEmail, string subject, MailAddress cc, MailAddress bcc, string body, bool htmlemail)
		{
			MailUtil.Mail(context, fromEmail, toEmail, subject, cc, bcc, body, htmlemail, null);
		}

		public static void Mail(engine.Context context, MailAddress fromEmail, MailAddress toEmail, string subject, MailAddress cc, MailAddress bcc, string body)
		{
			MailUtil.Mail(context, fromEmail, toEmail, subject, cc, bcc, body, false);
		}

		/// <summary>
		/// Send email using Smtp send mail object.
		/// </summary>
		/// <param name="context">XmlNuke context</param>
		/// <param name="fromEmail">Sender email</param>
		/// <param name="toEmail">Recipient email</param>
		/// <param name="subject">Subject message</param>
		/// <param name="cc">Carbon copy recipient email</param>
		/// <param name="bcc">Blind carbon copy recipient email</param>
		/// <param name="body">Body message</param>
		/// <param name="htmlemail">Send HTML email to user</param>
		/// <param name="attachments">Arraylist of System.Net.Attachments objects</param>
		public static void Mail(engine.Context context, MailAddress fromEmail, MailAddress toEmail, string subject, MailAddress cc, MailAddress bcc, string body, bool htmlemail, ArrayList attachments)
		{
			if (toEmail == null)
			{
				throw new Exception("Destination Email was not provided");
			}

			if (fromEmail == null)
			{
				throw new Exception("Source Email for was not provided");
			}

			MailMessage mail = new MailMessage(fromEmail, toEmail);

			// Write the users subject to the subject of the email
			mail.SubjectEncoding = System.Text.Encoding.UTF8;
			mail.Subject = subject;

			if (attachments != null)
			{
				foreach (object o in attachments)
				{
					if (o is Attachment)
						mail.Attachments.Add((Attachment)o);
					else
						throw new Exception("Attachment must be a collection of System.Net.Attachment object");
				}
			}

			if (cc != null) mail.CC.Add(cc);
			if (bcc != null) mail.Bcc.Add(bcc);

			// Declare what address the message is being sent to
			// The Addressee is set to the inputted value
			mail.Headers.Add("X-Mailer", context.XmlNukeVersion);
			mail.Headers.Add("X-Host", context.ContextValue("SERVER_NAME"));
			mail.IsBodyHtml = htmlemail;

			// Write the users message to the body of the email
			//body += "\n\n--------\n" +
			//	"eMail sent from site " + context.ContextValue("SERVER_NAME") + " at " + DateTime.Now.ToString() + 
			//	"\nuser " + context.ContextValue("Remote_Host") +
			//	"\nengine " + context.XmlNukeVersion +
			//	"\nxmlnuke.com";

			mail.Body = body;

			// Getting information from WebConfig
			string configSmtpHost = context.ContextValue("xmlnuke.SMTPSERVER");
			Regex smtpHostRegEx = new Regex(@"((?<user>[\w012345679]+):(?<pass>[\w012345679]+)@)?(?<server>[\w012345679.]+[\w012345679])(:(?<port>[0-9]+))?");
			Match smtpHostMatch = smtpHostRegEx.Match(configSmtpHost);

			SmtpClient smtp = new SmtpClient(smtpHostMatch.Groups["server"].Value);
			if (smtpHostMatch.Groups["port"].Value != "")
			{
				smtp.Port = Convert.ToInt32(smtpHostMatch.Groups["port"].Value);
			}
			if (smtpHostMatch.Groups["user"].Value != "")
			{
				smtp.Credentials = new System.Net.NetworkCredential(smtpHostMatch.Groups["user"].Value, smtpHostMatch.Groups["pass"].Value);
			}

			smtp.Send(mail);
		}

		public static MailAddress getEmailFromID(engine.Context context, string IDEmail)
		{
			processor.AnydatasetFilenameProcessor configFile = new processor.AnydatasetFilenameProcessor("_configemail", context);
			anydataset.AnyDataSet config = new anydataset.AnyDataSet(configFile);
			anydataset.IteratorFilter filter = new anydataset.IteratorFilter();
			filter.addRelation("destination_id", anydataset.Relation.Equal, IDEmail);
			anydataset.Iterator it = config.getIterator(filter);
			if (it.hasNext())
			{
				anydataset.SingleRow data = it.moveNext();
				return MailUtil.getFullEmailName(data.getField("name"), data.getField("email"));
			}
			else
			{
				return null;
			}
		}

		public static MailAddress getFullEmailName(string name, string email)
		{
			return new MailAddress(email, name, System.Text.Encoding.UTF8);
		}

		public static MailAddress getFullEmailName(string email)
		{
			return new MailAddress(email);
		}
	}
}