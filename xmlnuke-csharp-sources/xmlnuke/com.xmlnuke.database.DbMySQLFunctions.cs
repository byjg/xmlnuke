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
using com.xmlnuke.anydataset;

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
            for (int i = 0; i < param.Length; i++)
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
            if (!sql.Contains(" LIMIT "))
            {
                return sql += " LIMIT " + start.ToString() + "," + qty.ToString() + " ";
            }
            else
            {
                return sql;
            }
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

        /**
         * Format date column in sql string given an input format that understands Y M D
         * @param string fmt
         * @param string col
         * @return string
         * @example db.getDbFunctions().SQLDate("d/m/Y H:i", "dtcriacao")
         */
        public string SQLDate(string fmt, string col)
        {
            if (String.IsNullOrEmpty(col)) col = "NOW()";
            string s = "DATE_FORMAT(" + col + ",'";
            bool concat = false;
            int len = fmt.Length;
            for (int i = 0; i < len; i++)
            {
                char ch = fmt[i];
                switch (ch)
                {
                    case 'Y':
                    case 'y':
                        s += "%Y";
                        break;
                    case 'Q':
                    case 'q':
                        s += "'),Quarter(" + col + ")";

                        if (len > i + 1) s += ",DATE_FORMAT(" + col + ",'";
                        else s += ",('";
                        concat = true;
                        break;
                    case 'M':
                        s += "%b";
                        break;

                    case 'm':
                        s += "%m";
                        break;
                    case 'D':
                    case 'd':
                        s += "%d";
                        break;

                    case 'H':
                        s += "%H";
                        break;

                    case 'h':
                        s += "%I";
                        break;

                    case 'i':
                        s += "%i";
                        break;

                    case 's':
                        s += "%s";
                        break;

                    case 'a':
                    case 'A':
                        s += "%p";
                        break;

                    default:

                        if (ch == '\\')
                        {
                            i++;
                            ch = fmt[i];
                        }
                        s += ch;
                        break;
                }
            }
            s += "')";
            if (concat) s = "CONCAT(" + s + ")";
            return s;
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
		    int id = base.executeAndGetInsertedId(dbdataset, sql, param);
		    IIterator it = dbdataset.getIterator("select LAST_INSERT_ID() id");
		    if (it.hasNext())
		    {
			    SingleRow sr = it.moveNext();
                Int32.TryParse(sr.getField("id"), out id);
		    }

		    return id;
        }
    }

}