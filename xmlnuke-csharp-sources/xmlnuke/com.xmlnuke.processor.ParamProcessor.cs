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
using com.xmlnuke.util;
using com.xmlnuke;

namespace com.xmlnuke.processor
{
	/// <summary>
	/// ParamProcessor can process the XSL transform result (or xhtml cache) and replace the [PARAM:...] and Adjust Links to Full XMLNuke link (when is possible). 
	/// <P><b>Only uses this class after XML/XSL Transform and with XHTML files</b></P>
	/// </summary>
	public class ParamProcessor
	{
		private engine.Context _context;

		/// <summary>
		/// ParamProcessor constructor.
		/// </summary>
		/// <param name="context">The com.xmlnuke.engine.Context class</param>
		public ParamProcessor(engine.Context context)
		{
			this._context = context;
		}

		/// <summary>
		/// Process XHTML files and look for HREF attributes and change "engine:xmlnuke" and "module:..." contents to FULL QUALIFIED VIRTUAL PATH and XMLNUke's context params
		/// </summary>
		/// <param name="xmlDom">XmlDocument to be parsed</param>
		/// <param name="tagName">Tag to be looked for</param>
		/// <param name="attribute">Attribute within tag to be looked for</param>
		public void AdjustToFullLink(XmlDocument xmlDom, string tagName, string attribute)
		{
			XmlNodeList nodeList = xmlDom.SelectNodes("//" + tagName.ToLower() + " | //" + tagName.ToUpper());
			XmlNode nodeAtr;
			foreach (XmlNode node in nodeList)
			{
				nodeAtr = node.SelectSingleNode("@" + attribute.ToUpper());
				if (nodeAtr == null)
				{
					nodeAtr = node.SelectSingleNode("@" + attribute.ToLower());
				}
				if (nodeAtr != null)
				{
					nodeAtr.Value = this.GetFullLink(nodeAtr.Value);
				}
			}
		}

		/// <summary>
		/// Extract from a Query String like (key1=value1&amp;key2=value2&amp;...) the value from a key supplied.
		/// </summary>
		/// <param name="strQueryString">Query string</param>
		/// <param name="strKeyPair">Key to be looked for</param>
		/// <returns>Return the value if found or an empty string if not found</returns>
		private string ExtractPairQueryString(string strQueryString, string strKeyPair)
		{
			int iPos = strQueryString.IndexOf("&" + strKeyPair + "=");
			if (iPos < 0)
			{
				iPos = strQueryString.IndexOf("?" + strKeyPair + "=");
				if (iPos < 0)
				{
					return "";
				}
			}
			iPos++;

			strQueryString = strQueryString.Substring(iPos + strKeyPair.Length + 1);
			iPos = strQueryString.IndexOf("&");
			if (iPos >= 0)
			{
				strQueryString = strQueryString.Substring(0, iPos);
			}
			else
			{
				iPos = strQueryString.IndexOf("\"");
				if (iPos >= 0)
				{
					strQueryString = strQueryString.Substring(0, iPos);
				}
			}
			return strQueryString;
		}

		/// <summary>
		/// Replace a HREF value with XMLNuke context values.
		/// </summary>
		/// <param name="strHref">HREF value</param>
		/// <returns>Return the new string if exists engine:xmlnuke or module:... Otherwise returns the original value</returns>
		public string GetFullLink(string strHref)
		{
			string sResult = strHref;
			bool admin = false;
			int iPosScript = strHref.IndexOf("engine:xmlnuke");
			if (iPosScript >= 0)
			{
				sResult = sResult.Substring(0, iPosScript) + this._context.UrlXmlNukeEngine + sResult.Substring(("engine:xmlnuke").Length + iPosScript);
			}
			else
			{
				iPosScript = strHref.IndexOf("module:");
				if (iPosScript >= 0)
				{
					sResult = sResult.Substring(0, iPosScript) + this._context.UrlModule + "?module=" + sResult.Substring(("module:").Length + iPosScript).Replace("?", "&");
				}
				else
				{
					iPosScript = strHref.IndexOf("admin:");
					if (iPosScript >= 0)
					{
						admin = true;
						string namespacedef = "com.xmlnuke.admin.";
						if (strHref.IndexOf(":engine") >= 0)
						{
							sResult = _context.UrlXmlNukeAdmin + sResult.Substring(("admin:engine").Length + iPosScript);
						}
						else
						{
							if (strHref.IndexOf(".") >= 0)
							{
								namespacedef = "";
							}
							sResult = sResult.Substring(0, iPosScript) + this._context.UrlModule + "?module=" + namespacedef + sResult.Substring(("admin:").Length + iPosScript).Replace("?", "&");
						}
					}
					else
					{
						return strHref;
					}
				}
			}

			int iPosQuestion = sResult.LastIndexOf("?");
			string XML = this.ExtractPairQueryString(sResult, "xml");
			string XSL = this.ExtractPairQueryString(sResult, "xsl");
			string SITE = this.ExtractPairQueryString(sResult, "site");
			string LANG = this.ExtractPairQueryString(sResult, "lang");

			bool fullLink = (this._context.ContextValue("xmlnuke.USEFULLPARAMETER") == "true");
			if (iPosQuestion >= 0)
			{
				if (((SITE == "") && fullLink) || (!fullLink && (SITE == "") && (this._context.Site != this._context.ContextValue("xmlnuke.DEFAULTSITE"))))
				{
					sResult = sResult + "&site=" + this._context.Site;
				}
				if (((XSL == "") && !admin && fullLink) || (!fullLink && (XSL == "") && (this._context.Xsl != this._context.ContextValue("xmlnuke.DEFAULTPAGE"))))
				{
					sResult = sResult + "&xsl=" + (this._context.Xsl == "index" ? this._context.ContextValue("xmlnuke.DEFAULTPAGE") : this._context.Xsl);
				}
				if (XML == "" && fullLink)
				{
					sResult = sResult + "&xml=" + this._context.Xml;
				}
				//if ( (LANG == "" && fullLink)  || (!fullLink && (LANG=="") && (strpos("!".this._context.ContextValue("HTTP_ACCEPT_LANGUAGE"), "!".this._context.Language().getName()) === false) ) )
				if ((LANG == "" && fullLink) || (!fullLink && (LANG == "") && (this._context.ContextValue("HTTP_ACCEPT_LANGUAGE").ToLower().IndexOf(this._context.Language.Name.ToLower()) != 0)))
				{
					sResult = sResult + "&lang=" + this._context.Language.Name.ToLower();
				}
			}

			return this._context.VirtualPathAbsolute(sResult);
		}

		/// <summary>
		/// Process XHTML file and replace the tags [param:...] to XMLNuke context values
		/// </summary>
		/// <param name="sRead">XHTML File</param>
		/// <returns>Return the XHTML File modified</returns>
		public void ProcessParameters(XmlDocument xmlDom)
		{
			XmlNode nodeRoot = xmlDom.DocumentElement;
			XmlNode nodeWorking;
			if (nodeRoot.HasChildNodes)
			{
				nodeWorking = nodeRoot.FirstChild;
				while (nodeWorking != null)
				{
					ProcessChildren(nodeWorking, 0);
					nodeWorking = nodeWorking.NextSibling;
				}
			}
		}

		private void ProcessChildren(XmlNode node, int depth)
		{
			// "TEXTAREA" and "PRE" nodes doesnt process PARAM names!
			if ((node.ParentNode.Name == "textarea") || (node.ParentNode.Name == "pre"))
			{
				return;
			}

			XmlAttributeCollection attribs;
			string result;

			if ((node.NodeType == XmlNodeType.Element) || (node.NodeType == XmlNodeType.Text))
			{
                if (!String.IsNullOrEmpty(node.Value) && node.Value.IndexOf("<") >= 0)
                {
                    XmlNode nodeToProc = util.XmlUtil.CreateXmlDocumentFromStr("<root>" + node.Value + "</root>").DocumentElement;
                    node.Value = "";
                    util.XmlUtil.AddNodeFromNode(node.ParentNode, nodeToProc);
                }
                
				result = this.CheckParameters(node.Value);

				if (result != "")
				{
					// If test below is True RESULT contains HTML TAGS. These tags need be processed
					// Otherwise, go ahead!
					if (result.IndexOf("<") >= 0)
					{
                        
                        XmlNode nodeToProc = util.XmlUtil.CreateXmlDocumentFromStr("<root>" + result + "</root>").DocumentElement;
						if (node.NodeType == XmlNodeType.Text)
						{
							node.Value = "";
							util.XmlUtil.AddNodeFromNode(node.ParentNode, nodeToProc);
						}
						else
						{
							util.XmlUtil.AddNodeFromNode(node, nodeToProc);
						}
					}
					else
					{
						node.Value = result;
					}

				}
				attribs = node.Attributes;
				if (attribs != null)
				{
					for (int i = 0; i < attribs.Count; i++)
					{
						result = this.CheckParameters(attribs[i].Value);
						if (result != "")
						{
							attribs[i].Value = result;
						}
					}
				}

				XmlNode nodeworking;
				if (node.HasChildNodes)
				{
					nodeworking = node.FirstChild;
					while (nodeworking != null)
					{
						this.ProcessChildren(nodeworking, depth + 1);
						nodeworking = nodeworking.NextSibling;
					}
				}
			}
		}

		private string CheckParameters(string param)
		{
			if (param == null)
			{
				return "";
			}
			int iStart = param.IndexOf("[param:");
			if (iStart >= 0)
			{
				int iEnd;
				while (iStart >= 0)
				{
					iEnd = param.IndexOf("]", iStart + 1);
					string paramDesc = param.Substring(iStart + 7, iEnd - iStart - 7);
					param = param.Substring(0, iStart) + this._context.ContextValue(paramDesc) + param.Substring(iEnd + 1);
					iStart = param.IndexOf("[param:");
				}
				return param;
			}
			else
			{
				return "";
			}
		}

	}
}