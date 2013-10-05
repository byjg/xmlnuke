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
using System.Xml.XPath;
using System.Xml.Xsl;
using com.xmlnuke.admin;
using com.xmlnuke.util;
using System.Text.RegularExpressions;
using com.xmlnuke.classes;

namespace com.xmlnuke.engine
{
    // Output Result for XML processing
    public enum OutputResult
    {
        XHtml,
        Xml, 
        Json
    }

	/// <summary>
	/// XmlnukeEngine class use a Facade Design Pattern. This class call all of other xmlnuke classes and return the XML/XSL processed
	/// </summary>
	public class XmlnukeEngine
	{
		private engine.Context _context = null;

		protected OutputResult _outputResult = OutputResult.XHtml;

		protected string _extractNodes;

		protected string _extractNodesRoot;

		/// <summary>
		/// ParamProcessor constructor. 
		/// </summary>
		/// <param name="context">The com.xmlnuke.engine.Context class</param>
		public XmlnukeEngine(engine.Context context)
			: this(context, OutputResult.XHtml, "", "xmlnuke")
		{ }

		public XmlnukeEngine(engine.Context context, OutputResult outputResult, string extractNodes)
			: this(context, outputResult, extractNodes, "xmlnuke")
		{ }

		public XmlnukeEngine(engine.Context context, OutputResult outputResult, string extractNodes, string extractNodesRoot)
		{
			this._context = context;
			this._outputResult = outputResult;
			this._extractNodes = extractNodes;
			this._extractNodesRoot = extractNodesRoot;
		}

		/// <summary>
		/// Transform XML/XSL documents from the current XMLNuke Context. 
		/// </summary>
		/// <returns>Return the XHTML result</returns>
		public string TransformDocument()
		{
			// Creating FileNames will be used in this functions.
			processor.XMLCacheFilenameProcessor xmlCacheFile = new processor.XMLCacheFilenameProcessor(_context.Xml, _context);

			// Check if file cache already exists
			// If exists read it from there;
			if (util.FileUtil.Exists(xmlCacheFile.FullQualifiedNameAndPath()) && !this._context.NoCache && !this._context.Reset && (this._outputResult == OutputResult.XHtml))
			{
				return util.FileUtil.QuickFileRead(xmlCacheFile.FullQualifiedNameAndPath());
			}
			// If not exists process XML/XSL file now;
			else
			{
				// Creating FileNames will be used in this functions.
				processor.XMLFilenameProcessor xmlFile = new processor.XMLFilenameProcessor(_context.Xml, _context);

				// Transform Document
				string result = TransformDocument(getXmlDocument(xmlFile));

				// Save cache file - NOCACHE: Doesn't Save; Otherwise: Allways save
                if (!this._context.NoCache && (this._outputResult == OutputResult.XHtml))
				{
					util.FileUtil.QuickFileWrite(xmlCacheFile.FullQualifiedNameAndPath(), result);
				}

				return result;
			}
		}

		/// <summary>
		/// Transform XML/XSL documents from the user module process result. 
		/// </summary>
		/// <param name="module">User module interface</param>
		/// <returns>Return the XHTML result</returns>
		public string TransformDocument(module.IModule module)
		{
			bool useCache = module.useCache();
			string result;

            if (!module.hasInCache() || !useCache || (this._outputResult != OutputResult.XHtml))
			{
				classes.IXmlnukeDocument px = module.CreatePage();
				XmlDocument xmlDoc = px.makeDomObject();
				XmlNode nodePage = xmlDoc.DocumentElement;
				this.addXMLDefault(nodePage);

				string admin_page = "admin" + util.FileUtil.Slash() + "admin_page";
				string admin_index = "admin" + util.FileUtil.Slash() + "admin_index";
				if (!(module is admin.IAdmin))
				{
					if ((this._context.Xsl.IndexOf(admin_page) < 0) && (this._context.Xsl.IndexOf(admin_index) < 0))
					{
						result = TransformDocument(xmlDoc);
					}
					else
					{
						processor.XSLFilenameProcessor xslFile = new processor.XSLFilenameProcessor(this._context.ContextValue("xmlnuke.DEFAULTPAGE"), _context);
						result = TransformDocument(xmlDoc, xslFile);
					}
				}
				else
				{
					processor.XSLFilenameProcessor xslFile = new processor.XSLFilenameProcessor(admin_page, _context);
					xslFile.FilenameLocation = processor.ForceFilenameLocation.PathFromRoot;
					xslFile.UseFileFromAnyLanguage();
					result = TransformDocument(xmlDoc, xslFile);
				}

				if (useCache && (this._outputResult == OutputResult.XHtml))
				{
					module.saveToCache(result);
				}
			}
			else
			{
				result = module.getFromCache();
			}
			module.finalizeModule();
			return result;
		}

		/// <summary>
		/// Private method used to add accessories XML (_all and index) into current XML file. Runtime only.
		/// </summary>
		/// <param name="nodePage">Node page</param>
		private void addXMLDefault(XmlNode nodePage)
		{
			// Creating FileNames will be used in this functions.
			processor.XMLFilenameProcessor allFile = new processor.XMLFilenameProcessor("_all", _context);
			processor.XMLFilenameProcessor indexFile = new processor.XMLFilenameProcessor("index", _context);

			util.XmlUtil.AddNodeFromFile(nodePage, allFile, "page");
			util.XmlUtil.AddNodeFromFile(nodePage, indexFile, "xmlindex");
		}

		/// <summary>
		/// Transform XML/XSL documents from custom XmlDocument. 
		/// </summary>
		/// <param name="xml">XmlDocument</param>
		/// <returns>Return the XHTML result</returns>
		public string TransformDocument(XmlDocument xml)
		{
			// This method didnt have ADMIN/ADMIN_PAGE
			//if (_context.Xsl == "admin" + util.FileUtil.Slash() + "admin_page")
			//{
			//	_context.Xsl = _context.ContextValue("xmlnuke.DEFAULTPAGE");
			//}

			// Creating FileNames will be used in this functions.
			processor.XSLFilenameProcessor xslFile = new processor.XSLFilenameProcessor(_context.Xsl, _context);

			return this.TransformDocument(xml, xslFile);
		}

		/// <summary>
		/// Transform an XMLDocument object with an XSLFile
		/// </summary>
		/// <param name="xml">XMLDocument</param>
		/// <param name="xslFile">XSL File</param>
		/// <returns>The transformation string</returns>
		public string TransformDocument(XmlDocument xml, processor.XSLFilenameProcessor xslFile)
		{
		    // Add a custom XML based on attribute xmlobjet inside root
		    // Example:
		    // <page include="base.namespace, file.php" xmlobject="plugin.name[param1, param2]">
		    string pattern = @"/(?<plugin>((\w+)\.)+\w+)\[(?<param>([#']?[\w]+[#']?\s*,?\s*)+)\]/";
		    XmlElement xmlRoot = xml.DocumentElement;
		    XmlAttributeCollection xmlRootAttributes = xmlRoot.Attributes;
            foreach (XmlAttribute attr in xmlRootAttributes)
            {
                /*
			    if (attr.Name == "include")
			    {
				    $param = explode(",", $attr->nodeValue);
				    if (count($param) == 1)
				    {
					    ModuleFactory::IncludePhp(trim($param[0]));
				    }
				    else
				    {
					    ModuleFactory::IncludePhp(trim($param[0]), trim($param[1]));
				    }
			    }
                 */
                if (attr.Name == "xmlobject")
                {
                    Match m = Regex.Match(attr.Value, pattern);
                    object[] paramValue = new object[] { null, null, null, null };

                    if (m.Success)
                    {
                        if (m.Groups["param"].Success)
                        {
                            string[] param = m.Groups["param"].Value.Split(',');
                            for (int i = 0; i < param.Length; i++)
                            {
                                if (param[i] == "#CONTEXT#")
                                {
                                    paramValue[i] = this._context;
                                }
                                else
                                {
                                    paramValue[i] = param[i].Trim();
                                }
                            }
                            object plugin = PluginFactory.LoadPlugin(m.Groups["plugin"].Value, paramValue[0], paramValue[1], paramValue[2], paramValue[3]);
                            if (!(plugin is IXmlnukeDocumentObject))
                            {
                                throw new Exception("The attribute in XMLNuke need to implement IXmlnukeDocumentObject interface");
                            }
                            ((IXmlnukeDocumentObject)plugin).generateObject(xmlRoot);
                        }
                    }
                }
            }

		    // Check if there is no XSL template
			if (this._outputResult != OutputResult.XHtml)
            {
                XmlDocument xmlResult = null;

				if (this._extractNodes == "")
				{
					xmlResult = xml;
				}
				else
				{
					XmlNodeList nodes = xml.DocumentElement.SelectNodes(this._extractNodes);
					XmlDocument retDocument = util.XmlUtil.CreateXmlDocumentFromStr("<" + this._extractNodesRoot + "/>");
					XmlNode nodeRoot = retDocument.DocumentElement;
					util.XmlUtil.AddAttribute(nodeRoot, "xpath", this._extractNodes);
					util.XmlUtil.AddAttribute(nodeRoot, "site", this._context.Site);
					foreach (XmlNode node in nodes)
					{
						XmlNode nodeToAdd = util.XmlUtil.CreateChild(nodeRoot, node.Name, "");
						foreach (XmlNode attr in node.Attributes)
						{
							util.XmlUtil.AddAttribute(nodeToAdd, attr.Name, attr.Value);
						}
						util.XmlUtil.AddNodeFromNode(nodeToAdd, node);
					}
					xmlResult = retDocument;
				}

                if (this._outputResult == OutputResult.Json)
                {
                    // Convert to JSon.
                    return XmlUtil.XmlToJSON(xmlResult);
                }
                else
                {
                    return xmlResult.OuterXml;
                }
			}

			_context.Xsl = xslFile.ToString();

			// Set up a transform object with the XSLT file
			XslTransform xslTran = new XslTransform();
			processor.SnippetProcessor snippetProcessor = new processor.SnippetProcessor(this._context, xslFile);
			Uri uri = processor.SnippetProcessor.getUriFromXsl(xslFile, this._context);
			object input = snippetProcessor.GetEntity(uri, null, typeof(System.IO.Stream));
			//((System.IO.Stream)input).Seek(0, System.IO.SeekOrigin.Begin);
			XmlTextReader xtr = new XmlTextReader((System.IO.Stream)input);
			try
			{
				xslTran.Load(xtr, new XmlUrlResolver(), this.GetType().Assembly.Evidence);
				//xslTran.Load(xtr);
				//xslTran.Load (xslFile.FullQualifiedNameAndPath(), snippetProcessor); 
			}
			finally
			{
				xtr.Close();
				input = null;
			}

			// Create Argument List
			XsltArgumentList xslArg = new XsltArgumentList();
			xslArg.AddParam("xml", "", this._context.Xml);
			xslArg.AddParam("xsl", "", this._context.Xsl);
			xslArg.AddParam("site", "", this._context.Site);
			xslArg.AddParam("lang", "", this._context.Language.Name.ToLower());
			xslArg.AddParam("transformdate", "", System.DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss"));
			xslArg.AddParam("urlbase", "", this._context.UrlBase());
			xslArg.AddParam("engine", "", "CSHARP");

			//Transform and output
			System.IO.MemoryStream result = new System.IO.MemoryStream();
			XmlTextWriter xtw = new XmlTextWriter(result, System.Text.UTF8Encoding.UTF8);
			xtw.Formatting = Formatting.Indented;
			xtw.Indentation = 1;
			xtw.IndentChar = ' ';
			xslTran.Transform(xml, xslArg, xtw, null); // 1.0 Compatibility: xslTran.Transform(xml, null, xtw); 

			// Reload XHTML result to process PARAM and HREFs
			XmlDocument xhtml = new XmlDocument();
			result.Seek(0, System.IO.SeekOrigin.Begin);
			xhtml.Load(result);
			processor.ParamProcessor paramProcessor = new processor.ParamProcessor(this._context);
			paramProcessor.AdjustToFullLink(xhtml, "A", "HREF");
			paramProcessor.AdjustToFullLink(xhtml, "FORM", "ACTION");
			paramProcessor.AdjustToFullLink(xhtml, "AREA", "HREF");
			paramProcessor.AdjustToFullLink(xhtml, "LINK", "HREF");
			if (this._context.ContextValue("xmlnuke.ENABLEPARAMPROCESSOR") == "true")
			{
				paramProcessor.ProcessParameters(xhtml);
			}

			return xhtml.OuterXml;
		}

		/// <summary>
		/// Get a XMLDocument from a XMLFile
		/// </summary>
		/// <param name="xmlFile">XMLFile</param>
		/// <returns>XMLDocument</returns>
		public XmlDocument getXmlDocument(processor.XMLFilenameProcessor xmlFile)
		{
			_context.Xml = xmlFile.ToString();

			// Load XMLDocument and add ALL and INDEX nodes
			XmlDocument xmlDoc;
			try
			{
				if (!(xmlFile.FilenameLocation == processor.ForceFilenameLocation.PathFromRoot)) // Get From Repository...
				{
					xmlDoc = _context.getXMLDataBase().getDocument(xmlFile.FullQualifiedName());
				}
				else
				{
					xmlDoc = util.XmlUtil.CreateXmlDocument(xmlFile.FullQualifiedNameAndPath());
				}
			}
			catch (System.IO.FileNotFoundException ex)
			{
				processor.XMLFilenameProcessor xmlFileNotFound = new processor.XMLFilenameProcessor("notfound", this._context);
				if (_context.getXMLDataBase().existsDocument(xmlFileNotFound.FullQualifiedName()))
				{
					xmlDoc = _context.getXMLDataBase().getDocument(xmlFileNotFound.FullQualifiedName());
				}
				else
				{
					throw ex;
				}
			}

			XmlNode xmlRootNode = xmlDoc.SelectSingleNode("page");
			if (xmlRootNode != null) //Index.<lang>.xml doensnt have node PAGE
			{
				this.addXMLDefault(xmlRootNode);
			}

			return xmlDoc;
		}


		public string TransformDocumentRemote(string url)
		{
			string cachename = "REMOTE-" + UsersBase.EncodeSHA(url);
			processor.XMLCacheFilenameProcessor cacheFile = new processor.XMLCacheFilenameProcessor(cachename, this._context);

			string file = cacheFile.FullQualifiedNameAndPath();
			if (util.FileUtil.Exists(file))
			{
				DateTime horaMod = System.IO.File.GetLastWriteTime(file);
				TimeSpan tempo = System.DateTime.Now - horaMod;
				if (tempo.Minutes > 30)
				{
					util.FileUtil.DeleteFile(cacheFile);
				}
			}

			if ((util.FileUtil.Exists(file)) && (!this._context.Reset))
			{
				return util.FileUtil.QuickFileRead(file);
			}
			else
			{
				XmlDocument xmlDoc = util.FileUtil.GetRemoteXMLDocument(url, this._context);

				string result = this.TransformDocument(xmlDoc);

				result = System.Text.RegularExpressions.Regex.Replace(result, "&(amp|#38);gt;", ">");
				result = System.Text.RegularExpressions.Regex.Replace(result, "&(amp|#38);lt;", "<");

				util.FileUtil.QuickFileWrite(file, result);

				return result;
			}
		}

		public static string ProcessModule(com.xmlnuke.engine.Context context, com.xmlnuke.engine.XmlnukeEngine engine)
		{
			return XmlnukeEngine.ProcessModule(context, engine, context.Module);
		}

		public static string ProcessModule(com.xmlnuke.engine.Context context, com.xmlnuke.engine.XmlnukeEngine engine, string moduleName)
		{
			com.xmlnuke.module.IModule module = null;
			try
			{
				module = com.xmlnuke.module.ModuleFactory.GetModule(moduleName, context, null);
			}
			catch (com.xmlnuke.exceptions.NotFoundException ex)
			{
				com.xmlnuke.module.LoadErrorStructure le;
				le.error = com.xmlnuke.module.ErrorType.NotFound;
				le.moduleName = moduleName;
				le.errorMessage = ex.Message;
				le.stackTrace = "";
				module = com.xmlnuke.module.ModuleFactory.GetModule("LoadError", context, le);
			}
			catch (com.xmlnuke.exceptions.NotAuthenticatedException)
			{
				string s = context.ContextValue("SELFURL");
				s = com.xmlnuke.classes.XmlnukeManageUrl.encodeParam(s.Substring(0, s.Length - 1)); // Encode
				context.redirectUrl(context.bindModuleUrl(context.ContextValue("xmlnuke.LOGINMODULE")) + "&ReturnUrl=" + s);
			}
			catch (com.xmlnuke.exceptions.InsufficientPrivilegeException ex)
			{
				com.xmlnuke.module.LoadErrorStructure le;
				le.error = com.xmlnuke.module.ErrorType.ErrorMessage;
				le.moduleName = moduleName;
				le.errorMessage = ex.Message;
				le.stackTrace = "";
				module = com.xmlnuke.module.ModuleFactory.GetModule("LoadError", context, le);
			}

			if (module != null)
			{
				try
				{
					return engine.TransformDocument(module);
				}
				catch (System.Threading.ThreadAbortException)
				{
					// Nothing to do. 
				}
				catch (Exception ex)
				{
					com.xmlnuke.module.LoadErrorStructure le;
					le.error = com.xmlnuke.module.ErrorType.ErrorMessage;
					le.moduleName = moduleName;
					le.errorMessage = ex.Message;
					Exception inner = ex.InnerException;
					while (inner != null)
					{
						le.errorMessage += "; " + inner.Message;
						inner = inner.InnerException;
					}
					le.stackTrace = ex.StackTrace;
					module = com.xmlnuke.module.ModuleFactory.GetModule("LoadError", context, le);
					return engine.TransformDocument(module);
				}
			}

			// Believe me. You NEVER will run this piece of code. But I need to compile, so, you understand.
			return "Something is wrong and I cant understand it. None module was loaded. Can you explain that?";

		}

	}
}