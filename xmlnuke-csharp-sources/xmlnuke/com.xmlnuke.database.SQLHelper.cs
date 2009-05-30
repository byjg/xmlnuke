/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML+
 *
 *  Main Specification and Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
 * 
 *  This file is part of XMLNuke project+ Visit http://www+xmlnuke+com
 *  for more information+
 *  
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version+
 *  
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE+  See the
 *  GNU General Public License for more details+
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc+, 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA+
 *
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= 
 */
using System;
using System.Reflection;
using System.Collections;
using System.Collections.Specialized;
using System.Xml;
using com.xmlnuke.classes;
using com.xmlnuke.anydataset;

namespace com.xmlnuke.database
{

	public enum SQLType
	{
		SQL_UPDATE,
		SQL_INSERT,
		SQL_DELETE
	}

	public enum SQLFieldType
	{
		Literal,
		Text,
		Number,
		Date,
		Boolean,
		AutoDetectByValue
	}

	public struct SQLUpdateData
	{
		public string SQL;
		public DbParameters Parameters;
	}

	public struct SQLField
	{
		public SQLFieldType FieldType;
		public string FieldName;
		public object FieldValue;
		public bool Key;

		public SQLField(SQLFieldType fieldType, string fieldName, object fieldValue, bool key)
		{
			this.FieldType = fieldType;
			this.FieldName = fieldName;
			this.FieldValue = fieldValue;
			this.Key = key;
		}		
	}

	public class SQLFieldArray : CollectionBase
	{
		public int Add(SQLFieldType fieldType, string fieldName, object fieldValue, bool key)
		{
			return base.List.Add(new SQLField(fieldType, fieldName, fieldValue, key));
		}

		public int Add(SQLFieldType fieldType, string fieldName, object fieldValue)
		{
			return this.Add(fieldType, fieldName, fieldValue, false);
		}

		public int Add(string fieldName, object fieldValue, bool key)
		{
			return this.Add(SQLFieldType.AutoDetectByValue, fieldName, fieldValue, key);
		}

		public int Add(string fieldName, object fieldValue)
		{
			return this.Add(SQLFieldType.AutoDetectByValue, fieldName, fieldValue, false);
		}

		public int Add(SQLField field)
		{
			return base.List.Add(field);
		}

		public bool Contains(SQLField field)
		{
			return base.List.Contains(field);
		}

		public void Insert(int index, SQLField field)
		{
			base.List.Insert(index, field);
		}

		public SQLField this[int index]
		{
			get
			{
				object o = base.List[index];
				if (o == null)
				{
					throw new Exception("Cannot retrieve SQLField index " + index.ToString());
				}
				else
				{
					return (SQLField)o;
				}
			}
		}

		public void Remove(SQLField field)
		{
			base.List.Remove(field);
		}
	}

	public class SQLHelper
	{
		/**
		 * @var DBDataSet
		 */
		protected DBDataSet _db;
		

		protected string _fieldDeliLeft = "";
		protected string _fieldDeliRight = "";

		public SQLHelper(DBDataSet db)
		{
			this._db = db;
		}
		
		public SQLUpdateData generateSQL(string table, BaseModel model, SQLType sqltype)
		{
			SQLFieldArray fields = new SQLFieldArray();
			
			Type t = model.GetType();

			PropertyInfo[] pi = t.GetProperties();
			foreach (PropertyInfo prop in pi)
			{
				if (prop.CanRead)
				{
					fields.Add(prop.Name, prop.GetValue(t, null)); 
				}
			}
			
			return this.generateSQL(table, fields, sqltype);
		}

		public SQLUpdateData generateSQL(string table, SQLFieldArray fields, SQLType type)
		{
			return this.generateSQL(table, fields, type, "", null, '.');
		}

		public SQLUpdateData generateSQL(string table, SQLFieldArray fields, SQLType type, string filter, char decimalpoint)
		{
			return this.generateSQL(table, fields, type, filter, null, decimalpoint);
		}
		
		public SQLUpdateData generateSQL(string table, SQLFieldArray fields, SQLType type, string filter, DbParameters filterParam, char decimalpoint)
		{
			SQLUpdateData retData = new SQLUpdateData();
			retData.Parameters = new DbParameters();
			retData.SQL = "";

			if (type == SQLType.SQL_UPDATE)
			{
				foreach (SQLField field in fields)
				{
					if (!field.Key)
					{
						if (retData.SQL != "")
						{
							retData.SQL += ", ";
						}
						retData.SQL += " " + this._fieldDeliLeft + field.FieldName + this._fieldDeliRight + " = " + this.getValue(field, decimalpoint, retData.Parameters) + " ";
					}
					else
					{
						if (filter != "")
						{
							filter += " and ";
						}
						filter += " " + field.FieldName + " = " + this.getValue(field, decimalpoint, retData.Parameters) + " ";
					}
				}
				retData.SQL = "update " + table + " set " + retData.SQL + " where " + filter;
			}
			else if (type == SQLType.SQL_INSERT)
			{
				string campos = "";
				string valores = "";
				foreach (SQLField field in fields)
				{
					util.Debug.Print(field.FieldName);
					if (campos != "")
					{
						campos += ", ";
						valores += ", ";
					}
					campos += this._fieldDeliLeft + field.FieldName + this._fieldDeliRight;
					valores += this.getValue(field, decimalpoint, retData.Parameters);
				}
				retData.SQL = "insert into " + table + " (" + campos + ") values (" + valores + ")";
				util.Debug.Print(retData.SQL);
			}
			else if (type == SQLType.SQL_DELETE) 
			{
				if (String.IsNullOrEmpty(filter))
				{
					throw new Exception("I can't generate delete statements without filter");
				}
				retData.SQL = "delete from " + table + " where " + filter;
			}
			
			if (filterParam != null)
			{
				foreach(DbParameter param in filterParam)
				{
					retData.Parameters.Add(param);
				}
			}
			
			return retData;
		}

		/**
		 * Generic Function
		 *
		 * @param unknown_type $valores
		 * @return unknown
		 */
		protected string getValue(SQLField field, char decimalpoint, DbParameters param)
		{
			string retValue = "[[" + field.FieldName + "]]";

			if ((field.FieldValue == null) || (field.FieldValue.ToString() ==""))
			{
				retValue = "null";
			}
			else
			{
				param.Add(field.FieldName, field.FieldValue);
			}

			return retValue;
		}

		public void setFieldDelimeters(string left, string right)
		{
			this._fieldDeliLeft = left;
			this._fieldDeliRight = right;
		}		
	}
}