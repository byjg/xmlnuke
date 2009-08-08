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

namespace com.xmlnuke.Database
{

    public class DbMySQLFunctions : DbBaseFunctions
    {

        /// <summary>
        /// Given two or more string the system will return the string containing de proper SQL commands to concatenate these string;
        /// </summary>
        /// <param name="param"></param>
        /// <returns></returns>
        public override string Concat(string[] param)
        {
            string sql = "concat(";
            for (int i = 0; i<param.Length; i++)
            {
                string var = param[i];
                sql += (i == 0 ? "" : ",") + var;
            }
            sql += ")";

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
    		return sql += " LIMIT " + start.ToString() + "," + qty.ToString() + " ";
        }

        /// <summary>
        /// Given a SQL returns it with the proper TOP or equivalent method included
        /// </summary>
        /// <param name="sql"></param>
        /// <param name="qty"></param>
        /// <returns></returns>
        public override string Top(string sql, int qty)
        {
            return this.Limit(sql, 0, qty);
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
            return true;
        }
    }

}