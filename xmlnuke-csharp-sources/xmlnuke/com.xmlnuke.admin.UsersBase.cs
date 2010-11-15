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
using com.xmlnuke.exceptions;

namespace com.xmlnuke.admin
{
	public enum UserProperty
	{
		Site,
		Role
	}

	//Structure of the UserTable
	public struct UserTable
	{
		public string Table;
		public string Id;
		public string Name;
		public string Email;
		public string Username;
		public string Password;
		public string Created;
		public string Admin;
	}

	//Structure of the Custom Table
	public struct CustomTable
	{
		public string Table;
		public string Id;
		public string Name;
		public string Value;
	}

	//Structure of the Roles Table
	public struct RolesTable
	{
		public string Table;
		public string Site;
		public string Role;
	}


    public abstract class UsersBase
    {

        protected UserTable _UserTable;

        protected CustomTable _CustomTable;

        protected RolesTable _RolesTable;

        protected engine.Context _context = null;

        /// <summary>
        /// Get the SHA1 string from user password
        /// </summary>
        /// <param name="password">Plain password</param>
        /// <returns>SHA1 encripted password</returns>
        public string getSHAPassword(string password)
        {
            return UsersBase.EncodeSHA(password);
        }

        public static string EncodeSHA(string text)
        {
            byte[] dataArray = System.Text.Encoding.ASCII.GetBytes(text);
            System.Security.Cryptography.HashAlgorithm sha = new System.Security.Cryptography.SHA1CryptoServiceProvider();
            byte[] result = sha.ComputeHash(dataArray);
            string strTmp = "";
            for (int i = 0; i < result.Length; i++)
            {
                strTmp += String.Format("{0,2:X2}", result[i]);
            }
            return strTmp;
        }


        public static string getPropertyNodeName(UserProperty userProp)
        {
            string result = "";

            switch (userProp)
            {
                case UserProperty.Site:
                    {
                        result = "editsite";
                        break;
                    }
                case UserProperty.Role:
                    {
                        result = "roles";
                        break;
                    }
            }

            return result;
        }

        virtual public SingleRow getUser(IteratorFilter filter)
        {
            return null;
        }

        /// <summary>
        /// Get the user based on his email.
        /// </summary>
        /// <param name="email">Email to find</param>
        /// <returns>SingleRow if user was found; null, otherwise</returns>
        public anydataset.SingleRow getUserEMail(string email)
        {
            anydataset.IteratorFilter filter;
            filter = new anydataset.IteratorFilter();
            filter.addRelation(this._UserTable.Email, anydataset.Relation.Equal, email.ToLower());
            return this.getUser(filter);
        }

        /// <summary>
        /// Get the user based on his login
        /// </summary>
        /// <param name="username">Login name</param>
        /// <returns>SingleRow if user was found; null, otherwise</returns>
        public anydataset.SingleRow getUserName(string username)
        {
            anydataset.IteratorFilter filter;
            filter = new anydataset.IteratorFilter();
            filter.addRelation(this._UserTable.Username, anydataset.Relation.Equal, username.ToLower());
            return this.getUser(filter);
        }

        public anydataset.SingleRow getUserId(string id)
        {
            anydataset.IteratorFilter filter;
            filter = new anydataset.IteratorFilter();
            filter.addRelation(this._UserTable.Id, anydataset.Relation.Equal, id.ToLower());
            return this.getUser(filter);
        }

        /// <summary>
        /// Validate if the user and password exists in the file
        /// </summary>
        /// <param name="userName">Login name</param>
        /// <param name="password">Plain text password</param>
        /// <returns>SingleRow if user exists; null, otherwise</returns>
        public anydataset.SingleRow validateUserName(string userName, string password)
        {
            anydataset.IteratorFilter filter;
            filter = new anydataset.IteratorFilter();
            filter.addRelation(this._UserTable.Username, anydataset.Relation.Equal, userName.ToLower());
            filter.addRelation(this._UserTable.Password, anydataset.Relation.Equal, this.getSHAPassword(password));
            return getUser(filter);
        }

        /// <summary>
        /// Check if the user have rights to edit specific site.
        /// </summary>
        /// <param name="userName">Login name</param>
        /// <param name="siteName">Site to check</param>
        /// <returns>True if have rights; false, otherwise</returns>
        public bool checkUserProperty(string userName, string propValue, UserProperty userProp)
        {
            SingleRow user = this.getUserId(userName);
            if (user != null)
            {
                if (user.getField(this._UserTable.Admin) == "yes")
                {
                    return true;
                }
                else
                {
                    string[] values = user.getFieldArray(UsersBase.getPropertyNodeName(userProp));
                    foreach (string value in values)
                    {
                        if (propValue == value)
                        {
                            return true;
                        }
                    }
                    return false;
                }
            }
            else
            {
                return false;
            }
        }

        /// <summary>
        /// Return all sites from a specific user
        /// </summary>
        /// <param name="userName">Login name</param>
        /// <returns>String vector with all sites</returns>
        public string[] returnUserProperty(string userId, UserProperty userProp)
        {
            anydataset.SingleRow user = getUserId(userId);
            if (user != null)
            {
                string[] values = user.getFieldArray(getPropertyNodeName(userProp));
                if (user.getField(this._UserTable.Admin) == "yes")
                {
                    if (userProp == UserProperty.Site)
                    {
                        string[] result = _context.ExistingSites();
                        for (int i = 0; i < result.Length; i++)
                        {
                            result[i] = com.xmlnuke.util.FileUtil.ExtractFileName(result[i]);
                        }
                        return result;
                    }
                    else
                    {
                        return new string[] { "admin" };
                    }
                }
                else
                {
                    if (values.Length == 0)
                    {
                        return null;
                    }
                    else
                    {
                        return values;
                    }
                }

            }
            else
            {
                return null;
            }
        }

        public UserTable getUserTable()
        {
            return this._UserTable;
        }

        public CustomTable getCustomTable()
        {
            return this._CustomTable;
        }

        public RolesTable getRolesTable()
        {
            return this._RolesTable;
        }

        virtual protected void configTableNames()
        {
            this._UserTable = new UserTable();
            this._UserTable.Table = "user";
            this._UserTable.Id = "userid";
            this._UserTable.Name = "name";
            this._UserTable.Email = "email";
            this._UserTable.Username = "username";
            this._UserTable.Password = "password";
            this._UserTable.Created = "created";
            this._UserTable.Admin = "admin";

            this._CustomTable = new CustomTable();
            this._CustomTable.Table = "custom";
            this._CustomTable.Id = "customid";
            this._CustomTable.Name = "name";
            this._CustomTable.Value = "value";
            // Table "CUSTOM" must have [this._UserTable.Id = "userid"].

            this._RolesTable = new RolesTable();
            this._RolesTable.Table = "roles";
            this._RolesTable.Site = "site";
            this._RolesTable.Role = "role";
        }

        public bool userIsAdmin()
        {
            return this.userIsAdmin(null);
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="userId"></param>
        /// <returns></returns>
        public bool userIsAdmin(string userId)
        {
            if (String.IsNullOrEmpty(userId))
            {
                userId = this._context.authenticatedUserId();
                if (String.IsNullOrEmpty(userId))
                    throw new NotAuthenticatedException();
            }

            SingleRow user = this.getUserId(userId);
            if (user != null)
                return (user.getField(this._UserTable.Admin) == "yes");
            else
                throw new Exception("Cannot find the user");
        }

        public bool userHasRole(string role)
        {
            return this.userHasRole(role, null);
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="role"></param>
        /// <param name="userId"></param>
        /// <returns></returns>
        public bool userHasRole(string role, string userId)
        {
            if (String.IsNullOrEmpty(userId))
            {
                userId = this._context.authenticatedUserId();
                if (String.IsNullOrEmpty(userId))
                    throw new NotAuthenticatedException();
            }

            return this.checkUserProperty(userId, role, UserProperty.Role);
        }

    }

}