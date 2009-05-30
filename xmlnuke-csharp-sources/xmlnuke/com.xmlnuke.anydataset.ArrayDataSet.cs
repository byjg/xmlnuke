/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  CSharp Implementation: Joao Gilberto Magalhaes
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
using System.Reflection;
using com.xmlnuke.engine;
using com.xmlnuke.processor;
using com.xmlnuke.util;

namespace com.xmlnuke.anydataset
{
	public class ArrayDataSet
	{

		protected NameValueCollection _array;
		protected string _fieldName;

		public ArrayDataSet(string[] array)
			: this(array, "value")
		{ }
		public ArrayDataSet(string[] array, string fieldName)
		{
			this._fieldName = fieldName;
			this._array = new NameValueCollection();

			for (int i = 0; i < array.Length; i++)
			{
				this._array[i.ToString()] = this._fieldName + '\xFF' + array[i];
			}
		}

		public ArrayDataSet(ArrayList array)
			: this(array, "value")
		{ }
		public ArrayDataSet(ArrayList array, string fieldName)
		{
			this._fieldName = fieldName;
			this._array = new NameValueCollection();

			for (int i = 0; i < array.Count; i++)
			{
				if (array[i] is string)
				{
					this._array[i.ToString()] = this._fieldName + '\xFF' + array[i].ToString();
				}
				else
				{
					this._array[i.ToString()] = this.ListProperties(array[i]);
				}
			}
		}

		public ArrayDataSet(NameValueCollection array)
			: this(array, "value")
		{ }
		public ArrayDataSet(NameValueCollection array, string fieldName)
		{
			this._fieldName = fieldName;
			this._array = new NameValueCollection();
			for (int i = 0; i < array.Keys.Count; i++)
			{
				this._array[array.Keys[i]] = this._fieldName + '\xFF' + array[array.Keys[i]];
			}
		}

		/**
		*@access public
		*@param string sql
		*@param array array
		*@return DBIterator
		*/
		public IIterator getIterator()
		{
			return new ArrayIterator(this._array);
		}

		private string ListProperties(object objectToInspect)
		{
			string returnString = "";
			Type objectType = objectToInspect.GetType();
			PropertyInfo[] properties = objectType.GetProperties();

			foreach (PropertyInfo property in properties)
			{
				object o = property.GetValue(objectToInspect, null);
				if (o is string)
				{
					if (returnString != "")
					{
						returnString += '\xFE';
					}
					returnString += property.Name + '\xFF' + o.ToString();
				}
			}

			return returnString;
		}
	}

}