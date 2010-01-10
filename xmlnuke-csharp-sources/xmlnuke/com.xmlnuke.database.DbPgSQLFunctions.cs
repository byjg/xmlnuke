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

    public class DbPgSQLFunctions : DbBaseFunctions
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
                sql += (i == 0 ? "" : " || ") + var;
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
            if (!sql.Contains(" LIMIT "))
            {
                return sql += " LIMIT " + qty.ToString() + " OFFSET " + start.ToString();
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
            if (String.IsNullOrEmpty(col)) col = "now()";
            string s = "TO_CHAR(" + col + ",'";

            int len = fmt.Length;
            for (int i = 0; i < len; i++)
            {
                char ch = fmt[i];
                switch (ch)
                {
                    case 'Y':
                    case 'y':
                        s += "YYYY";
                        break;
                    case 'Q':
                    case 'q':
                        s += "Q";
                        break;

                    case 'M':
                        s += "Mon";
                        break;

                    case 'm':
                        s += "MM";
                        break;
                    case 'D':
                    case 'd':
                        s += "DD";
                        break;

                    case 'H':
                        s += "HH24";
                        break;

                    case 'h':
                        s += "HH";
                        break;

                    case 'i':
                        s += "MI";
                        break;

                    case 's':
                        s += "SS";
                        break;

                    case 'a':
                    case 'A':
                        s += "AM";
                        break;

                    default:
                        // handle escape characters...
                        if (ch == '\\')
                        {
                            i++;
                            ch = fmt[i];
                        }
                        if ("-/.:;, ".IndexOf(ch) >= 0) s += ch;
                        else s += "\"" + ch + "\"";
                        break;

                }
            }
            return s + "')";
        }

    }

}