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

namespace com.xmlnuke.module
{
	public enum ErrorType
	{
		NotFound,
		ErrorMessage
	}

	public struct LoadErrorStructure
	{
		public ErrorType error;
		public string moduleName;
		public string errorMessage;
		public string stackTrace;
	}
	/// <summary>
	/// LoadError is a default module descendant from BaseModule class. 
	/// This class runs only if the requested module not found.
	/// </summary>
	/// <seealso cref="com.xmlnuke.module.IModule"/><seealso cref="com.xmlnuke.module.BaseModule"/><seealso cref="com.xmlnuke.module.ModuleFactory"/>
	public class LoadError : BaseModule
	{
		/// <summary>
		/// Error message
		/// </summary>
		private LoadErrorStructure errorObject;

		/// <summary>
		/// Default constructor
		/// </summary>
		public LoadError()
		{ }

		/// <summary>
		/// This method receive a external error message and show it.
		/// </summary>
		/// <param name="customArg"></param>
		override public void CustomSetup(object customArg)
		{
			this.errorObject = (LoadErrorStructure)customArg;
		}

		/// <summary>
		/// Return the LanguageCollection used in this module
		/// </summary>
		/// <returns>LanguageCollection</returns>
		override public international.LanguageCollection WordCollection()
		{
			international.LanguageCollection myWords = base.WordCollection();

			if (!myWords.loadedFromFile())
			{
				// English Words
				myWords.addText("en-us", "TITLE", "Module Requested Not Found");
				myWords.addText("en-us", "MESSAGE", "The requested module {0}");
				// Portuguese Words
				myWords.addText("pt-br", "TITLE", "Mdulo solicitado no foi encontrado");
				myWords.addText("pt-br", "MESSAGE", "O mdulo solicitado {0}");
			}

			return myWords;
		}

		/// <summary>
		/// Returns if use cache
		/// </summary>
		/// <returns>False</returns>
		override public bool useCache()
		{
			return false;
		}

		/// <summary>
		/// Output error message
		/// </summary>
		/// <returns>PageXml object</returns>
		override public classes.IXmlnukeDocument CreatePage()
		{
			base.CreatePage();
			classes.PageXml px = new classes.PageXml();
			international.LanguageCollection myWords = this.WordCollection();

			XmlNode blockcenter;
			if (errorObject.error == ErrorType.NotFound)
			{
				blockcenter = px.addBlockCenter(myWords.Value("TITLENOTFOUND"));
			}
			else
			{
				blockcenter = px.addBlockCenter(myWords.Value("TITLEERRORMESSAGE"));
			}
			XmlNode paragraph = px.addParagraph(blockcenter);
			px.addText(paragraph, myWords.Value("MESSAGE", new string[] { this.errorObject.moduleName }));
			paragraph = px.addParagraph(blockcenter);
			px.addText(paragraph, myWords.Value("ORIGINALMESSAGE", new string[] { this.errorObject.errorMessage }));
			if (errorObject.stackTrace != "")
			{
				paragraph = px.addParagraph(blockcenter);
				px.BreakLine = true;
				px.addBold(paragraph, "Stack Trace:");
				string[] lines = errorObject.stackTrace.Split('\n');
				foreach (string line in lines)
				{
					px.addText(paragraph, line);
				}
			}
			paragraph = px.addParagraph(blockcenter);
			px.addHref(paragraph, "javascript:history.go(-1)", myWords.Value("GOBACK"));
			return px;
		}

	}
}