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
	/// <summary>
	/// XSLTheme is a default module descendant from BaseModule class. 
	/// This class shows/edit the profile from the current user.
	/// </summary>
	/// <seealso cref="com.xmlnuke.module.IModule"/><seealso cref="com.xmlnuke.module.BaseModule"/><seealso cref="com.xmlnuke.module.ModuleFactory"/>
	public class XSLTheme : BaseModule
	{
		/// <summary>
		/// Default constructor
		/// </summary>
		public XSLTheme()
		{ }

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
			LanguageCollection myWords = this.WordCollection();

			this.defaultXmlnukeDocument = new XmlnukeDocument(myWords.Value("TITLE"), myWords.Value("ABSTRACT"));

			XmlBlockCollection blockcenter = new XmlBlockCollection(myWords.Value("TITLE"), BlockPosition.Center);
			this.defaultXmlnukeDocument.addXmlnukeObject(blockcenter);

			XmlParagraphCollection paragraph = new XmlParagraphCollection();
			blockcenter.addXmlnukeObject(paragraph);

			ArrayList xslUsed = new ArrayList();

			XSLFilenameProcessor xsl = new XSLFilenameProcessor("", this._context);
			string[] filelist = FileUtil.RetrieveFilesFromFolder(xsl.PrivatePath(), "*" + xsl.Extension());
			this.generateList(myWords.Value("LISTPERSONAL"), paragraph, filelist, xslUsed, xsl);

			filelist = FileUtil.RetrieveFilesFromFolder(xsl.SharedPath(), "*" + xsl.Extension());
			this.generateList(myWords.Value("LISTGENERIC"), paragraph, filelist, xslUsed, xsl);

			return this.defaultXmlnukeDocument.generatePage();
		}

		private void generateList(string caption, XmlParagraphCollection paragraph, string[] filelist, ArrayList xslUsed, XSLFilenameProcessor xsl)
		{
			paragraph.addXmlnukeObject(new XmlnukeText(caption, true));

			XmlListCollection listCollection = new XmlListCollection(XmlListType.UnorderedList);
			paragraph.addXmlnukeObject(listCollection);

			foreach (string file in filelist)
			{
				XmlAnchorCollection anchor;

				string xslname = FileUtil.ExtractFileName(file);
				xslname = xsl.removeLanguage(xslname);
				if (!xslUsed.Contains(xslname))
				{
					XmlnukeSpanCollection objectList = new XmlnukeSpanCollection();
					listCollection.addXmlnukeObject(objectList);

					xslUsed.Add(xslname);
					if (xslname == "index")
					{
						anchor = new XmlAnchorCollection("engine:xmlnuke?xml=index&xsl=index");
						anchor.addXmlnukeObject(new XmlnukeText(xslname, true));
						objectList.addXmlnukeObject(anchor);
					}
					else
					{
						anchor = new XmlAnchorCollection("module:XSLTheme?xsl=" + xslname);
						anchor.addXmlnukeObject(new XmlnukeText(xslname, true));
						objectList.addXmlnukeObject(anchor);
					}
				}
			}
		}

	}
}