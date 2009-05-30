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

namespace com.xmlnuke.anydataset
{

	/// <summary>
	/// Enum to abstract relational operators. Used on AddRelation method.
	/// </summary>
	public enum Relation
	{
		/// <summary>= operator</summary>
		Equal = 0,
		/// <summary>&lt; operator</summary>
		LessThan = 1,
		/// <summary>&gt; operator</summary>
		GreaterThan = 2,
		/// <summary>&lt;= operator</summary>
		LessOrEqualThan = 3,
		/// <summary>&gt;> operator</summary>
		GreaterOrEqualThan = 4,
		/// <summary>!= operator</summary>
		NotEqual = 5,
		/// <summary>Starts With operator</summary>
		StartsWith = 6,
		/// <summary>Like operator</summary>
		Contains = 7
	}

	/// <summary>
	/// IteratorFilter class abstract XPATH commands to filter an AnyDataSet XML. Used on getIterator method.
	/// </summary>
	public class IteratorFilter
	{
		/// <summary>Representative XPATH string</summary>
		private string _xpathFilter;

		/// <summary>Representative WHERE clause</summary>
		private string _sqlFilter;
		private System.Collections.Specialized.NameValueCollection _sqlParam;

		/// <summary>
		/// IteratorFilter Constructor
		/// </summary>
		public IteratorFilter()
		{
			this._xpathFilter = "";
			this._sqlFilter = "";
			this._sqlParam = new System.Collections.Specialized.NameValueCollection();
		}

		/// <summary>
		/// Get the XPATH string
		/// </summary>
		/// <returns>XPath String</returns>
		public string getXPath()
		{
			if (this._xpathFilter == "")
			{
				return "anydataset/row";
			}
			else
			{
				return "anydataset/row[" + this._xpathFilter + "]";
			}
		}

		/// <summary>
		/// Get the SQL string
		/// </summary>
		/// <returns>SQL String</returns>
		public string getSql(string tableName, out DbParameters param)
		{
			string sql = "select * from " + tableName;
			string filtro = this.getFilter(out param);
			if (filtro != "")
			{
				sql += " where " + filtro;
			}

			return sql;
		}

		public string getFilter()
		{
			DbParameters param;
			string filtro = this.getFilter(out param);
			return XmlnukeProviderFactory.ParseSQLWithoutParam(filtro, param);
		}

		public string getFilter(out DbParameters param)
		{
			param = new DbParameters();

			foreach (string key in this._sqlParam.Keys)
			{
				DbParameter paramItem = new DbParameter();
				paramItem.Name = key;
				paramItem.Value = this._sqlParam[key];
				param.Add(paramItem);
			}

			return this._sqlFilter;
		}

		/// <summary>
		/// Private method to get a Xpath string to a single string comparison
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="relation">Relation enum</param>
		/// <param name="value">Field string value</param>
		/// <returns>Xpath String</returns>
		private string getStrXpathRelation(string name, Relation relation, string value)
		{
			double OutValue;
			bool is_numeric = false;

			if (String.IsNullOrEmpty(value))
			{
				is_numeric = double.TryParse(value.ToString().Trim(), System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.CurrentCulture, out OutValue);
			}

			char str = is_numeric ? ' ' : '\'';
			string field = "field[@name='" + name + "'] ";
			value = " " + str + value + str + " ";

			string result = "";
			switch (relation)
			{
				case Relation.Equal:
					{
						result = field + "=" + value;
						break;
					}
				case Relation.GreaterThan:
					{
						result = field + ">" + value;
						break;
					}
				case Relation.LessThan:
					{
						result = field + "<" + value;
						break;
					}
				case Relation.GreaterOrEqualThan:
					{
						result = field + ">=" + value;
						break;
					}
				case Relation.LessOrEqualThan:
					{
						result = field + "<=" + value;
						break;
					}
				case Relation.NotEqual:
					{
						result = field + "!=" + value;
						break;
					}
				case Relation.StartsWith:
					{
						result = " starts-with(" + field + ", " + value + ") ";
						break;
					}
				case Relation.Contains:
					{
						result = " contains(" + field + ", " + value + ") ";
						break;
					}
			}
			return result;
		}

		/// <summary>
		/// Private method to get a Xpath string to a single integer comparison
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="relation">Relation enum</param>
		/// <param name="value">Field integer value</param>
		/// <returns>Xpath String</returns>
		private string getStrXpathRelation(string name, Relation relation, int value)
		{
			return this.getStrXpathRelation(name, relation, value.ToString());
		}

		/// <summary>
		/// Private method to get a Xpath string to a single string comparison
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="relation">Relation enum</param>
		/// <param name="value">Field string value</param>
		/// <returns>Xpath String</returns>
		private string getStrSqlRelation(string name, Relation relation, string value)
		{
			value = value.Trim();
			string paramName = name;
			int i = 0;
			while (this._sqlParam[paramName] != null)
			{
				paramName = name + (i++).ToString();
			}

			this._sqlParam[paramName] = value;

			string result = "";
			string field = " " + name + " ";
			string valueparam = " [[" + paramName + "]] ";
			switch (relation)
			{
				case Relation.Equal:
					{
						result = field + "=" + valueparam;
						break;
					}
				case Relation.GreaterThan:
					{
						result = field + ">" + valueparam;
						break;
					}
				case Relation.LessThan:
					{
						result = field + "<" + valueparam;
						break;
					}
				case Relation.GreaterOrEqualThan:
					{
						result = field + ">=" + valueparam;
						break;
					}
				case Relation.LessOrEqualThan:
					{
						result = field + "<=" + valueparam;
						break;
					}
				case Relation.NotEqual:
					{
						result = field + "!=" + valueparam;
						break;
					}
				case Relation.StartsWith:
					{
						this._sqlParam[paramName] = value + "%";
						result = field + " like " + valueparam;
						break;
					}
				case Relation.Contains:
					{
						this._sqlParam[paramName] = "%" + value + "%";
						result = field + " like " + valueparam;
						break;
					}
			}

			return result;

		}

		/// <summary>
		/// Private method to get a Xpath string to a single integer comparison
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="relation">Relation enum</param>
		/// <param name="value">Field integer value</param>
		/// <returns>Xpath String</returns>
		private string getStrSqlRelation(string name, Relation relation, int value)
		{
			return this.getStrSqlRelation(name, relation, value.ToString());
		}

		protected void addRelationInternal(string name, Relation relation, string value, bool useAnd)
		{
			if ((_xpathFilter != "") && (_xpathFilter.Substring(_xpathFilter.Length - 2, 2) != "( "))
			{
				_xpathFilter += ((useAnd) ? " and " : " or ");
				_sqlFilter += ((useAnd) ? " and " : " or ");
			}
			_xpathFilter += getStrXpathRelation(name, relation, value);
		}

		/// <summary>
		/// Add a single string comparison to filter.
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="relation">Relation enum</param>
		/// <param name="value">String value</param>
		public void addRelation(string name, Relation relation, string value)
		{
			this.addRelationInternal(name, relation, value, true);
			_sqlFilter += getStrSqlRelation(name, relation, value);
		}

		/// <summary>
		/// Add a single integer comparison to filter.
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="relation">Relation enum</param>
		/// <param name="value">Integer value</param>
		public void addRelation(string name, Relation relation, int value)
		{
			this.addRelationInternal(name, relation, value.ToString(), true);
			_sqlFilter += getStrSqlRelation(name, relation, value);
		}

		/// <summary>
		/// Add a single string comparison to filter. This comparison use the OR operator.
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="relation">Relation enum</param>
		/// <param name="value">String value</param>
		public void addRelationOr(string name, Relation relation, string value)
		{
			this.addRelationInternal(name, relation, value, false);
			_sqlFilter += getStrSqlRelation(name, relation, value);
		}

		/// <summary>
		/// Add a single integer comparison to filter. This comparison use the OR operator.
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="relation">Relation enum</param>
		/// <param name="value">Integer value</param>
		public void addRelationOr(string name, Relation relation, int value)
		{
			this.addRelationInternal(name, relation, value.ToString(), false);
			_sqlFilter += getStrSqlRelation(name, relation, value);
		}

		/// <summary>
		/// Add a "("
		/// </summary>
		public void startGroup()
		{
			if ((this._xpathFilter != "") && (_xpathFilter.Substring(_xpathFilter.Length - 2, 2) == ") "))
			{
				this._xpathFilter = this._xpathFilter + " or ";
				this._sqlFilter = this._sqlFilter + " or ";
			}
			else if (this._xpathFilter != "")
			{
				this._xpathFilter = this._xpathFilter + " and ";
				this._sqlFilter = this._sqlFilter + " and ";
			}
			this._xpathFilter = this._xpathFilter + " ( ";
			this._sqlFilter = this._sqlFilter + " ( ";
		}

		/// <summary>
		/// Add a ")"
		/// </summary>
		public void endGroup()
		{
			this._xpathFilter = this._xpathFilter + " ) ";
			this._sqlFilter = this._sqlFilter + " ) ";
		}
	}
}