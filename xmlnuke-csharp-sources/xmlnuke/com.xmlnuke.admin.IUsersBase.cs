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
using com.xmlnuke.anydataset;

namespace com.xmlnuke.admin
{

	/// <summary>
	/// UserAnyDataSet is a class to Store and Retrive USERS from an AnyDataSet structure. 
	/// Note that UsersAnyDataSet doesn't inherits from AnyDataSet, because some funcionalities
	/// from AnyDataSet didn't used in this class.
	/// </summary>
	public interface IUsersBase
	{
		/// <summary>
		/// Save the current UsersAnyDataSet
		/// </summary>
		void Save();

		bool addUser(string name, string userName, string email, string password);

		/// <summary>
		/// Get the user based on a filter
		/// </summary>
		/// <param name="filter">Filter to find user</param>
		/// <returns>SingleRow if user was found; null, otherwise</returns>
		SingleRow getUser(IteratorFilter filter);

		/// <summary>
		/// Get the user based on his email.
		/// </summary>
		/// <param name="email">Email to find</param>
		/// <returns>SingleRow if user was found; null, otherwise</returns>
		SingleRow getUserEMail(string email);

		/// <summary>
		/// Get the user based on his login
		/// </summary>
		/// <param name="username">Login name</param>
		/// <returns>SingleRow if user was found; null, otherwise</returns>
		SingleRow getUserName(string username);

		/// <summary>
		/// Get the user based on his login
		/// </summary>
		/// <param name="username">Login name</param>
		/// <returns>SingleRow if user was found; null, otherwise</returns>
		SingleRow getUserId(string username);

		/// <summary>
		/// Remove the user based on his login.
		/// </summary>
		/// <param name="username">Login name</param>
		/// <returns></returns>
		bool removeUserName(string username);

		/// <summary>
		/// Get an Iterator with all users
		/// </summary>
		/// <returns>Iterator</returns>
		IIterator getIterator();

		/// <summary>
		/// Get an Iterator based on a filter
		/// </summary>
		/// <param name="filter">filter</param>
		/// <returns>Iterator</returns>
		IIterator getIterator(IteratorFilter filter);

		/// <summary>
		/// Get the SHA1 string from user password
		/// </summary>
		/// <param name="password">Plain password</param>
		/// <returns>SHA1 encripted password</returns>
		string getSHAPassword(string password);

		/// <summary>
		/// Validate if the user and password exists in the file
		/// </summary>
		/// <param name="userName">Login name</param>
		/// <param name="password">Plain text password</param>
		/// <returns>SingleRow if user exists; null, otherwise</returns>
		SingleRow validateUserName(string userName, string password);

		/// <summary>
		/// Check if the user have a specific property
		/// </summary>
		/// <param name="userName">Login name</param>
		/// <param name="propValue">Value to find</param>
		/// <param name="userProp">Property to check</param>
		/// <returns>True if have rights; false, otherwise</returns>
		bool checkUserProperty(string userName, string propValue, UserProperty userProp);

		/// <summary>
		/// Return all sites from a specific user
		/// </summary>
		/// <param name="userName">Login name</param>
		/// <returns>String vector with all sites</returns>
		string[] returnUserProperty(string userName, UserProperty userProp);

		/// <summary>
		/// Add a property to an user.
		/// </summary>
		/// <param name="userName">Login name</param>
		/// <param name="propValue">Value to add</param>
		/// <param name="userProp">Property to add</param>
		/// <returns>True if added; false, otherwise</returns>
		bool addPropertyValueToUser(string userName, string propValue, UserProperty userProp);

		/// <summary>
		/// Add a custom property to an user.
		/// </summary>
		/// <param name="userName">Login name</param>
		/// <param name="propValue">Value to add</param>
		/// <param name="userProp">Property to add</param>
		/// <returns>True if added; false, otherwise</returns>
		bool addPropertyValueToUser(string userName, string propValue, string userProp);

		/// <summary>
		/// Remove a property from an user.
		/// </summary>
		/// <param name="userName">Login name</param>
		/// <param name="propValue">Value to remove</param>
		/// <param name="userProp">Property to remove</param>
		/// <returns>True if added; false, otherwise</returns>
		bool removePropertyValueFromUser(string userName, string propValue, UserProperty userProp);

		bool removePropertyValueFromUser(string userName, string propValue, string userProp);

		/// <summary>
		/// Remove a specific site from all users
		/// </summary>
		/// <param name="siteName">Site name</param>
		void removePropertyValueFromAllUsers(string propValue, UserProperty userProp);

		UserTable getUserTable();

		CustomTable getCustomTable();

		RolesTable getRolesTable();


		/// <summary>
		/// Get all Roles
		/// </summary>
		/// <param name="site"></param>
		/// <param name="role"></param>
		/// <returns></returns>
		IIterator getRolesIterator(string site, string role);
		IIterator getRolesIterator(string site);

		/// <summary>
		/// Add a public role into a site
		/// </summary>
		/// <param name="site"></param>
		/// <param name="role"></param>
		void addRolePublic(string site, string role);

		/// <summary>
		/// Edit or remove a public role into a site. 
		/// </summary>
		/// <param name="site"></param>
		/// <param name="role"></param>
		/// <param name="newValue"></param>
		void editRolePublic(string site, string role, string newValue);
		void editRolePublic(string site, string role);

	}

}