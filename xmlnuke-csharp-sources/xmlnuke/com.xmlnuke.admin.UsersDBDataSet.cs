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
using com.xmlnuke;
using com.xmlnuke.anydataset;
using com.xmlnuke.engine;

namespace com.xmlnuke.admin
{
	/// <summary>
	/// UserAnyDataSet is a class to Store and Retrive USERS from an AnyDataSet structure.
	/// Note that UsersAnyDataSet doesn't inherits from AnyDataSet, because some funcionalities
	/// from AnyDataSet didn't used in this class.
	/// </summary>
	public class UsersDBDataSet : UsersBase, IUsersBase
	{
		private DBDataSet _DB;

		private System.Collections.Hashtable _cacheUserWork;
		private System.Collections.Hashtable _cacheUserOriginal;

		/// <summary>
		/// DBDataSet constructor
		/// </summary>
		public UsersDBDataSet(Context context, string dataBase)
		{
			this._context = context;
			this._DB = new DBDataSet(dataBase, context);
			this.configTableNames();
			this._cacheUserWork = new System.Collections.Hashtable();
			this._cacheUserOriginal = new System.Collections.Hashtable();
		}

		/// <summary>
		/// Save the current UsersAnyDataSet
		/// </summary>
		public void Save()
		{
			foreach (string key in this._cacheUserOriginal.Keys)
			{
				SingleRow srOri = (SingleRow)this._cacheUserOriginal[key];
				SingleRow srMod = (SingleRow)this._cacheUserWork[key];

				bool changed = false;
				foreach (string fieldname in srOri.getFieldNames())
				{
					if (srOri.getField(fieldname) != srMod.getField(fieldname))
					{
						changed = true;
						break;
					}
				}

				if (changed)
				{
					string sql = "UPDATE " + this._UserTable.Table;
					sql += " SET " + this._UserTable.Name + " = [[" + this._UserTable.Name + "]] ";
					sql += ", " + this._UserTable.Email + " = [[" + this._UserTable.Email + "]] ";
					sql += ", " + this._UserTable.Username + " = [[" + this._UserTable.Username + "]] ";
					sql += ", " + this._UserTable.Password + " = [[" + this._UserTable.Password + "]] ";
					//sql += ", " + this._UserTable.Created + " = [[" + srMod.getField(this._UserTable.Created) + "]] ";
					sql += ", " + this._UserTable.Admin + " = [[" + this._UserTable.Admin + "]] ";
					sql += " WHERE " + this._UserTable.Id + " = [[" + this._UserTable.Id + "]]";

					DbParameters param = new DbParameters();
					param.Add(this._UserTable.Name, System.Data.DbType.String, srMod.getField(this._UserTable.Name));
					param.Add(this._UserTable.Email, System.Data.DbType.String, srMod.getField(this._UserTable.Email));
					param.Add(this._UserTable.Username, System.Data.DbType.String, srMod.getField(this._UserTable.Username));
					param.Add(this._UserTable.Password, System.Data.DbType.String, srMod.getField(this._UserTable.Password));
					//param.Add(this._UserTable.Created, System.Data.DbType.String, srMod.getField(this._UserTable.Created));
					param.Add(this._UserTable.Admin, System.Data.DbType.String, srMod.getField(this._UserTable.Admin));
					param.Add(this._UserTable.Id, System.Data.DbType.Int32, srMod.getField(this._UserTable.Id));

					this._DB.execSQL(sql, param);
				}
			}

			this._cacheUserOriginal = new System.Collections.Hashtable();
			this._cacheUserWork = new System.Collections.Hashtable();
		}
		//Parameters : strings / Return: Bool
		public bool addUser(string name, string userName, string email, string password)
		{
			if (this.getUserEMail(email) != null)
			{
				return false;
			}
			if (this.getUserName(userName) != null)
			{
				return false;
			}
			string sql = " INSERT INTO " + this._UserTable.Table + " (" + this._UserTable.Name + ", " + this._UserTable.Email + ", " + this._UserTable.Username + ", " + this._UserTable.Password + ", " + this._UserTable.Created + " ) ";
			sql += " VALUES ([[" + this._UserTable.Name + "]], [[" + this._UserTable.Email + "]], [[" + this._UserTable.Username + "]], [[" + this._UserTable.Password + "]], [[" + this._UserTable.Created + "]] ) ";

			DbParameters param = new DbParameters();
			param.Add(this._UserTable.Name, System.Data.DbType.String, name);
			param.Add(this._UserTable.Email, System.Data.DbType.String, email.ToLower());
			param.Add(this._UserTable.Username, System.Data.DbType.String, userName.ToLower());
			param.Add(this._UserTable.Password, System.Data.DbType.String, this.getSHAPassword(password));
			param.Add(this._UserTable.Created, System.Data.DbType.DateTime, DateTime.Now);

			this._DB.execSQL(sql, param);

			return true;
		}

		/// <summary>
		/// Get an Iterator .
		/// </summary>
		/// <returns>Iterator</returns>anydataset.Iterator
		public IIterator getIterator()
		{
			return getIterator(new IteratorFilter());
		}

		/// <summary>
		/// Get an Iterator based on a filter
		/// </summary>
		/// <param name="filter">filter</param>anydataset.IteratorFilter
		/// <returns>Iterator</returns>anydataset.Iterator
		public IIterator getIterator(IteratorFilter filter)
		{
			DbParameters param = new DbParameters();
			string sql = filter.getSql(this._UserTable.Table, out param);
			sql += " order by " + this._UserTable.Name;
			return this._DB.getIterator(sql, param);
		}

		/// <summary>
		/// Get the user based on a filter
		/// </summary>
		/// <param name="filter">Filter to find user</param>
		/// <returns>SingleRow if user was found; null, otherwise</returns> anydataset.SingleRow
		override public SingleRow getUser(IteratorFilter filter)
		{
			IIterator it = this.getIterator(filter);
			if (it.hasNext())
			{
				// Get the Requested User
				SingleRow sr = it.moveNext();
				this.getCustomFields(sr);

				// Clone the User Properties
				AnyDataSet anyOri = new AnyDataSet();
				anyOri.appendRow();
				foreach (string fieldName in sr.getFieldNames())
				{
					anyOri.addField(fieldName, sr.getField(fieldName));
				}
				Iterator itOri = anyOri.getIterator();
				SingleRow srOri = itOri.moveNext();

				// Store and return to the user the proper single row.
				this._cacheUserOriginal[sr.getField(this._UserTable.Id)] = srOri;
				this._cacheUserWork[sr.getField(this._UserTable.Id)] = sr;
				return (SingleRow)this._cacheUserWork[sr.getField(this._UserTable.Id)];
			}
			else
			{
				return null;
			}
		}

		/// <summary>
		/// Remove the user based on his login.
		/// </summary>
		/// <param name="username">Login name</param>
		/// <returns>bool</returns>
		public bool removeUserName(string username)
		{
			DbParameters param = new DbParameters();
			param.Add(this._UserTable.Id, System.Data.DbType.Int32, username);

			this._DB.execSQL(" DELETE FROM " + this._CustomTable.Table + " WHERE " + this._UserTable.Id + " = [[" + this._UserTable.Id + "]]", param);
			this._DB.execSQL(" DELETE FROM " + this._UserTable.Table + " WHERE " + this._UserTable.Id + " = [[" + this._UserTable.Id + "]]", param);
			return true;
		}

		/// <summary>
		/// Add a specific site to user
		/// </summary>
		/// <param name="userName">Login name</param>
		/// <param name="siteName">Site to add</param>
		/// <returns>True or false</returns>bool
		/// Parameters: string userName, string propValue, UserProperty userProp
		public bool addPropertyValueToUser(string userName, string propValue, UserProperty userProp)
		{
			if (!checkUserProperty(userName, propValue, userProp))
			{
				return addPropertyValueToUser(userName, propValue, UsersBase.getPropertyNodeName(userProp));
			}
			else
			{
				return false;
			}
		}

		public bool addPropertyValueToUser(string userName, string propValue, string userProp)
		{
			SingleRow user = this.getUserId(userName);
			if (user != null)
			{
				string sql = " INSERT INTO " + this._CustomTable.Table +
					"( " + this._UserTable.Id + ", " + this._CustomTable.Name + ", " + this._CustomTable.Value + ") " +
					" VALUES ( [[" + this._UserTable.Id + "]], [[" + this._CustomTable.Name + "]], [[" + this._CustomTable.Value + "]] ) ";

				DbParameters param = new DbParameters();
				param.Add(this._UserTable.Id, System.Data.DbType.Int32, userName);
				param.Add(this._CustomTable.Name, System.Data.DbType.String, userProp);
				param.Add(this._CustomTable.Value, System.Data.DbType.String, propValue);

				this._DB.execSQL(sql, param);
				return true;
			}
			else
			{
				return false;
			}
		}

		/// <summary>
		/// Remove a specific site from user
		/// </summary>
		/// <param name="userName">Login name</param>
		/// <param name="siteName">Site name</param>
		/// <returns>True or false</returns>bool
		/// Parameters: string userName, string propValue, UserProperty userProp
		public bool removePropertyValueFromUser(string userName, string propValue, UserProperty userProp)
		{
			return removePropertyValueFromUser(userName, propValue, UsersBase.getPropertyNodeName(userProp));
		}

		public bool removePropertyValueFromUser(string userName, string propValue, string userProp)
		{
			SingleRow user = this.getUserId(userName);
			if (user != null)
			{
				DbParameters param = new DbParameters();
				param.Add(this._UserTable.Id, System.Data.DbType.Int32, userName);
				param.Add(this._CustomTable.Name, System.Data.DbType.String, userProp);

				string sql = " DELETE FROM " + this._CustomTable.Table;
				sql += " WHERE " + this._UserTable.Id + " = [[" + this._UserTable.Id + "]] AND " + this._CustomTable.Name + " = [[" + this._CustomTable.Name + "]] ";
				if (propValue != null)
				{
					sql += " AND " + this._CustomTable.Value + " = [[" + this._CustomTable.Value + "]] ";
					param.Add(this._CustomTable.Value, System.Data.DbType.String, propValue);
				}

				this._DB.execSQL(sql, param);
				return true;
			}
			else
			{
				return false;
			}
		}

		/// <summary>
		/// Remove a specific site from all users
		/// </summary>
		/// <param name="siteName">Site name</param>
		/// string propValue, UserProperty userProp
		public void removePropertyValueFromAllUsers(string propValue, UserProperty userProp)
		{
			DbParameters param = new DbParameters();
			param.Add(this._CustomTable.Name, System.Data.DbType.String, UsersBase.getPropertyNodeName(userProp));
			param.Add(this._CustomTable.Value, System.Data.DbType.String, propValue);

			this._DB.execSQL(" DELETE FROM " + this._CustomTable.Table +
				" WHERE " + this._CustomTable.Name + " = [[" + this._CustomTable.Name + "]]" +
				" AND " + this._CustomTable.Value + " = [[" + this._CustomTable.Value + "]]", param);
		}


		/// <summary>
		/// Return all custom?s fields from this user
		/// </summary>
		/// <param name="userName">User Name</param>	
		protected void getCustomFields(SingleRow userRow)
		{
			string userName = userRow.getField(this._UserTable.Id);
			string sql = "select * from " + this._CustomTable.Table;
			sql += " where " + this._UserTable.Id + " = [[id]]";

			DbParameters param = new DbParameters();
			param.Add("id", System.Data.DbType.Int32, userName);

			IIterator it = this._DB.getIterator(sql, param);
			while (it.hasNext())
			{
				SingleRow sr = it.moveNext();
				userRow.AddField(sr.getField(this._CustomTable.Name), sr.getField(this._CustomTable.Value));
			}
		}

		/// <summary>
		/// Get all Roles
		/// </summary>
		/// <param name="site"></param>
		/// <param name="role"></param>
		/// <returns></returns>
		public IIterator getRolesIterator(string site, string role)
		{
			string sql = "select * from " + this._RolesTable.Table +
				" where (" + this._RolesTable.Site + " = '" + site + "' or " + this._RolesTable.Site + " = '_all' ) ";

			if (role != "")
			{
				sql += " and " + this._RolesTable.Role + " = '" + role + "'";
			}

			return this._DB.getIterator(sql);
		}
		public IIterator getRolesIterator(string site)
		{
			return this.getRolesIterator(site, "");
		}

		/// <summary>
		/// Add a public role into a site
		/// </summary>
		/// <param name="site"></param>
		/// <param name="role"></param>
		public void addRolePublic(string site, string role)
		{
			IIterator it = this.getRolesIterator(site, role);
			if (it.hasNext())
			{
				throw new Exception("Role exists.");
			}

			string sql = "insert into " + this._RolesTable.Table + "( " + this._RolesTable.Site + ", " + this._RolesTable.Role + " ) " +
				" values ( [[site]], [[role]] )";

			DbParameters param = new DbParameters();
			param.Add("site", System.Data.DbType.String, site);
			param.Add("role", System.Data.DbType.String, role);

			this._DB.execSQL(sql, param);
		}

		/// <summary>
		/// Edit or delete a Role,
		/// </summary>
		/// <param name="site"></param>
		/// <param name="role"></param>
		/// <param name="newValue"></param>
		public void editRolePublic(string site, string role, string newValue)
		{
			if (newValue != null)
			{
				this.addRolePublic(site, newValue);
			}

			string sql = "DELETE FROM " + this._RolesTable.Table +
				" WHERE " + this._RolesTable.Site + " = [[site]] " +
				" AND " + this._RolesTable.Role + " = [[role]] ";

			DbParameters param = new DbParameters();
			param.Add("site", System.Data.DbType.String, site);
			param.Add("role", System.Data.DbType.String, role);

			this._DB.execSQL(sql, param);
		}
		public void editRolePublic(string site, string role)
		{
			this.editRolePublic(site, role, null);
		}

		protected override void configTableNames()
		{
			base.configTableNames();

			this._UserTable.Table = "xmlnuke_users";
			this._CustomTable.Table = "xmlnuke_custom";
			this._RolesTable.Table = "xmlnuke_roles";
		}
	}
}