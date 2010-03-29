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
using System.Text.RegularExpressions;
using com.xmlnuke.anydataset;

namespace com.xmlnuke.Database
{

    public class DbOleDbFunctions : DbBaseFunctions
    {

        /// <summary>
        /// Given two or more string the system will return the string containing de proper SQL commands to concatenate these string;
        /// </summary>
        /// <param name="param"></param>
        /// <returns></returns>
        public override string Concat(string[] param)
        {
            string sql = "";
            for (int i = 0; i < param.Length; i++)
            {
                string var = param[i];
                sql += (i == 0 ? "" : " + ") + var;
            }

            return sql;
        }

        /// <summary>
        /// Given a SQL returns it with the proper LIMIT or equivalent method included
        /// </summary>
        /// <param name="sql"></param>
        /// <param name="start"></param>
        /// <param name="qty"></param>
        /// <returns></returns>
        public override string Limit(string sql, int start, int qty)
        {
            return sql;
        }

        /// <summary>
        /// Given a SQL returns it with the proper TOP or equivalent method included
        /// </summary>
        /// <param name="sql"></param>
        /// <param name="qty"></param>
        /// <returns></returns>
        public override string Top(string sql, int qty)
        {
            String replacement;
            Regex rx = new Regex("/(^\\s*select\\s+(distinctrow|distinct)?)/i");
            replacement = "\\1 TOP "+ qty + " ";
            return rx.Replace(sql, replacement);
		    //preg_replace('/(^\s*select\s+(distinctrow|distinct)?)/i','\\1 TOP '.qty.' ',$sql);            
		    //return this.Limit(sql, 0, qty);
        }

        /// <summary>
        /// Return if the database provider have a top or similar function 
        /// </summary>
        /// <returns></returns>
        public override bool hasTop()
        {
            return true;
        }

        /// <summary>
        /// Return if the database provider have a limit function 
        /// </summary>
        /// <returns></returns>
        public override bool hasLimit()
        {
            return false;
        }

        /**
         * Format date column in sql string given an input format that understands Y M D
         * @param string fmt
         * @param string col
         * @return string
         * @example db.getDbFunctions().SQLDate("d/m/Y H:i", "dtcriacao")
         */
        public string SQLDate(string fmt, string col)
        {
			throw new Exception("Not available for OleDb");
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="dbdataset"></param>
        /// <param name="sql"></param>
        /// <param name="param"></param>
        /// <returns></returns>
        public int executeAndGetInsertedId(com.xmlnuke.anydataset.DBDataSet dbdataset, string sql, com.xmlnuke.anydataset.DbParameters param)
        {
			// http://databases.aspfaq.com/general/how-do-i-get-the-identity/autonumber-value-for-the-row-i-inserted.html

			// ********************************
			// Note:
			// This is a trick routine, because there is no specific way defined to get the last inserted id
			// ********************************
			
			int id = base.executeAndGetInsertedId(dbdataset, sql, param);

            Regex rx = new Regex(@"/^\s*insert\s*into\s*(?<tablename>[\w\d]*)\s*/i");
			Match m = rx.Match(sql);
			
			if (m.Success)
			{
				string tableName = m.Groups["tablename"].Value;
			
				System.Data.Common.DbDataAdapter dataAdapter = dbdataset.getDataAdpater(tableName);
				string key = dataAdapter.UpdateCommand.Parameters.Item(0).SourceColumn;
			
		    	IIterator it = dbdataset.getIterator("select max(" + key + ") id from " + tableName);
			    if (it.hasNext())
			    {
				    SingleRow sr = it.moveNext();
	                Int32.TryParse(sr.getField("id"), out id);
			    }
			}
			
		    return id;
        }
    }

}
