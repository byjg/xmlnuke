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
using System.Collections;
using System.Collections.Specialized;
using System.Xml;
using com.xmlnuke.engine;
using com.xmlnuke.classes;
using com.xmlnuke.anydataset;
using com.xmlnuke.util;
using com.xmlnuke.Database;

namespace com.xmlnuke.database
{

    public abstract class BaseDBAccess
    {

        protected DBDataSet _db = null;

        protected Context _context = null;

        protected SQLHelper _sqlhelper = null;

        public BaseDBAccess(Context context)
        {
            this._context = context;
        }

        public abstract string getDataBaseName();

        protected DBDataSet getDBDataSet()
        {
            if (this._db == null)
            {
                this._db = new DBDataSet(this.getDataBaseName(), this._context);
            }
            return this._db;
        }

        protected void executeSQL(string sql)
        {
            this.executeSQL(sql, null);
        }

        protected void executeSQL(string sql, DbParameters param)
        {
            DBDataSet db = this.getDBDataSet();

            int start = this.printDebugInfoHeader(sql, param);

            if (param == null)
            {
                db.execSQL(sql);
            }
            else
            {
                db.execSQL(sql, param);
            }

            this.printDebugInfoFooter(start);

        }

        protected void executeSQL(SQLUpdateData data)
        {
            this.executeSQL(data.SQL, data.Parameters);
        }


        protected IIterator getIterator(string sql)
        {
            return this.getIterator(sql, null);
        }

        protected IIterator getIterator(string sql, DbParameters param)
        {
            DBDataSet db = this.getDBDataSet();

            int start = this.printDebugInfoHeader(sql, param);

            IIterator it;
            if (param == null)
            {
                it = db.getIterator(sql);
            }
            else
            {
                it = db.getIterator(sql, param);
            }

            this.printDebugInfoFooter(start);

            return it;
        }

        private int printDebugInfoHeader(string sql, DbParameters param)
        {
            bool debug = this._context.getDebugInModule();
            int start = 0;
            if (debug)
            {
                Debug.Print("<hr>");
                Debug.Print("Class name: " + "");
                Debug.Print("SQL: " + sql);
                if (param != null)
                {
                    string s = "";
                    foreach (DbParameter par in param)
                    {
                        if (s != "")
                        {
                            s += ", ";
                        }
                        s += string.Format("[{0}]={1}", par.Name, par.Value);
                    }
                    Debug.Print("Params: " + s);
                }
                start = System.Environment.TickCount;
            }

            return start;
        }

        private void printDebugInfoFooter(int start)
        {
            if (start > 0)
            {
                int total = System.Environment.TickCount - start;
                Debug.Print("Execution time: " + total.ToString() + " ms ");
            }
        }

        public SQLHelper getSQLHelper()
        {
            this.getDBDataSet();

            if (this._sqlhelper == null)
            {
                this._sqlhelper = new SQLHelper(this._db);
            }

            return this._sqlhelper;
        }

        protected IIterator getIteratorbyId(string tablename, string key, object value)
        {
            string sql = "select * from " + tablename + " where " + key + " = [[key]] ";
            DbParameters param = new DbParameters();
            param.Add("key", value);
            return this.getIterator(sql, param);
        }

        public static NameValueCollection getArrayFromIterator(IIterator it, string key, string value)
        {
            return BaseDBAccess.getArrayFromIterator(it, key, value, "-- Selecione --");
        }
        public static NameValueCollection getArrayFromIterator(IIterator it, string key, string value, string firstElement)
        {
            NameValueCollection retArray = new NameValueCollection();
            if (!String.IsNullOrEmpty(firstElement))
            {
                retArray.Add("", firstElement);
            }
            while (it.hasNext())
            {
                SingleRow sr = it.moveNext();
                retArray[sr.getField(key)] = sr.getField(value);
            }
            return retArray;
        }


        /// <summary>
        /// Get a IDbFunctions class containing specific database operations
        /// </summary>
        /// <returns></returns>
        public IDbFunctions getDbFunctions()
        {
            return this.getDBDataSet().getDbFunctions();
        }

    }
}