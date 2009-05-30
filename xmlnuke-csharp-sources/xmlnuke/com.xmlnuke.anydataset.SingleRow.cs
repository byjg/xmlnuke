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

namespace com.xmlnuke.anydataset
{
	/// <summary>
	/// SingleRow class represent an unique anydataset row.
	/// </summary>
	public class SingleRow
	{
		/// <summary>XmlNode represents a SingleRow</summary>
		private XmlNode _node;

		/// <summary>
		/// SingleRow constructor
		/// </summary>
		/// <param name="node">XmlNode represents a SingleRow</param>
		public SingleRow(XmlNode node)
		{
			_node = node;
		}

		/// <summary>
		/// Add a string field to row
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="value">String Value</param>
		public void AddField(string name, string value)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(_node, "field", value);
			util.XmlUtil.AddAttribute(nodeWorking, "name", name);
		}

		/// <summary>
		/// Add a DateTime field to row
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="value">DateTime Value</param>
		public void AddField(string name, DateTime value)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(_node, "field", value.ToString("yyyy/MM/dd HH:mm:ss"));
			util.XmlUtil.AddAttribute(nodeWorking, "name", name);
		}

		/// <summary>
		/// Get the string value from a field name
		/// </summary>
		/// <param name="name">Field name</param>
		public string getField(string name)
		{
			XmlNode node = this._node.SelectSingleNode("field[@name='" + name + "']");
			if (node == null)
			{
				return "";
			}
			else
			{
				return node.InnerText;
			}
		}

		/// <summary>
		/// Get the NodeList from a single field. You need you when the field is repeated.
		/// </summary>
		/// <param name="name">Field name</param>
		public XmlNodeList getFieldNodes(string name)
		{
			return this._node.SelectNodes("field[@name='" + name + "']");
		}

		public string[] getFieldArray(string name)
		{
			XmlNodeList nodes = this.getFieldNodes(name);
			string[] array = new string[nodes.Count];
			for (int i = 0; i < nodes.Count; i++)
			{
				array[i] = nodes[i].InnerXml;
			}
			return array;
		}

		/// <summary>
		/// Return all Field Names from current SingleRow
		/// </summary>
		/// <returns></returns>
		public string[] getFieldNames()
		{
			XmlNodeList fields = this._node.SelectNodes("field");
			string[] array = new string[fields.Count];
			for (int i = 0; i < fields.Count; i++)
			{
				XmlNode fieldname = fields[i].SelectSingleNode("@name");
				if (fieldname == null)
				{
					array[i] = "_NULL_";
				}
				else
				{
					array[i] = fieldname.InnerXml;
				}
			}
			return array;
		}


		/// <summary>
		/// Remove specified field name with specified value name from row.
		/// </summary>
		/// <param name="name">string</param>
		/// <param name="value">string</param>
		public void removeFieldNameValue(string name, string value)
		{
			string[] array = this.getFieldArray(name);

			int numNode = -1;

			for (int i = 0; i < array.Length; i++)
			{
				if (array[i] == value)
				{
					numNode = i;
					break;
				}
			}

			if (numNode != -1)
			{
				XmlNodeList nodes = this.getFieldNodes(name);
				this.removeField(nodes[numNode]);
			}
		}

		/// <summary>
		/// Update a specific field and specific value with new value 
		/// </summary>
		/// <param name="name">string</param>
		/// <param name="oldvalue">string</param>
		/// <param name="newvalue">string</param>
		public void setFieldValue(string name, string oldvalue, string newvalue)
		{
			string[] array = this.getFieldArray(name);

			int numNode = -1;

			for (int i = 0; i < array.Length; i++)
			{
				if (array[i] == oldvalue)
				{
					numNode = i;
					break;
				}
			}

			if (numNode != -1)
			{
				XmlNodeList nodes = this.getFieldNodes(name);
				nodes[numNode].Value = newvalue;
			}
			else
			{
				this.AddField(name, newvalue);
			}
		}

		/// <summary>
		/// Set a string value to existing field name
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="value">Field value</param>
		public void setField(string name, string value)
		{
			XmlNode node = this._node.SelectSingleNode("field[@name='" + name + "']");
			if (node != null)
			{
				node.InnerText = value;
			}
			else
			{
				this.AddField(name, value);
			}
		}

		/// <summary>
		/// Remove specified field name from row.
		/// </summary>
		/// <param name="name">Field name</param>
		public void removeField(string name)
		{
			XmlNode node = this._node.SelectSingleNode("field[@name='" + name + "']");
			this.removeField(node);
		}

		/// <summary>
		/// Remove specified field node from row
		/// </summary>
		/// <param name="node">Field node</param>
		public void removeField(XmlNode node)
		{
			if (node != null)
			{
				this._node.RemoveChild(node);
			}
		}

		/// <summary>
		/// Get the XmlNode row objet
		/// </summary>
		/// <returns>XmlNode</returns>
		public XmlNode getDomObject()
		{
			return this._node;
		}

	}
}