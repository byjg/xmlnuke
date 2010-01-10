/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification and Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
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
using com.xmlnuke.util;
using com.xmlnuke.classes;

namespace com.xmlnuke.Database
{

    public abstract class DbBaseFunctions : IDbFunctions
    {

        /// <summary>
        /// Given two or more string the system will return the string containing de proper SQL commands to concatenate these string;
        /// </summary>
        /// <param name="s1"></param>
        /// <param name="s2"></param>
        /// <returns></returns>
        public virtual string Concat(string s1, string s2)
        {
            string[] x = new string[] { s1, s2 };

            return this.Concat(x);
        }

        /// <summary>
        /// Given two or more string the system will return the string containing de proper SQL commands to concatenate these string;
        /// </summary>
        /// <param name="param"></param>
        /// <returns></returns>
        public virtual string Concat(string[] param)
        {
            return "";
        }

        /// <summary>
        /// Given a SQL returns it with the proper LIMIT or equivalent method included
        /// </summary>
        /// <param name="sql"></param>
        /// <param name="start"></param>
        /// <param name="qty"></param>
        /// <returns></returns>
        public virtual string Limit(string sql, int start, int qty)
        {
            return sql;
        }

        /// <summary>
        /// Given a SQL returns it with the proper TOP or equivalent method included
        /// </summary>
        /// <param name="sql"></param>
        /// <param name="qty"></param>
        /// <returns></returns>
        public virtual string Top(string sql, int qty)
        {
            return sql;
        }

        /// <summary>
        /// Return if the database provider have a top or similar function 
        /// </summary>
        /// <returns></returns>
        public virtual bool hasTop()
        {
            return false;
        }

        /// <summary>
        /// Return if the database provider have a limit function 
        /// </summary>
        /// <returns></returns>
        public virtual bool hasLimit()
        {
            return false;
        }

        /// <summary>
	    /// Format date column in sql string given an input format that understands Y M D
        /// example $db->getDbFunctions()->SQLDate("d/m/Y H:i", "dtcriacao")
        /// </summary>
        /// <param name="fmt"></param>
        /// <param name="col"></param>
        /// <returns></returns>
	    public string SQLDate(string fmt, string col)
	    {
		    return "";
	    }

        public string toDate(string date, DATEFORMAT dateFormat)
        {
            return this.toDate(date, dateFormat, false);
        }

        /// <summary>
        /// Format a string to database readable format.
        /// </summary>
        /// <param name="date"></param>
        /// <param name="dateFormat"></param>
        /// <param name="hour"></param>
        /// <returns></returns>
	    public virtual string toDate(string date, DATEFORMAT dateFormat, bool hour)
	    {
		    return DateUtil.ConvertDate(date, dateFormat, DATEFORMAT.YMD, hour);
	    }

	    public string fromDate(string date, DATEFORMAT dateFormat)
        {
            return this.fromDate(date, dateFormat, false);
        }

    	/// <summary>
    	/// Format a string from database to a user readable format.
    	/// </summary>
    	/// <param name="date"></param>
    	/// <param name="dateFormat"></param>
    	/// <param name="hour"></param>
    	/// <returns></returns>
	    public virtual string fromDate(string date, DATEFORMAT dateFormat, bool hour)
	    {
		    return DateUtil.ConvertDate(date, DATEFORMAT.YMD, dateFormat, hour);
	    }
    }

}