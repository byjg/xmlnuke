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
using System.Collections.Generic;
using com.xmlnuke.util;

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

    class IteratorFilterStruct
    {
        public const string CMD_AND = " and ";
        public const string CMD_OR = " or ";
        public const string CMD_STGRP = " ( ";
        public const string CMD_ENGRP = " ) ";

        public string Command { get; set; }
        public string Name { get; set; }
        public Relation Relation { get; set; }
        public string Value { get; set; }
    }

	/// <summary>
	/// IteratorFilter class abstract XPATH commands to filter an AnyDataSet XML. Used on getIterator method.
	/// </summary>
	public class IteratorFilter
	{
	    /**
	     * @var array
	     */
	    private List<IteratorFilterStruct> _filters;

	    /**
	    *@desc IteratorFilter Constructor
	    */
	    public IteratorFilter()
	    {
		    this._filters = new List<IteratorFilterStruct>();
	    }

	    /**
	    *@param
	    *@return string - XPath String
	    *@desc Get the XPATH string
	    */
	    public string getXPath()
	    {
            DbParameters param;

		    string xpathFilter = this.generator(1, out param);
		    //Debug.PrintValue(xpathFilter);

		    if (xpathFilter == "")
		    {
			    return "/anydataset/row";
		    }
		    else
		    {
			    return "/anydataset/row[" + xpathFilter + "]";
		    }
	    }

	    public string getSql(string tableName, out DbParameters param)
        {
            return this.getSql(tableName, out param, "*");
        }

        public string getSql(string tableName, out DbParameters param, string returnFields)
	    {
		    param = new DbParameters();

		    string sql = "select " + returnFields + " from " + tableName;
		    string sqlFilter = this.generator(2, out param);
		    if (sqlFilter != "")
		    {
			    sql += " where " + sqlFilter + " ";
		    }
		    //Debug.PrintValue(sql, params);

		    return sql;
	    }

	    /**
	     *
	     * @param array
	     * @return unknown_type
	     */
	    public List<SingleRow> match(List<SingleRow> array)
	    {
		    List<SingleRow> returnArray = new List<SingleRow>();

		    foreach (SingleRow sr in array)
		    {
			    if (this.evalString(sr))
			    {
				    returnArray.Add(sr);
			    }
		    }

		    return returnArray;
	    }

	    /**
	     *
	     * @param type
	     * @param param
	     * @return unknown_type
	     */
	    private string generator(int type, out DbParameters param)
	    {
		    string filter = "";
		    param = new DbParameters();

		    IteratorFilterStruct previousValue = null;
		    foreach (IteratorFilterStruct value in this._filters)
		    {
			    if (value.Command == IteratorFilterStruct.CMD_STGRP)
			    {
				    if (previousValue != null)
				    {
					    filter += " or ( ";
				    }
				    else
				    {
					    filter += " ( ";
				    }
			    }
			    else if (value.Command == IteratorFilterStruct.CMD_ENGRP)
			    {
				    filter += ")";
			    }
			    else
			    {
				    if ( (previousValue != null) && (previousValue.Command != IteratorFilterStruct.CMD_STGRP) )
				    {
					    filter += value.Command;
				    }
				    if (type == 1)
				    {
					    filter += this.getStrXpathRelation(value.Name, value.Relation, value.Value);
				    }
				    else if (type == 2)
				    {
					    filter += this.getStrSqlRelation(value.Name, value.Relation, value.Value, ref param);
				    }
			    }
			    previousValue = value;
		    }

		    return filter;
	    }

	    /**
	    *@param string name - Field name
	    *@param Relation relation - Relation enum
	    *@param string value - Field string value
	    *@return string - Xpath String
	    *@desc Private method to get a Xpath string to a single string comparison
	    */
	    private string getStrXpathRelation(string name, Relation relation, string value)
	    {
		    string str = (Number.IsNumeric(value)?"":"'");
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

	    /**
	     *
	     * @param name
	     * @param relation
	     * @param value
	     * @param param
	     * @return unknown_type
	     */
	    private string getStrSqlRelation(string name, Relation relation, string value, ref DbParameters param)
	    {
		    //str = is_numeric(value)?"":"'";
		    value = value.Trim();
		    string paramName = name;
		    int i = 0;

			foreach (DbParameter p in param)
			{
                if (p.Name == name)
                {
				    paramName = name + (i++).ToString();
                }
			}

            DbParameter par = new DbParameter();

		    par.Name = paramName;
            par.Value = value;

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
				    par.Value = value + "%";
				    result = field + " like " + valueparam;
				    break;
			    }
			    case Relation.Contains:
			    {
				    par.Value = "%" + value + "%";
				    result = field + " like " + valueparam;
				    break;
			    }

		    }

			param.Add(par);

			return result;
	    }


	    /**
	     *
	     * @param array
	     * @return unknown_type
	     */
	    private bool evalString(SingleRow sr)
	    {
		    List<bool> result = new List<bool>();
		    bool finalResult = false;
		    int pos = 0;

		    result.Add(true); // Zero!

		    foreach (IteratorFilterStruct filter in this._filters)
		    {
			    if ( (filter.Command == IteratorFilterStruct.CMD_ENGRP) || (filter.Command == IteratorFilterStruct.CMD_OR))
			    {
				    finalResult |= result[pos++];
				    result.Add(true);
			    }

                if (filter.Command == IteratorFilterStruct.CMD_STGRP)
                {
                    finalResult |= result[pos++];
                    result.Add(true);
                    continue;
                }

                if (filter.Name == null)
                    continue;

                string[] field = sr.getFieldArray(filter.Name);

                foreach (string valueparam in field)
			    {
				    switch (filter.Relation)
				    {
					    case Relation.Equal:
					    {
						    result[pos] &= (valueparam == filter.Value);
						    break;
					    }
					    case Relation.GreaterThan:
					    {
						    result[pos] &= (valueparam.CompareTo(filter.Value) > 0);
						    break;
					    }
					    case Relation.LessThan:
					    {
						    result[pos] &= (valueparam.CompareTo(filter.Value) < 0);
						    break;
					    }
					    case Relation.GreaterOrEqualThan:
					    {
						    result[pos] &= (valueparam.CompareTo(filter.Value) >= 0);
						    break;
					    }
					    case Relation.LessOrEqualThan:
					    {
						    result[pos] &= (valueparam.CompareTo(filter.Value) <= 0);
						    break;
					    }
					    case Relation.NotEqual:
					    {
						    result[pos] &= (valueparam != filter.Value);
						    break;
					    }
					    case Relation.StartsWith:
					    {
						    result[pos] &= (valueparam.StartsWith(filter.Value));
						    break;
					    }
					    case Relation.Contains:
					    {
						    result[pos] &= (valueparam.Contains(filter.Value));
						    break;
					    }
				    }
			    }
		    }

		    finalResult |= result[pos];

		    return finalResult;
	    }

	    /**
	    *@param string name - Field name
	    *@param Relation relation - Relation enum
	    *@param string value - Field string value
	    *@return void
	    *@desc Add a single string comparison to filter.
	    */
	    public void addRelation(string name, Relation relation, string value)
	    {
            IteratorFilterStruct filter = new IteratorFilterStruct();
            filter.Command = IteratorFilterStruct.CMD_AND;
            filter.Name = name;
            filter.Relation = relation;
            filter.Value = value;
		    this._filters.Add(filter);
	    }

	    /**
	    *@param string name - Field name
	    *@param Relation relation - Relation enum
	    *@param string value - Field string value
	    *@return void
	    *@desc Add a single string comparison to filter. This comparison use the OR operator.
	    */
	    public void addRelationOr(string name, Relation relation, string value)
	    {
            IteratorFilterStruct filter = new IteratorFilterStruct();
            filter.Command = IteratorFilterStruct.CMD_OR;
            filter.Name = name;
            filter.Relation = relation;
            filter.Value = value;
		    this._filters.Add(filter);
	    }

	    /**
	     * Add a "("
	     *
	     */
	    public void startGroup()
	    {
            IteratorFilterStruct filter = new IteratorFilterStruct();
            filter.Command = IteratorFilterStruct.CMD_STGRP;
		    this._filters.Add(filter);
	    }

	    /**
	     * Add a ")"
	     *
	     */
	    public void endGroup()
	    {
            IteratorFilterStruct filter = new IteratorFilterStruct();
            filter.Command = IteratorFilterStruct.CMD_ENGRP;
            this._filters.Add(filter);
        }

        public string getFilter()
        {
            DbParameters param;
            return this.generator(2, out param);
        }
    }

}