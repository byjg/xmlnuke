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

namespace com.xmlnuke.anydataset
{
	/// <summary>
	/// SingleRow class represent an unique anydataset row.
	/// </summary>
	public class SingleRow
	{
		/// <summary>XmlNode represents a SingleRow</summary>
		private XmlNode _node;

        private Dictionary<string, List<string>> _row;
        private Dictionary<string, List<string>> _originalRow;


		/// <summary>
		/// SingleRow constructor
		/// </summary>
		/// <param name="node">XmlNode represents a SingleRow</param>
		public SingleRow()
		{
            _row = new Dictionary<string, List<string>>();
		}

        public SingleRow(Dictionary<string, List<string>> row)
        {
            _row = row;
        }

        /// <summary>
		/// Add a string field to row
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="value">String Value</param>
		public void AddField(string name, string value)
		{
            if (!_row.ContainsKey(name))
            {
                _row.Add(name, new List<string>());
            }
            _row[name].Add(value);
		}

		/// <summary>
		/// Add a DateTime field to row
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="value">DateTime Value</param>
		public void AddField(string name, DateTime value)
		{
            this.AddField(name, value.ToString("yyyy/MM/dd HH:mm:ss"));
		}

		/// <summary>
		/// Get the string value from a field name
		/// </summary>
		/// <param name="name">Field name</param>
		public string getField(string name)
		{
            if (!_row.ContainsKey(name))
            {
                return "";
            }
            else
            {
                if (_row[name].Count == 0)
                {
                    return "";
                }
                else
                {
                    return _row[name][0];
                }
            }
		}

		public string[] getFieldArray(string name)
		{
            if (!_row.ContainsKey(name))
            {
                return new string[]{};
            }
            else
            {
                return _row[name].ToArray();
            }
		}

		/// <summary>
		/// Return all Field Names from current SingleRow
		/// </summary>
		/// <returns></returns>
		public string[] getFieldNames()
		{
            string[] result = new string[_row.Keys.Count];
            int i=0;
            foreach(string s in _row.Keys)
            {
                result[i++] = s;
            }
			return result;
		}



		/// <summary>
		/// Update a specific field and specific value with new value 
		/// </summary>
		/// <param name="name">string</param>
		/// <param name="oldvalue">string</param>
		/// <param name="newvalue">string</param>
		public void setFieldValue(string name, string oldvalue, string newvalue)
		{
            if (!_row.ContainsKey(name))
            {
                this.AddField(name, newvalue);
            }
            else
            {
                int olditem = _row[name].IndexOf(oldvalue);
                if (olditem == -1)
                {
                    this.AddField(name, newvalue);
                }
                else
                {
                    _row[name][olditem] = newvalue;
                }
            }
		}

		/// <summary>
		/// Set a string value to existing field name
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="value">Field value</param>
		public void setField(string name, string value)
		{
            if (!_row.ContainsKey(name))
            {
                this.AddField(name, value);
            }
            else
            {
                List<string> list = new List<string>();
                list.Add(value);
                _row[name] = list;
            }
		}

        /// <summary>
        /// Remove specified field name with specified value name from row.
        /// </summary>
        /// <param name="name">string</param>
        /// <param name="value">string</param>
        public void removeFieldNameValue(string name, string value)
        {
            if (_row.ContainsKey(name))
            {
                _row[name].Remove(value);
                if (_row[name].Count == 0)
                {
                    this.removeField(name);
                }
            }
        }
        
        /// <summary>
		/// Remove specified field name from row.
		/// </summary>
		/// <param name="name">Field name</param>
		public void removeField(string name)
		{
            _row.Remove(name);
		}

		/// <summary>
		/// Get the XmlNode row objet
		/// </summary>
		/// <returns>XmlNode</returns>
		public XmlNode getDomObject()
		{
			XmlDocument row = util.XmlUtil.CreateXmlDocumentFromStr("<row />");
			XmlNode root = row.DocumentElement;
			foreach(string key in this._row.Keys)
			{
				foreach(string value in this._row[key])
				{
					XmlNode field = util.XmlUtil.CreateChild(root, "field", value);
					util.XmlUtil.AddAttribute(field, "name", key);
				}
			}

            return root;
		}

        public Dictionary<string, List<string>> getRawFormat()
        {
            return this._row;
        }

	    public bool hasChanges()
	    {
		    return (!this._row.Equals(this._originalRow));
	    }

	    public void acceptChanges()
	    {
		    this._originalRow = this._row;
	    }

	    public void rejectChanges()
	    {
		    this._row = this._originalRow;
	    }

    }
}