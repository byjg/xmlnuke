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
using System.Xml;
using System.Data;
using System.Data.Odbc;
using com.xmlnuke.engine;
using com.xmlnuke.classes;
using System.Collections;

namespace com.xmlnuke.anydataset
{

	public class DBIterator : IIterator
	{
		private const int RECORD_BUFFER = 50;
		private System.Collections.Queue _rowBuffer;

		private IDataReader _reader;
		private Context _context;
		private string[] _fieldNames;
		private INPUTTYPE[] _fieldTypes;
		private IDbConnection _db;

		/// <summary>
		/// Class it implements IIterator interface. 
		/// You need to use the getIterator method in a AnyDataSet class to create an Iterator.
		/// </summary>
		/// <param name="reader">IDataReader .NET</param>
		/// <param name="context">XMLNuke context</param>
		/// <seealso cref="com.xmlnuke.anydataset.DBDataSet">DBDataSet</seealso>
		public DBIterator(IDataReader reader, Context context, IDbConnection db)
		{
			this._context = context;
			this._reader = reader;
			this._db = db;
			_fieldNames = new string[reader.FieldCount];
			_fieldTypes = new INPUTTYPE[reader.FieldCount];
			for (int i = 0; i < this._reader.FieldCount; i++)
			{
				_fieldNames[i] = this._reader.GetName(i);
				if ((this._reader.GetFieldType(i) == typeof(System.Byte)) ||
					 (this._reader.GetFieldType(i) == typeof(System.Int16)) ||
					 (this._reader.GetFieldType(i) == typeof(System.Int32)) ||
					 (this._reader.GetFieldType(i) == typeof(System.Int64)) ||
					 (this._reader.GetFieldType(i) == typeof(System.Single)) ||
					 (this._reader.GetFieldType(i) == typeof(System.Double)) ||
					 (this._reader.GetFieldType(i) == typeof(System.Decimal)) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.Odbc.OdbcType.TinyInt.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.Odbc.OdbcType.SmallInt.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.Odbc.OdbcType.Int.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.Odbc.OdbcType.BigInt.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.Odbc.OdbcType.Real.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.Odbc.OdbcType.Double.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.Odbc.OdbcType.Decimal.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.Odbc.OdbcType.Numeric.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.OleDb.OleDbType.TinyInt.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.OleDb.OleDbType.SmallInt.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.OleDb.OleDbType.Integer.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.OleDb.OleDbType.BigInt.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.OleDb.OleDbType.Single.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.OleDb.OleDbType.Double.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.OleDb.OleDbType.Decimal.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.SqlDbType.TinyInt.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.SqlDbType.SmallInt.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.SqlDbType.Int.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.SqlDbType.BigInt.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.SqlDbType.Real.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.SqlDbType.Float.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.SqlDbType.Decimal.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.SqlDbType.Money.ToString())) ||
					 (this._reader.GetFieldType(i) == Type.GetType(System.Data.SqlDbType.SmallMoney.ToString()))
				   )
				{
					_fieldTypes[i] = INPUTTYPE.NUMBER;
				}
				else if ((this._reader.GetFieldType(i) == typeof(System.DateTime)) ||
						  (this._reader.GetFieldType(i) == Type.GetType(System.Data.Odbc.OdbcType.DateTime.ToString())) ||
						  (this._reader.GetFieldType(i) == Type.GetType(System.Data.OleDb.OleDbType.Date.ToString())) ||
						  (this._reader.GetFieldType(i) == Type.GetType(System.Data.OleDb.OleDbType.DBDate.ToString())) ||
						  (this._reader.GetFieldType(i) == Type.GetType(System.Data.SqlDbType.DateTime.ToString())) ||
						  (this._reader.GetFieldType(i) == Type.GetType(System.Data.SqlDbType.SmallDateTime.ToString()))
						)
				{
					_fieldTypes[i] = INPUTTYPE.DATE;
				}
				else
				{
					_fieldTypes[i] = INPUTTYPE.TEXT;
				}

				this._rowBuffer = new System.Collections.Queue();
			}
		}

		/// <summary>
		/// Get an array with all fields returned by SQL command.
		/// </summary>
		/// <returns>String with field names</returns>
		public string[] getFieldNames()
		{
			return this._fieldNames;
		}

		/// <summary>
		/// Get an array with all field types. This array uses INPUTTYPE definition.
		/// </summary>
		/// <returns>array of INPUTTYPE</returns>
		public INPUTTYPE[] getFieldTypes()
		{
			return this._fieldTypes;
		}

		/// <summary>
		/// Not implemented yet.
		/// </summary>
		/// <returns>a number zero</returns>
		public int Count()
		{
			// Not implemented yet!
			return 0;
		}

		/// <summary>
		/// Check if exists more records and move the pointer to the next record.
		/// </summary>
		/// <returns>True if exists more records.</returns>
		public bool hasNext()
		{
			if (this._rowBuffer.Count >= RECORD_BUFFER)
			{
				return true;
			}
			else if (this._reader != null && this._reader.Read())
			{
				AnyDataSet any = new AnyDataSet();
				any.appendRow();
				for (int i = 0; i < this._reader.FieldCount; i++)
				{
					string fieldName = this._fieldNames[i].ToString().ToLower();
					Object readerValue;
					try
					{
						readerValue = this._reader.GetValue(i);
					}
					catch (Exception)
					{
						// Some inconsistent data from Database causes erros.
						// This info is discarted
						// Example: MySql.DataTime '0000-00-00 00:00:00'
						readerValue = DBNull.Value;
					}

					if (readerValue == DBNull.Value)
					{
						any.addField(fieldName, "");
					}
					else if (this.getFieldTypes()[i] == INPUTTYPE.DATE)
					{
						any.addField(fieldName, this._reader.GetDateTime(i));
					}
					else if (this.getFieldTypes()[i] == INPUTTYPE.NUMBER)
					{
						any.addField(fieldName, Convert.ToString(readerValue));
					}
					else
					{
						any.addField(fieldName, readerValue.ToString());
					}
				}

				Iterator it = any.getIterator();

				// Enfileira o registo
				this._rowBuffer.Enqueue(it.moveNext());
				// Traz novos até encher o Buffer
				if (this._rowBuffer.Count < RECORD_BUFFER)
					this.hasNext();

				return true;
			}
			else if (this._reader == null)
			{
				return (this._rowBuffer.Count > 0);
			}
			else
			{
				this._reader.Close();
				this._reader = null;
				if (this._db != null)
				{
					this._db.Close();
					this._db = null;
				}
				return (this._rowBuffer.Count > 0);
			}
		}

		/// <summary>
		/// Return the current record.
		/// </summary>
		/// <returns>SingleRow it contains the current record.</returns>
		public SingleRow moveNext()
		{
			if (!this.hasNext())
			{
				throw new Exception("No more records. Did you used hasNext() before moveNext()?");
			}
			else
			{
				return (SingleRow)this._rowBuffer.Dequeue();
			}
		}

        #region IEnumerable Members

        public IEnumerator GetEnumerator()
        {
            return new IteratorEnumerable(this);
        }

        #endregion
    }

}
