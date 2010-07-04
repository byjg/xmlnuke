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
using System.Collections.Generic;
using System.Text;
using System.Collections;

namespace com.xmlnuke.util
{
	/// <summary>
	/// Generic functions to manipulate XML nodes.
	/// Note: This classes didn't inherits from XmlDocument or XmlNode
	/// </summary>
	public class XmlUtil
	{
		/// <summary>
		/// This class doesn't use constructor because all methods are statics.
		/// </summary>
		public XmlUtil()
		{ }

		/// <summary>
		/// Create an empty XmlDocument object with some default parameters
		/// </summary>
		/// <returns>XmlDocument object</returns>
		public static XmlDocument CreateXmlDocument()
		{
			XmlDocument xmldoc = new XmlDocument();
			xmldoc.XmlResolver = null;
			//let's add the XML declaration section
			XmlNode xmlnode = xmldoc.CreateNode(XmlNodeType.XmlDeclaration, "", "");
			xmldoc.AppendChild(xmlnode);
			return xmldoc;
		}

		/// <summary>
		/// Create a XmlDocument object from a file saved on disk.
		/// </summary>
		/// <param name="filename">Filename</param>
		/// <returns>XmlDocument object</returns>
		public static XmlDocument CreateXmlDocument(string filename)
		{
			XmlDocument xmldoc = new XmlDocument();
			//let's add the XML declaration section
			xmldoc.Load(filename);
			return xmldoc;
		}

		public static XmlDocument CreateXmlDocumentFromStr(string xml)
		{
			XmlDocument xmldoc = new XmlDocument();
			xmldoc.XmlResolver = null;
			xmldoc.LoadXml(xml.Replace("&", "&amp;"));
			return xmldoc;
		}

		public static string GetFormattedDocument(XmlDocument xml)
		{
			byte[] buf;
			System.IO.MemoryStream ms = new System.IO.MemoryStream();
			try
			{
				XmlTextWriter wr = new XmlTextWriter(ms, null);
				wr.Formatting = Formatting.Indented;
				wr.Indentation = 3;
				wr.IndentChar = ' ';
				try
				{
					xml.Save(wr);
				}
				finally
				{
					wr.Close();
				}

				buf = ms.ToArray();
			}
			finally
			{
				ms.Close();
			}
			return System.Text.Encoding.UTF8.GetString(buf, 0, buf.Length);
		}

		/// <summary>
		/// Add node to specific XmlNode from file existing on disk
		/// </summary>
		/// <param name="rootNode">XmlNode receives node</param>
		/// <param name="filename">File to import node</param>
		/// <param name="nodetoadd">Node to be added</param>
		public static void AddNodeFromFile(XmlNode rootNode, processor.FilenameProcessor filename, string nodetoadd)
		{
			if (rootNode == null)
			{
				return;
			}
			if (!filename.getContext().getXMLDataBase().existsDocument(filename.FullQualifiedName()))
			{
				return;
			}

			try
			{
				XmlDocument source = filename.getContext().getXMLDataBase().getDocument(filename.FullQualifiedName());
				XmlNodeList nodes = source.SelectSingleNode(nodetoadd).ChildNodes;
				foreach (XmlNode node in nodes)
				{
					XmlNode newNode = rootNode.OwnerDocument.ImportNode(node, true);
					rootNode.AppendChild(newNode);
				}
			}
			catch (Exception ex)
			{
				throw ex;
			}
		}

		/// <summary>
		/// Attention: NODE MUST BE AN ELEMENT NODE!!!
		/// </summary>
		/// <param name="source"></param>
		/// <param name="nodeToAdd"></param>
		public static void AddNodeFromNode(XmlNode source, XmlNode nodeToAdd)
		{
			foreach (XmlNode node in nodeToAdd.ChildNodes)
			{
				XmlNode newNode = source.OwnerDocument.ImportNode(node, true);
				source.AppendChild(newNode);
			}
		}

		/// <summary>
		/// Append child node from specific node and add text
		/// </summary>
		/// <param name="rootNode">Parent node</param>
		/// <param name="nodeName">Node to add</param>
		/// <param name="nodeText">Text to add</param>
		/// <returns>New node</returns>
		public static XmlNode CreateChild(XmlNode rootNode, string nodeName, string nodeText)
		{
			XmlNode nodeworking = rootNode.OwnerDocument.CreateNode(XmlNodeType.Element, nodeName, "");
			XmlUtil.AddTextNode(nodeworking, nodeText);
			rootNode.AppendChild(nodeworking);
			return nodeworking;
		}
		public static XmlNode CreateChild(XmlNode rootNode, string nodeName)
		{
			return XmlUtil.CreateChild(rootNode, nodeName, "");
		}

		/// <summary>
		/// Create child node on the top from specific node and add text
		/// </summary>
		/// <param name="rootNode">Parent node</param>
		/// <param name="nodeName">Node to add</param>
		/// <param name="nodeText">Text to add</param>
		/// <returns></returns>
		public static XmlNode CreateChildBefore(XmlNode rootNode, string nodeName, string nodeText)
		{
			return XmlUtil.CreateChildBefore(nodeName, nodeText, rootNode.ChildNodes.Item(0));
		}

		public static XmlNode CreateChildBefore(XmlNode rootNode, string nodeName, string nodeText, int position)
		{
			return XmlUtil.CreateChildBefore(nodeName, nodeText, rootNode.ChildNodes.Item(position));
		}

		public static XmlNode CreateChildBefore(string nodeName, string nodeText, XmlNode node)
		{
			XmlNode nodeworking = node.OwnerDocument.CreateNode(XmlNodeType.Element, nodeName, "");
			XmlUtil.AddTextNode(nodeworking, nodeText);
			node.ParentNode.InsertBefore(nodeworking, node);
			return nodeworking;
		}

		/// <summary>
		/// Add text to node
		/// </summary>
		/// <param name="rootNode">Parent node</param>
		/// <param name="text">Text to add</param>
		public static void AddTextNode(XmlNode rootNode, string text)
		{
			XmlUtil.AddTextNode(rootNode, text, false);
		}

		/// <summary>
		/// Add text to node
		/// </summary>
		/// <param name="rootNode">Parent node</param>
		/// <param name="text">Text to add</param>
		/// <param name="escapeChars">If true create a CData Section, otherwise create a single text node.</param>
		public static void AddTextNode(XmlNode rootNode, string text, bool escapeChars)
		{
			if (text != "")
			{
				XmlNode nodeworkingText;
				if (escapeChars)
				{
					nodeworkingText = rootNode.OwnerDocument.CreateCDataSection(text);
				}
				else
				{
					nodeworkingText = rootNode.OwnerDocument.CreateTextNode(text);
				}
				rootNode.AppendChild(nodeworkingText);
			}
		}

		/// <summary>
		/// Add a attribute to specific node
		/// </summary>
		/// <param name="rootNode">Node to receive attribute</param>
		/// <param name="name">Attribute name</param>
		/// <param name="value">Attribute value</param>
		/// <returns>Node modified</returns>
		public static XmlNode AddAttribute(XmlNode rootNode, string name, string value)
		{
			XmlAttribute attrNode = rootNode.OwnerDocument.CreateAttribute(name);
			attrNode.Value = value;
			rootNode.Attributes.SetNamedItem(attrNode);
			return rootNode;
		}

		/// <summary>
		/// Add a attribute to specific node
		/// </summary>
		/// <param name="rootNode">Node to receive attribute</param>
		/// <param name="name">Attribute name</param>
		/// <param name="value">Attribute value</param>
		/// <returns>Node modified</returns>
		public static XmlNode AddAttribute(XmlNode rootNode, string name, int value)
		{
			return XmlUtil.AddAttribute(rootNode, name, value.ToString());
        }

        #region XmlToJSon - Original Code from http://www.phdcc.com/xml2json.htm

        /// <summary>
        /// XmlToJSon convert an XML to JSon. 
        /// Original Code from http://www.phdcc.com/xml2json.htm from Chris Cant.
        /// Modified By João Gilberto for XMLNuke Project
        /// </summary>
        /// <param name="xmlDoc"></param>
        /// <returns></returns>
        public static string XmlToJSON(XmlDocument xmlDoc)
        {
            return XmlUtil.XmlToJSON(xmlDoc.DocumentElement);
        }

        public static string XmlToJSON(XmlElement node)
        {
            StringBuilder sbJSON = new StringBuilder();
            sbJSON.Append("{ ");
            XmlToJSONnode(sbJSON, node, true);
            sbJSON.Append("}");
            return sbJSON.ToString();
        }

        //  XmlToJSONnode:  Output an XmlElement, possibly as part of a higher array
        private static void XmlToJSONnode(StringBuilder sbJSON, XmlElement node, bool showNodeName)
        {
            if (showNodeName)
                sbJSON.Append("\"" + SafeJSON(node.Name) + "\": ");

            sbJSON.Append("{");
            // Build a sorted list of key-value pairs
            //  where   key is case-sensitive nodeName
            //          value is an ArrayList of string or XmlElement
            //  so that we know whether the nodeName is an array or not.
            SortedList childNodeNames = new SortedList();

            //  Add in all node attributes
            if( node.Attributes!=null)
                foreach (XmlAttribute attr in node.Attributes)
                    StoreChildNode(childNodeNames,attr.Name,attr.InnerText);

            //  Add in all nodes
            foreach (XmlNode cnode in node.ChildNodes)
            {
                if (cnode is XmlText)
                    StoreChildNode(childNodeNames, "value", cnode.InnerText);
                else if (cnode is XmlElement)
                    StoreChildNode(childNodeNames, cnode.Name, cnode);
            }

            // Now output all stored info
            foreach (string childname in childNodeNames.Keys)
            {
                ArrayList alChild = (ArrayList)childNodeNames[childname];
                if (alChild.Count == 1)
                    OutputNode(childname, alChild[0], sbJSON, true);
                else
                {
                    sbJSON.Append(" \"" + SafeJSON(childname) + "\": [ ");
                    foreach (object Child in alChild)
                        OutputNode(childname, Child, sbJSON, false);
                    sbJSON.Remove(sbJSON.Length - 2, 2);
                    sbJSON.Append(" ], ");
                }
            }
            sbJSON.Remove(sbJSON.Length - 2, 2);
            sbJSON.Append(" }");
        }

        //  StoreChildNode: Store data associated with each nodeName
        //                  so that we know whether the nodeName is an array or not.
        private static void StoreChildNode(SortedList childNodeNames, string nodeName, object nodeValue)
        {
	        // Pre-process contraction of XmlElement-s
            if (nodeValue is XmlElement)
            {
                // Convert  <aa></aa> into "aa":null
                //          <aa>xx</aa> into "aa":"xx"
                XmlNode cnode = (XmlNode)nodeValue;
                if( cnode.Attributes.Count == 0)
                {
                    XmlNodeList children = cnode.ChildNodes;
                    if( children.Count==0)
                        nodeValue = null;
                    else if (children.Count == 1 && (children[0] is XmlText))
                        nodeValue = ((XmlText)(children[0])).InnerText;
                }
            }
            // Add nodeValue to ArrayList associated with each nodeName
            // If nodeName doesn't exist then add it
            object oValuesAL = childNodeNames[nodeName];
            ArrayList ValuesAL;
            if (oValuesAL == null)
            {
                ValuesAL = new ArrayList();
                childNodeNames[nodeName] = ValuesAL;
            }
            else
                ValuesAL = (ArrayList)oValuesAL;
            ValuesAL.Add(nodeValue);
        }

        private static void OutputNode(string childname, object alChild, StringBuilder sbJSON, bool showNodeName)
        {
            if (alChild == null)
            {
                if (showNodeName)
                    sbJSON.Append("\"" + SafeJSON(childname) + "\": ");
                sbJSON.Append("null");
            }
            else if (alChild is string)
            {
                if (showNodeName)
                    sbJSON.Append("\"" + SafeJSON(childname) + "\": ");
                string sChild = (string)alChild;
                sChild = sChild.Trim();
                Double temp;
                if (Double.TryParse(sChild, out temp))
                    sbJSON.Append(SafeJSON(sChild));
                else
                    sbJSON.Append("\"" + SafeJSON(sChild) + "\""); 
            }
            else
                XmlToJSONnode(sbJSON, (XmlElement)alChild, showNodeName);
            sbJSON.Append(", ");
        }

        // Make a string safe for JSON
        private static string SafeJSON(string sIn)
        {
            StringBuilder sbOut = new StringBuilder(sIn.Length);
            foreach (char ch in sIn)
            {
                if (Char.IsControl(ch) || ch == '\'')
                {
                    int ich = (int)ch;
                    sbOut.Append(@"\u" + ich.ToString("x4"));
                    continue;
                }
                else if (ch == '\"' || ch == '\\' || ch == '/')
                {
                    sbOut.Append('\\');
                }
                sbOut.Append(ch);
            }
            return sbOut.ToString();
        }
        #endregion

	}
}