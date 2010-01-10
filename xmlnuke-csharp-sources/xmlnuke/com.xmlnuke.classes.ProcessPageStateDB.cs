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
using System.Collections;
using System.Xml;
using com.xmlnuke.engine;
using com.xmlnuke.anydataset;
using com.xmlnuke.database;

namespace com.xmlnuke.classes
{

    /// <summary>
    /// ProcessPageStateDB is class to make easy View, Edit, Delete and Update single tables from relational databases like MySQL, PostGres, Oracle, SQLServer and others.
    /// To use this class is necessary define the fields are used.
    /// </summary>
    /// <example>
    /// <code>
    /// ProcessPageField fieldPage;
    /// ProcessPageFields pageFields = new ProcessPageFields();
    /// 
    /// fieldPage = new ProcessPageField(true);
    /// fieldPage.fieldName = "field1";
    /// fieldPage.key = true;             // Only one key field.
    /// fieldPage.dataType = INPUTTYPE.NUMBER;
    /// fieldPage.fieldCaption = "Key Field";
    /// fieldPage.fieldXmlInput = XmlInputObjectType.TEXTBOX;
    /// fieldPage.visibleInList = true;   // This field must be visible and in the FIRST position
    /// fieldPage.editable = true;        // If the Key field is AutoIncrement set this property 
    ///                                      // to false.
    /// fieldPage.required = true;
    /// pageFields.addProcessPageField(fieldPage);
    /// 
    /// fieldPage = new ProcessPageField(true);
    /// fieldPage.fieldName = "field2";
    /// fieldPage.key = false;
    /// fieldPage.dataType = INPUTTYPE.TEXT;
    /// fieldPage.fieldCaption = "Caption of Field 2";
    /// fieldPage.fieldXmlInput = XmlInputObjectType.TEXTBOX;
    /// fieldPage.visibleInList = true;
    /// fieldPage.editable = true;
    /// fieldPage.required = true;
    /// pageFields.addProcessPageField(fieldPage);
    /// 
    /// fieldPage = new ProcessPageField(true);
    /// fieldPage.fieldName = "field3";
    /// fieldPage.key = false;
    /// fieldPage.dataType = INPUTTYPE.DATE;
    /// fieldPage.fieldCaption = "Caption of Field 3";
    /// fieldPage.fieldXmlInput = XmlInputObjectType.TEXTBOX;
    /// fieldPage.visibleInList = true;
    /// fieldPage.editable = true;
    /// fieldPage.required = true;
    /// pageFields.addProcessPageField(fieldPage);
    /// </code>
    /// After defined all the fields the user must create the class, like this:
    /// <code>
    /// // Create a Block. 
    /// XmlBlockCollection block = new XmlBlockCollection("ProcessPageStateDB Example", BlockPosition.Center);
    /// 
    /// // Create the class passing all relevant paramenters.
    /// ProcessPageStateDB processPage = new ProcessPageStateDB(
    ///                   this._context, 
    ///                   pageFields, 
    ///                   "Editing Table 'mytable'", 
    ///                   "module:sample", 
    ///                   null, 
    ///                   "mytable", 
    ///                   "myconnection");
    /// block1.addXmlnukeObject(processPage);
    /// 
    /// // Create a XmlnukeDocument and generate XML in the CreatePage method.
    /// XmlnukeDocument xmlnukeDoc = new XmlnukeDocument("Titulo da Pgina", "Abstract Dessa Pgina");
    /// xmlnukeDoc.addXmlnukeObject(block1);
    /// return xmlnukeDoc.generatePage();
    /// </code>
    /// </example>
    public class ProcessPageStateDB : ProcessPageStateBase
    {
        protected string _table;
        protected string _conn;
        protected string _fieldDeliLeft = "";
        protected string _fieldDeliRight = "";
        protected DBDataSet _dbData = null;

        /// <summary>
        /// Constructor.
        /// </summary>
        /// <param name="context">XMLNuke context object</param>
        /// <param name="fields">Fields will be processed.</param>
        /// <param name="header">Simple string header</param>
        /// <param name="module">Module will be process this request. Usually is the same module instantiate the ProcessPageStateDB.</param>
        /// <param name="buttons">Custom buttons in View/Select mode.</param>
        /// <param name="table">Table in database. This table must contains all fields defined in "fields" parameter.</param>
        /// <param name="connection">Database connections. <see cref="T:com.xmlnuke.anydata.DBDataSet"/></param>
        public ProcessPageStateDB(Context context, ProcessPageFields fields, string header, string module, CustomButtons[] buttons, string table, string connection)
            : base(context, fields, header, module, buttons)
        {
            this._conn = connection;
            this._table = table;
            this._dbData = new DBDataSet(this._conn, this._context);
        }

        /// <summary>
        /// Returns an IIterator with all records in table
        /// </summary>
        /// <returns>IIterator</returns>
        public override IIterator getAllRecords()
        {
            return this.GetIterator(true);
        }

        /// <summary>
        /// Return a SingleRow with the selection of the user.
        /// </summary>
        /// <returns>SingleRow</returns>
        public override SingleRow getCurrentRecord()
        {
            if (this._currentAction != ProcessPageStateBase.ACTION_NEW)
            {
                IIterator it = this.GetIterator(false);

                if (it.hasNext())
                {
                    return it.moveNext();
                }
            }
            return null;
        }

        protected string getWhereClause(DbParameters param)
        {
            string[] arValueId = this._valueId.Split('|');
            string where = "";
            int i = 0;
            foreach (object keyIndex in this._keyIndex)
            {
                where += ((where != "") ? " and " : "") + this._fields[((int)keyIndex)].fieldName + " = [[valueid" + keyIndex + "]] ";
                DbParameter p = new DbParameter();
                p.Name = "valueid" + keyIndex;
                p.Value = arValueId[i++];
                param.Add(p);
            }
            return where;
        }

        /// <summary>
        /// Execute the proper action to insert, update and delete data from database.
        /// </summary>
        /// <returns>IXmlnukeDocumentObject it contains all necessary XML to inform the user the operation result</returns>
        public override IXmlnukeDocumentObject updateRecord()
        {
            IXmlnukeDocumentObject mdo = this.validateUpdate();
            if (mdo != null)
            {
                return mdo;
            }

            if (this._currentAction == ACTION_NEW_CONFIRM)
            {
                this.ExecuteSQL(SQLType.SQL_INSERT);
            }
            else if (this._currentAction == ACTION_EDIT_CONFIRM)
            {
                this.ExecuteSQL(SQLType.SQL_UPDATE);
            }
            else if (this._currentAction == ACTION_DELETE_CONFIRM)
            {
                this.ExecuteSQL(SQLType.SQL_DELETE);
            }

            //XmlFormCollection retorno = new XmlFormCollection(this._context, this._module, message);
            //retorno.addXmlnukeObject(new XmlInputHidden("filter", this._filter));
            //retorno.addXmlnukeObject(new XmlInputHidden("sort", this._sort));
            //retorno.addXmlnukeObject(new XmlInputHidden("curpage", this._curPage.ToString()));
            //retorno.addXmlnukeObject(new XmlInputHidden("offset", this._qtdRows.ToString()));
            //XmlInputButtons btnRetorno = new XmlInputButtons();
            //btnRetorno.addSubmit("Retornar", "");
            //retorno.addXmlnukeObject(btnRetorno);

            return null;
        }

        /// <summary>
        /// Pre Process the Value Before Create SQL
        /// </summary>
        protected virtual DbParameter preProcessValue(string fieldName, INPUTTYPE dataType, string currentValue)
        {
            DbParameter result = new DbParameter();
            result.Name = fieldName;

            if (currentValue == "")
            {
                result.DataType = System.Data.DbType.String;
                result.Value = null;
            }
            else if (dataType == INPUTTYPE.NUMBER)
            {
                char systemDecimalSeparator = System.Threading.Thread.CurrentThread.CurrentCulture.NumberFormat.NumberDecimalSeparator[0];

                currentValue = currentValue.Replace(this._decimalSeparator, systemDecimalSeparator);
                if (currentValue.IndexOf(systemDecimalSeparator) >= 0)
                {
                    result.DataType = System.Data.DbType.Double;
                    double dblValue;
                    Double.TryParse(currentValue, out dblValue);
                    result.Value = dblValue;
                }
                else
                {
                    result.DataType = System.Data.DbType.Int32;
                    int intValue;
                    Int32.TryParse(currentValue, out intValue);
                    result.Value = intValue;
                }
            }
            else if ((dataType == INPUTTYPE.DATE) || (dataType == INPUTTYPE.DATETIME))
            {
                result.DataType = System.Data.DbType.DateTime;
                result.Value = this._dbData.getDbFunctions().toDate(currentValue, this._dateFormat, (dataType == INPUTTYPE.DATETIME));
            }
            else
            {
                result.DataType = System.Data.DbType.String;
                result.Value = currentValue;
            }

            return result;
        }

        public string fieldDeliLeft
        {
            get { return this._fieldDeliLeft; }
            set { this._fieldDeliLeft = value; }
        }

        public string fieldDeliRight
        {
            get { return this._fieldDeliRight; }
            set { this._fieldDeliRight = value; }
        }



        protected void ExecuteSQL(SQLType sqlType)
        {
            SQLFieldArray sqlfields = new SQLFieldArray();
            if (sqlType != SQLType.SQL_DELETE)
            {
                foreach (ProcessPageField field in this._fields)
                {
                    if (field.editable)
                    {
                        DbParameter paramItem = this.preProcessValue(field.fieldName, field.dataType, this._context.ContextValue(field.fieldName));
                        sqlfields.Add(paramItem.Name, paramItem.Value);
                    }
                }
            }

            string filter = "";
            DbParameters filterParam = new DbParameters();
            if (sqlType != SQLType.SQL_INSERT)
            {
                filter = this.getWhereClause(filterParam);
            }

            SQLHelper helper = new SQLHelper(this._dbData);
            helper.setFieldDelimeters(this.fieldDeliLeft, this.fieldDeliRight);
            SQLUpdateData retdata = helper.generateSQL(this._table, sqlfields, sqlType, filter, filterParam, this._decimalSeparator);

            this.DebugInfo(retdata.SQL, retdata.Parameters);
            this._dbData.execSQL(retdata.SQL, retdata.Parameters);
        }

        protected IIterator GetIterator(bool getAll)
        {
            string fields = "";
            foreach (ProcessPageField field in this._fields)
            {
                if ((field.visibleInList) || (field.key) || (!getAll))
                {
                    if (fields != "") fields += ",";
                    fields += this.fieldDeliLeft + field.fieldName + this.fieldDeliRight;
                }
            }

            string sql =
                "select " + fields + " " +
                "from " + this._table + " ";

            DbParameters param = new DbParameters();
            if (!getAll)
            {
                sql += " where " + this.getWhereClause(param);
            }
            if (!String.IsNullOrEmpty(this._filter))
            {
                sql += (getAll ? " where " : " and ") + this.getFilter();
            }
            if (!String.IsNullOrEmpty(this._sort) && getAll)
            {
                sql += " order by " + this.getSort();
            }

            this.DebugInfo(sql, param);
            return this._dbData.getIterator(sql, param);
        }

        protected void DebugInfo(string sql, DbParameters param)
        {
            if (this._context.getDebugInModule())
            {

                util.Debug.Print("<hr>");
                util.Debug.Print("Class name: " + "");
                util.Debug.Print("SQL: " + sql);
                if (param != null)
                {
                    string s = "";
                    foreach (DbParameter p in param)
                    {
                        if (s != "")
                        {
                            s += ", ";
                        }
                        s += "[" + p.Name + "]=" + p.Value;
                    }
                    util.Debug.Print("Params: " + s);
                }
            }
        }


        /**
         * Format a date field from Database values
         * @param $curValue
         * @return string
         */
        protected override string dateFromSource(string curValue, bool hour)
        {
            try
            {
                return this._dbData.getDbFunctions().fromDate(curValue, this._dateFormat, hour);
            }
            catch
            {
                return "??/??/????";
            }
        }

        /**
         * 
         * @param EditListField $editListField
         * @param ProcessPageField $field
         * @return EditListField
         */
        protected EditListField editListFieldCustomize(EditListField editListField, ProcessPageField field)
        {
            if ((((field.dataType == INPUTTYPE.DATE) || (field.dataType == INPUTTYPE.DATETIME))) && (field.editListFormatter == null))
            {
                editListField.fieldType = EditListFieldType.FORMATTER;
                editListField.formatter = new ProcessPageStateDBFormatterDate(this._dbData, this._dateFormat, (field.dataType == INPUTTYPE.DATETIME));
            }
            return editListField;
        }
    }




    public class ProcessPageStateDBFormatterDate : IEditListFormatter
    {
        /**
         * @var DBDataSet
         */
        protected DBDataSet _dbData = null;
        protected bool _hour = false;
        protected DATEFORMAT _dateFormat;

        public ProcessPageStateDBFormatterDate(DBDataSet dbData, DATEFORMAT dateFormat, bool hour)
        {
            this._dbData = dbData;
            this._dateFormat = dateFormat;
            this._hour = hour;
        }

        public string Format(SingleRow row, string fieldname, string value)
        {
            if (value != "")
            {
                return this._dbData.getDbFunctions().fromDate(value, this._dateFormat, this._hour);
            }
            else
            {
                return "";
            }
        }
    }

}
