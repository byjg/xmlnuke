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
using System.Data.Common;
using System.Collections;
using com.xmlnuke.engine;
using com.xmlnuke.processor;
using System.Reflection;

namespace com.xmlnuke.anydataset
{
	/// <summary>
	/// Class to retrive and stored data in SGDB (relational databases). The key point od this class
	/// is work with relational databases like work with Anydataset classes (xml). 
	/// This class supports ODBC and OLEDB connections.
	/// </summary>
	/// <example>
	/// Retrieve data from a MySQL database
	/// <code>
	/// DBDataSet dbdata = new DBDataSet("connectionkey", this._context);
	/// DBIterator it = dbdata.getIterator("select field1, field2 from tablename");
	/// XmlParagraphCollection paraDB = new XmlParagraphCollection();
	/// while (it.hasNext())
	/// {
	///    SingleRow sr = it.moveNext();
	///    paraDB.addXmlnukeObject(new XmlnukeText(sr.getField("field1"));
	///    paraDB.addXmlnukeObject(new XmlnukeBreakLine());
	/// }
	/// </code>
	/// </example>
	/// <example>
	/// Store data in a MySQL database
	/// <code>
	/// DBDataSet dbdata = new DBDataSet("connectionkey", this._context);
	/// dbdata.execSQL("update tablename set field1='value' where keyfield = 1");
	/// }
	/// </code>
	/// </example>
	/// <example>
	/// Configuration file for ODBC databases
	/// <code>
	/// &lt;anydataset&gt;
	///   &lt;row&gt;
	///     &lt;field name="dbname"&gt;connectionkey&lt;/field&gt;
	///     &lt;field name="dbtype"&gt;ODBC&lt;/field&gt;
	///     &lt;field name="dbconnectionstring"&gt;Driver={MySQL ODBC 3.51 Driver};Server=localhost;UID=root;PWD=password;Database=test&lt;/field&gt;
	///   &lt;/row&gt;
	///   &lt;row&gt;
	///     &lt;field name="dbname"&gt;connectionkey2&lt;/field&gt;
	///     &lt;field name="dbtype"&gt;ODBC&lt;/field&gt;
	///     &lt;field name="dbconnectionstring"&gt;DSN=dsnodbcentry;&lt;/field&gt;
	///   &lt;/row&gt;
	/// &lt;/anydataset&gt;
	/// </code>
	/// </example>
	public class DBDataSet
	{
		private Context _context;
		private string connectionString;
		protected string _dbname;
		protected string _dbtype;
		protected DbTransaction transaction = null;

		protected DbConnection _connection = null;

		/// <summary>
		/// DBDataset Construtor
		/// </summary>
		/// <param name="dbname">Connection String alias.</param>
		/// <param name="context">XMLnuke context class</param>
		public DBDataSet(string dbname, Context context)
		{
			this._context = context;

			AnydatasetFilenameProcessor configFile = new AnydatasetFilenameProcessor("_db", context);

			AnyDataSet config = new AnyDataSet(configFile);
			IteratorFilter filter = new IteratorFilter();
			filter.addRelation("dbname", Relation.Equal, dbname);
			Iterator it = config.getIterator(filter);
			if (!it.hasNext())
			{
				throw new Exception("Database name " + dbname + " not found!");
			}

			SingleRow data = it.moveNext();

			this._dbname = dbname;
			this._dbtype = data.getField("dbtype");
			this.connectionString = data.getField("dbconnectionstring");
		}


		/// <summary>
		/// Gets an IIterator interface (DBIterator object) from a SQL command.
		/// </summary>
		/// <param name="sql">SQL Command.</param>
		/// <returns>return a DBIterator</returns>
		public DBIterator getIterator(string sql)
		{
			return this.getIterator(sql, null);
		}

		protected DbCommand getCommand(IDbConnection db, string sql, DbParameters parameters)
		{
			DbParameters modifiedParameters;

			DbCommand command = (DbCommand)db.CreateCommand();
			command.CommandText = XmlnukeProviderFactory.ParseSQL(this._dbtype, sql, parameters, out modifiedParameters);
			command.CommandType = CommandType.Text;
			command.Transaction = this.transaction;
			this.assignParameters(command, modifiedParameters);
			if (db.State != System.Data.ConnectionState.Open)
			{
				db.Open();
			}
			return command;
		}

		/// <summary>
		/// Gets an IIterator interface (DBIterator object) from a SQL command with bind parameters.
		/// </summary>
		/// <code>
		///	   DBDataSet db = new DBDataSet("bdconection", context);
		///    DbParameters parameters = new DbParameters();
		///    param.Add("id", System.Data.DbType.Int32, 1500);
		///    
		///	   Iterator it = db.getIterator("select * from tabela where id = [[id]] ", parameters);
		/// </code>
		/// <remarks>
		/// Parameters must be declared in sequence order for using...
		/// </remarks>
		/// <param name="sql">SQL Command.</param>
		/// <returns>return a DBIterator</returns>
		public DBIterator getIterator(string sql, DbParameters parameters)
		{
			IDbConnection db = this.getConnection(this.transaction != null);
			IDbCommand command = this.getCommand(db, sql, parameters);
			//this._context.Debug(sql);
			IDataReader reader;
			DBIterator it;
			if (this.transaction == null)
			{
				reader = command.ExecuteReader(System.Data.CommandBehavior.CloseConnection);
				it = new DBIterator(reader, this._context, db);
			}
			else
			{
				reader = command.ExecuteReader();
				it = new DBIterator(reader, this._context, null);
			}

			return it;
		}

		public string[] getAllFields(string tablename)
		{
			//IDbConnection conn = this.getConnection(false);
			IDbDataAdapter adapter = this.getDataAdpater("select * from " + tablename + " where 0=1", null);
			DataSet DS = new DataSet();
			adapter.Fill(DS);

			string[] fields = new string[DS.Tables[0].Columns.Count];

			for (int i = 0; i < DS.Tables[0].Columns.Count; i++)
			{
				fields[i] = DS.Tables[0].Columns[i].ColumnName;
			}
			return fields;
		}

		/// <summary>
		/// Execute a SQL command (insert, update or delete) in database.
		/// </summary>
		/// <param name="sql"></param>
		public void execSQL(string sql)
		{
			this.execSQL(sql, null);
		}

		/// <summary>
		/// Execute a SQL command (insert, update or delete) in database with BIND parameters.
		/// </summary>
		/// <code>
		///	   DBDataSet db = new DBDataSet("bdconection", context);
		///    DbParameters parameters = new DbParameters();
		///    param.Add("id", System.Data.DbType.Int32, 1500);
		///    param.Add("ds", System.Data.DbType.String, "SomeString");
		/// 
		///	   db.execSQL("insert into tabela (id_field, ds_field) values ([[id]], [[ds]])", parameters);
		/// </code>
		/// <remarks>
		/// Parameters must be declared in sequence order for using...
		/// </remarks>
		/// <param name="sql"></param>
		public void execSQL(string sql, DbParameters parameters)
		{
			IDbConnection db = this.getConnection(this.transaction != null);
			IDbCommand command = this.getCommand(db, sql, parameters);
			try
			{
				//	this._context.Debug(sql);
				command.ExecuteNonQuery();
			}
			finally
			{
				if (this.transaction == null)
				{
					db.Close();
					db = null;
				}
			}
		}

		protected void assignParameters(IDbCommand command, DbParameters parameters)
		{
			if (parameters != null)
			{
				for (int j = 0; j < parameters.Count; j++)
				{
					IDbDataParameter param = command.CreateParameter();
					//param.Direction = System.Data.ParameterDirection.Input;
					param.Value = parameters[j].Value;
					param.ParameterName = parameters[j].Name;
					// Add HERE specific conditions for USE DataType and Size.
					//------------------------------------------------
					//param.DbType = parameters[j].DataType;
					//param.Size = parameters[j].Size;
					if (param.Value == null)
					{
						param.Value = DBNull.Value;
					}
					command.Parameters.Add(param);
				}
			}
		}

		/// <summary>
		/// Get the connection Object. 
		/// .NET has a strange behavior it doesnt permit Have TWO DataReaders opened at the Same Connection.
		/// So, if the user wants a DataReader, XMLNuke will give a new Connection.
		/// For Insert, Update and Delete the Same Connection will be used.
		/// Execption: If a transaction Was started the system will use a same transaction. So You need open only one DataReader;
		/// </summary>
		/// <param name="newConnection">True return a new connection. False use the same share connection objet. </param>
		/// <returns></returns>
		protected DbConnection getConnection(bool hasTransaction)
		{
			//
			// TRY Standard drivers
			//
			DbConnection connObject;
			if (hasTransaction && (this.transaction != null) && (this._connection != null))
			{
				return this._connection;
			}

			connObject = XmlnukeProviderFactory.DbConnection(this._dbtype, this.connectionString);

			if (hasTransaction)
			{
				this._connection = connObject;
			}
			return connObject;
		}

		public DbDataAdapter getDataAdpater(string tableName)
		{
			return this.getDataAdpater("select * from " + tableName, null);
		}

		public DbDataAdapter getDataAdpater(string table, DbParameters param)
		{
			DbConnection conn = this.getConnection(false);
			DbDataAdapter adapter = XmlnukeProviderFactory.DbDataAdapter(this._dbtype, table, conn);
			return adapter;
		}

		public void StartTransaction(System.Data.IsolationLevel il)
		{
			IDbConnection dbo = this.getConnection(true);
			if (dbo.State != System.Data.ConnectionState.Open)
			{
				dbo.Open();
			}
			if (this.transaction == null)
			{
				this.transaction = (DbTransaction)dbo.BeginTransaction(il);
			}
			else
			{
				throw new Exception("Start Transaction Failed: Transaction already started.");
			}
		}

		public void StartTransaction()
		{
			this.StartTransaction(IsolationLevel.ReadCommitted);
		}

		public void CommitTransaction()
		{
			if (this.transaction == null)
			{
				throw new Exception("Commit Failed: Transaction not initialized.");
			}
			else
			{
				this.transaction.Commit();
				this.transaction = null;
				this._connection.Close();
				this._connection = null;
			}
		}

		public void RollBackTransaction()
		{
			if (this.transaction == null)
			{
				throw new Exception("Rollback Failed: Transaction not initialized.");
			}
			else
			{
				this.transaction.Rollback();
				this.transaction = null;
				this._connection.Close();
				this._connection = null;
			}
		}

		public bool TestConnection()
		{
			IDbConnection db = this.getConnection(true);
			db.Open();
			db.Close();
			return true;
		}
	}

	public class DbParameter
	{
		public DbParameter(string inName, int inSize, System.Data.DbType inDataType, object inValue)
		{
			if (!System.Text.RegularExpressions.Regex.IsMatch(inName, "^[\\w\\d_]+$")) throw new Exception("Parameter '" + inName + "' must have only letters and numbers.");
			this.DataType = inDataType;
			this.Name = inName;
			this.Value = inValue;
			this.Size = inSize;
		}

		public DbParameter(string inName, System.Data.DbType inDataType, object inValue)
		{
			if (!System.Text.RegularExpressions.Regex.IsMatch(inName, "^[\\w\\d_]+$")) throw new Exception("Parameter '" + inName + "' must have only letters and numbers.");
			this.DataType = inDataType;
			this.Name = inName;
			this.Value = inValue;
			this.Size = 0;
		}

		public DbParameter(string inName, object inValue)
		{
			if (!System.Text.RegularExpressions.Regex.IsMatch(inName, "^[\\w\\d_]+$")) throw new Exception("Parameter '" + inName + "' must have only letters and numbers.");
			this.DataType = System.Data.DbType.Object;
			this.Name = inName;
			this.Value = inValue;
			this.Size = 0;
		}

		public DbParameter()
		{
			this.DataType = System.Data.DbType.Object;
			this.Name = "noname";
			this.Value = "";
			this.Size = 0;
		}

		protected string _Name;
		public string Name
		{
			get { return this._Name; }
			set { this._Name = value; }
		}

		protected int _Size;
		public int Size
		{
			get { return this._Size; }
			set { this._Size = value; }
		}

		protected System.Data.DbType _DataType;
		public System.Data.DbType DataType
		{
			get { return this._DataType; }
			set { this._DataType = value; }
		}

		protected object _Value;
		public object Value
		{
			get { return this._Value; }
			set { this._Value = value; }
		}
	}


	public class DbParameters : CollectionBase
	{
		public int Add(string name, System.Data.DbType dbtype, object value)
		{
			return base.List.Add(new DbParameter(name, dbtype, value));
		}

		public int Add(string name, object value)
		{
			return base.List.Add(new DbParameter(name, value));
		}

		public int Add(DbParameter param)
		{
			return base.List.Add(param);
		}

		public bool Contains(DbParameter param)
		{
			return base.List.Contains(param);
		}

		public void Insert(int index, DbParameter param)
		{
			base.List.Insert(index, param);
		}

		public DbParameter this[int index]
		{
			get
			{
				object o = base.List[index];
				if (o == null)
				{
					throw new Exception("Cannot retrieve DbParameter index " + index.ToString());
				}
				else
				{
					return (DbParameter)o;
				}
			}
		}

		public void Remove(DbParameter param)
		{
			base.List.Remove(param);
		}

	}

	/// <summary>
	/// Class to create and manipulate Several Data Types
	/// </summary>
	public class XmlnukeProviderFactory
	{

		/// <summary>
		/// Get the Connection object based on provider name
		/// </summary>
		/// <param name="providerName">Alias to a provider name or the name of assembly</param>
		/// <param name="objConnection">Null (if alias is passed) or the string it represents the connection object</param>
		/// <param name="connectionString">Connection string</param>
		/// <returns>Instance of IDbConnection</returns>
		public static DbConnection DbConnection(string providerName, string connectionString)		
		{
			DbProviderFactory factory = DbProviderFactories.GetFactory(providerName);
			DbConnection result = factory.CreateConnection();
			result.ConnectionString = connectionString;
			return result;
		}

		/// <summary>
		/// Get a instance of IDbDataAdapter object based on provider name
		/// </summary>
		/// <param name="providerName">Alias to a provider name or the name of assembly</param>
		/// <param name="command">Select command</param>
		/// <returns>Instance of IDbDataAdapter</returns>
		public static DbDataAdapter DbDataAdapter(string providerName, string table, DbConnection db)
		{
			DbProviderFactory factory = DbProviderFactories.GetFactory(providerName);
			DbDataAdapter adapter = factory.CreateDataAdapter();
			adapter.SelectCommand = factory.CreateCommand();
			adapter.SelectCommand.CommandText = "select * from " + table;
			adapter.SelectCommand.Connection = db;	
			
			DbCommandBuilder builder = factory.CreateCommandBuilder();
			builder.DataAdapter = adapter;
			adapter.InsertCommand = builder.GetInsertCommand();
			adapter.DeleteCommand = builder.GetDeleteCommand();
			adapter.UpdateCommand = builder.GetUpdateCommand();

			return adapter;
		}

		/// <summary>
		/// Each provider have your own model for pass parameter. This method define how each provider name define the parameters
		/// </summary>
		/// <param name="providerName">Alias to a provider name or the name of assembly</param>
		/// <returns></returns>
		public static string GetParamModel(string providerName)
		{
			//TODO: TEM QUE VER UMA FORMA DE RETIRAR ELE...   ParameterMarkerPattern
			//TODO: VEJA NO SQLITE: @[\p{Lo}\p{Lu}\p{Ll}\p{Lm}_@#][\p{Lo}\p{Lu}\p{Ll}\p{Lm}\p{Nd}\uff3f_@#\$]*(?=\s+|$)
			//TODO: VEJA NO MYSQL: String.Format("({0}[A-Za-z0-9_$#]*)", connection.Settings.UseOldSyntax ? "@" : "?"); ==> (?[A-Za-z0-9_$#]*)
			/**
			    DbProviderFactory factory = DbProviderFactories.GetFactory("Mono.Data.Sqlite");
				DbConnection source = factory.CreateConnection();
				source.ConnectionString = "Data Source=/tmp/x.sqlite";
				source.Open();
				DataTable tbl = source.GetSchema(DbMetaDataCollectionNames.DataSourceInformation);
				com.xmlnuke.util.Debug.Print(tbl);
			 */
			
			// Get the Default Provider Name
			if (providerName.ToLower().IndexOf("odbc") >= 0)
			{
				return "?";
			}
			else if (providerName.ToLower().IndexOf("oledb") >= 0)
			{
				return "?";
			}
			else if (providerName.ToLower().IndexOf("sqlclient") >= 0)
			{
				return "@_";
			}
			else if (providerName.ToLower().IndexOf("mysql") >= 0)
			{
				return "?_";
			}
			else if (providerName.ToLower().IndexOf("oracle") >= 0)
			{
				return ":_";
			}
			else if (providerName.ToLower().IndexOf("npgsql") >= 0)
			{
				return ":_";
			}
			else if (providerName.ToLower().IndexOf("firebird") >= 0)
			{
				return "@_";
			}
			else if (providerName.ToLower().IndexOf("sqlite") >= 0)
			{
				return "@_";
			}
			else
			{
				return "@_";
			}
		}

		/// <summary>
		/// Transform generic parameters [[PARAM]] in a parameter recognized by the provider name based on current DbParameter array.
		/// </summary>
		/// <param name="providerName">Alias to a provider name or the name of assembly</param>
		/// <param name="SQL"></param>
		/// <param name="param"></param>
		/// <returns></returns>
		public static string ParseSQL(string providerName, string SQL, DbParameters param, out DbParameters newParam)
		{
			if (param == null || param.Count == 0)
			{
				newParam = null;
				return SQL;
			}

			newParam = new DbParameters();

			string pattern = XmlnukeProviderFactory.GetParamModel(providerName);
			for (int j = 0; j < param.Count; j++)
			{
				DbParameter paramItem = param[j];
				string replace = pattern.Replace("_", paramItem.Name);
				SQL = SQL.Replace("[[" + paramItem.Name + "]]", replace);
				if (pattern.IndexOf("_") >= 0)
					paramItem.Name = replace;
				newParam.Add(paramItem);
			}
			return SQL;
		}

		/// <summary>
		/// Return a SQL withou use of parameters. Avoid to use this option.
		/// </summary>
		/// <param name="SQL"></param>
		/// <param name="param"></param>
		/// <returns></returns>
		public static string ParseSQLWithoutParam(string SQL, DbParameters param)
		{
			if (param == null || param.Count == 0)
				return SQL;

			for (int j = 0; j < param.Count; j++)
			{
				string valor = param[j].Value.ToString();
				if (param[j].DataType == DbType.String)
					valor = "'" + valor.Replace("'", "''") + "'";
				SQL = SQL.Replace("[[" + param[j].Name + "]]", valor);
			}
			return SQL;
		}

	}
}