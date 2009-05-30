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

namespace com.xmlnuke.module
{

	public class Search : BaseModule
	{

		public Search()
		{ }

		override public void Setup(processor.XMLFilenameProcessor xmlModuleName, engine.Context context, object customArgs)
		{
			base.Setup(xmlModuleName, context, customArgs);
		}

		/// <summary>
		/// Dynamic information about use cache or not.
		/// </summary>
		/// <returns>False if action request parameters is "write" and erase all cache data. Otherwise return true.</returns>
		override public bool useCache()
		{
			return false;
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
				myWords.addText("en-us", "TITLE", "Search Site %s");
				myWords.addText("en-us", "ABSTRACT", "Search for words in website");
				myWords.addText("en-us", "PAGETITLE", "Search for words in website");
				myWords.addText("en-us", "PAGETEXT", "Search for words explanation");
				myWords.addText("en-us", "BLOCKSEARCH", "Search Filter");
				myWords.addText("en-us", "txtSearch", "Search For: ");
				myWords.addText("en-us", "chkAll", "In any document");
				myWords.addText("en-us", "BLOCKRESULT", "Search Result");
				myWords.addText("en-us", "NOTITLE", "No Title");
				myWords.addText("en-us", "NOTFOUND", "No documents were found.");
				myWords.addText("en-us", "SUBMIT", "Submit");

				// Portuguese Words
				myWords.addText("pt-br", "TITLE", "Procurar no site %s");
				myWords.addText("pt-br", "ABSTRACT", "Procurar por palavras no site");
				myWords.addText("pt-br", "PAGETITLE", "Palavras a pesquisar");
				myWords.addText("pt-br", "PAGETEXT", "Voc poder pesquisar palavras existentes nos pginas do WebSite. Esse mecanismo no efetua busca nos mdulos, documentos ou imagens do site.");
				myWords.addText("pt-br", "BLOCKSEARCH", "Critrios de Pesquisa");
				myWords.addText("pt-br", "txtSearch", "Procurar Por: ");
				myWords.addText("pt-br", "chkAll", "Em qualquer documento");
				myWords.addText("pt-br", "BLOCKRESULT", "Resultado da pesquisa");
				myWords.addText("pt-br", "NOTITLE", "Sem ttulo");
				myWords.addText("pt-br", "NOTFOUND", "Nenhum documento foi localizado");
				myWords.addText("pt-br", "SUBMIT", "Buscar");
			}
			return myWords;
		}

		/// <summary>
		/// CreatePage is called from module processor only if doesnt use cache or doesnt exist cache file and decide the proper output XML.
		/// </summary>
		/// <returns>XML object</returns>
		override public classes.IXmlnukeDocument CreatePage()
		{
			base.CreatePage();
			classes.PageXml px = new classes.PageXml();
			XmlNode paragraph;

			international.LanguageCollection myWords = this.WordCollection();

			px.Title = myWords.Value("TITLE", new string[] { this._context.ContextValue("SERVER_NAME") });
			px.Abstract = myWords.Value("ABSTRACT", new string[] { this._context.ContextValue("SERVER_NAME") });
			//px.Keyword = "guestbook";

			XmlNode blockcenter = px.addBlockCenter(myWords.Value("PAGETITLE"));

			paragraph = px.addParagraph(blockcenter);
			px.addText(paragraph, myWords.Value("PAGETEXT"));

			XmlNode form = px.addForm(blockcenter, "module:search?action=search", myWords.Value("BLOCKSEARCH"), "frm", true);

			px.addTextBox(form, myWords.Value("txtSearch"), "txtSearch", this._context.ContextValue("txtSearch"), 40, true, com.xmlnuke.classes.INPUTTYPE.TEXT, "", "", "", "");
			px.addCheckBox(form, myWords.Value("chkAll"), "chkAll", "all");

			XmlNode boxButton = px.addBoxButtons(form);
			px.addSubmit(boxButton, "", myWords.Value("SUBMIT"));

			if (this._action.ToLower() == "search")
			{
				blockcenter = px.addBlockCenter(myWords.Value("BLOCKRESULT"));

				ArrayList arr =
					this._context.getXMLDataBase().searchDocuments(
						this._context.ContextValue("txtSearch"),
						this._context.ContextValue("chkAll") != ""
					);

				if (arr == null)
				{
					paragraph = px.addParagraph(blockcenter);
					px.addText(paragraph, myWords.Value("NOTFOUND"));
				}
				else
				{
					XmlDocument docResult;
					XmlNode nodeResult;
					string titulo;
					string abstr;
					string singleName;

					ArrayList nodeTitleList = new ArrayList();
					nodeTitleList.Add("/page/meta/title");

					ArrayList nodeAbstractList = new ArrayList();
					nodeAbstractList.Add("/page/meta/abstract");

					// configSearch File
					processor.AnydatasetFilenameProcessor configSearchFile = new processor.AnydatasetFilenameProcessor("_configsearch", this._context);
					anydataset.AnyDataSet configSearch = new com.xmlnuke.anydataset.AnyDataSet(configSearchFile);
					anydataset.SingleRow sr;
					anydataset.Iterator it;
					it = configSearch.getIterator();
					while (it.hasNext())
					{
						sr = it.moveNext();
						nodeTitleList.Add(sr.getField("nodetitle"));
						nodeAbstractList.Add(sr.getField("nodeabstract"));
					}

					foreach (string s in arr)
					{
						singleName = processor.FilenameProcessor.StripLanguageInfo(s);

						try
						{
							docResult = this._context.getXMLDataBase().getDocument(s);
							nodeResult = getNode(nodeTitleList, docResult);
							titulo = (nodeResult == null) ? myWords.Value("NOTITLE") : nodeResult.InnerXml;
							nodeResult = getNode(nodeAbstractList, docResult);
							abstr = (nodeResult == null) ? "" : nodeResult.InnerXml;

							paragraph = px.addParagraph(blockcenter);
							px.addHref(paragraph, "engine:xmlnuke?xml=" + singleName, titulo);
							px.addText(paragraph, " [");
							px.addHref(paragraph, "engine:xmlnuke?xml=" + singleName + "&xsl=rawxml", "xml");
							px.BreakLine = true;
							px.addText(paragraph, "]");
							px.addText(paragraph, abstr);
							px.BreakLine = false;
						}
						catch (System.IO.FileNotFoundException)
						{
							paragraph = px.addParagraph(blockcenter);
							px.addText(paragraph, s + " (" + myWords.Value("NOTITLE") + ")");
						}
					}

					paragraph = px.addParagraph(blockcenter);
					px.addBold(paragraph, myWords.Value("DOCFOUND", new string[] { arr.Count.ToString() }));

				}
				//this.addMessageToDB( guestbook, this._context.ContextValue("txtName"), this._context.ContextValue("txtEmail"), this._context.ContextValue("txtMessage") );
				//guestbook = new anydataset.AnyDataSet(guestbookFile); // Mono 0.26 need reload because lost reference... I dont know why!!
			}

			return px;
		}

		private XmlNode getNode(ArrayList list, XmlDocument doc)
		{
			XmlNode result;
			foreach (string item in list)
			{
				result = doc.SelectSingleNode(item);
				if (result != null)
				{
					return result;
				}
			}

			return null;
		}


	}
}