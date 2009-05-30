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
using com.xmlnuke.processor;

namespace com.xmlnuke.admin
{

	/// <summary>
	/// UserAnyDataSet is a class to Store and Retrive USERS from an AnyDataSet structure. 
	/// Note that UsersAnyDataSet doesn't inherits from AnyDataSet, because some funcionalities
	/// from AnyDataSet didn't used in this class.
	/// </summary>
	public class UsersAnyDataSet : UsersBase, IUsersBase
	{
		/// <summary>Internal AnyDataSet structure to store the Users</summary>
		private AnyDataSet _anyDataSet;

		/// <summary>Internal Users file name</summary>
		private processor.AnydatasetSetupFilenameProcessor usersFile;

		/// <summary>
		/// AnyDataSet constructor
		/// </summary>
		public UsersAnyDataSet(engine.Context context)
		{
			_context = context;
			usersFile = new processor.AnydatasetSetupFilenameProcessor("users", context);
			_anyDataSet = new AnyDataSet(usersFile);
			this.configTableNames();
			this._UserTable.Id = this._UserTable.Username;
		}

		/// <summary>
		/// Save the current UsersAnyDataSet
		/// </summary>
		public void Save()
		{
			_anyDataSet.Save(usersFile);
		}

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
			_anyDataSet.appendRow();
			_anyDataSet.addField(this._UserTable.Name, name);
			_anyDataSet.addField(this._UserTable.Username, userName.ToLower());
			_anyDataSet.addField(this._UserTable.Email, email.ToLower());
			_anyDataSet.addField(this._UserTable.Password, getSHAPassword(password));
			_anyDataSet.addField(this._UserTable.Admin, "");
			_anyDataSet.addField(this._UserTable.Created, DateTime.Now);
			return true;
		}

		/// <summary>
		/// Get the user based on a filter
		/// </summary>
		/// <param name="filter">Filter to find user</param>
		/// <returns>SingleRow if user was found; null, otherwise</returns>
		override public SingleRow getUser(IteratorFilter filter)
		{
			Iterator it = _anyDataSet.getIterator(filter);
			if (!it.hasNext())
			{
				return null;
			}
			else
			{
				return it.moveNext();
			}
		}

		/// <summary>
		/// Remove the user based on his login.
		/// </summary>
		/// <param name="username">Login name</param>
		/// <returns></returns>
		public bool removeUserName(string username)
		{
			SingleRow user = getUserName(username);
			if (user != null)
			{
				_anyDataSet.removeRow(user.getDomObject());
				return true;
			}
			else
			{
				return false;
			}
		}

		/// <summary>
		/// Get an Iterator with all users
		/// </summary>
		/// <returns>Iterator</returns>
		public IIterator getIterator()
		{
			return _anyDataSet.getIterator();
		}

		/// <summary>
		/// Get an Iterator based on a filter
		/// </summary>
		/// <param name="filter">filter</param>
		/// <returns>Iterator</returns>
		public IIterator getIterator(IteratorFilter filter)
		{
			return _anyDataSet.getIterator(filter);
		}

		public System.Collections.ArrayList getArray(IteratorFilter filter, string fieldName)
		{
			return _anyDataSet.getArray(filter, fieldName);
		}

		/// <summary>
		/// Add a specific site to user
		/// </summary>
		/// <param name="userName">Login name</param>
		/// <param name="siteName">Site to add</param>
		/// <returns>True or false</returns>
		public bool addPropertyValueToUser(string userName, string propValue, UserProperty userProp)
		{
			bool ret = false;
			if (!this.checkUserProperty(userName, propValue, userProp))
			{
				ret = this.addPropertyValueToUser(userName, propValue, getPropertyNodeName(userProp));
			}
			return ret;
		}

		public bool addPropertyValueToUser(string userName, string propValue, string userProp)
		{
			SingleRow user = getUserName(userName);
			if (user != null)
			{
				user.AddField(userProp, propValue);
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
		/// <returns>True or false</returns>
		public bool removePropertyValueFromUser(string userName, string propValue, UserProperty userProp)
		{
			return removePropertyValueFromUser(userName, propValue, getPropertyNodeName(userProp));
		}

		public bool removePropertyValueFromUser(string userName, string propValue, string userProp)
		{
			SingleRow user = getUserName(userName);
			if (user != null)
			{
				XmlNodeList nodes = user.getFieldNodes(userProp);
				foreach (XmlNode node in nodes)
				{
					if ((propValue == null) || (propValue == node.InnerXml))
					{
						user.removeField(node);
					}
				}
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
		public void removePropertyValueFromAllUsers(string propValue, UserProperty userProp)
		{
			IIterator it = this.getIterator();
			while (it.hasNext())
			{
				SingleRow user = it.moveNext();
				this.removePropertyValueFromUser(user.getField(this._UserTable.Username), propValue, userProp);
			}
		}

		/// <summary>
		/// 
		/// </summary>
		/// <returns></returns>
		protected AnyDataSet getRoleAnydataSet()
		{
			AnydatasetSetupFilenameProcessor fileRole = new AnydatasetSetupFilenameProcessor(this._RolesTable.Table, this._context);
			AnyDataSet roleDataSet = new AnyDataSet(fileRole);
			return roleDataSet;
		}

		/// <summary>
		/// Get all Roles
		/// </summary>
		/// <param name="site"></param>
		/// <param name="role"></param>
		/// <returns></returns>
		public IIterator getRolesIterator(string site, string role)
		{
			IteratorFilter itf = new IteratorFilter();
			if (role != "")
			{
				itf.addRelation(this._RolesTable.Role, Relation.Equal, role);
			}
			itf.startGroup();
			itf.addRelation(this._RolesTable.Site, Relation.Equal, site);
			itf.addRelationOr(this._RolesTable.Site, Relation.Equal, "_all");
			itf.endGroup();

			AnyDataSet roleDataSet = this.getRoleAnydataSet();
			return roleDataSet.getIterator(itf);
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
			AnyDataSet dataset = this.getRoleAnydataSet();
			IteratorFilter dataFilter = new IteratorFilter();
			dataFilter.addRelation(this._RolesTable.Site, Relation.Equal, site);
			IIterator iterator = dataset.getIterator(dataFilter);
			if (!iterator.hasNext())
			{
				dataset.appendRow();
				dataset.addField(this._RolesTable.Site, site);
				dataset.addField(this._RolesTable.Role, role);
			}
			else
			{
				dataFilter.addRelation(this._RolesTable.Role, Relation.Equal, role);
				IIterator iteratorCheckDupRole = dataset.getIterator(dataFilter);
				if (!iteratorCheckDupRole.hasNext())
				{
					SingleRow sr = iterator.moveNext();
					sr.AddField(this._RolesTable.Role, role);
				}
				else
				{
					throw new Exception("Role exists");
				}
			}
			dataset.Save();
		}

		/// <summary>
		/// Edit or remove a public role into a site
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

			AnyDataSet roleDataSet = this.getRoleAnydataSet();
			IteratorFilter dataFilter = new IteratorFilter();
			dataFilter.addRelation(this._RolesTable.Site, Relation.Equal, site);
			dataFilter.addRelation(this._RolesTable.Role, Relation.Equal, role);
			IIterator it = roleDataSet.getIterator(dataFilter);
			if (it.hasNext())
			{
				SingleRow sr = it.moveNext();
				sr.removeField(this.getRoleNode(sr, role));
			}
			roleDataSet.Save();
		}
		public void editRolePublic(string site, string role)
		{
			this.editRolePublic(site, role, null);
		}

		/// <summary>
		/// Find a role in site
		/// </summary>
		/// <param name="sr"></param>
		/// <param name="role"></param>
		/// <returns></returns>
		protected XmlNode getRoleNode(SingleRow sr, string role)
		{
			XmlNodeList list = sr.getFieldNodes(this._RolesTable.Role);
			int count = 0;
			while (list.Count > count)
			{
				XmlNode node = list[count];
				if (node.Value == role)
				{
					return node;
				}
				count++;
			}

			return null;
		}
	}

}