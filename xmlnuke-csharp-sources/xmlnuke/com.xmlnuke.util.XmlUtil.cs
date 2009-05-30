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
	}
}