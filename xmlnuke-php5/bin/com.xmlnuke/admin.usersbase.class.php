<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
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

/**
 * Constants for the most common custom property values.
 * @package xmlnuke
 */
class UserProperty
{
	const Site = "editsite";
	const Role = "roles";

	/**
	 * Get a User property from property name
	 *
	 * @param UserProperty $userProp
	 * @return string
	 */
	public static function getPropertyNodeName($userProp)
	{
		$result = $userProp;

		switch ($userProp)
		{
			case UserProperty::Site:
			{
				$result = "editsite";
				break;
			}
			case UserProperty::Role:
			{
				$result = "roles";
				break;
			}
		}
		return $result;
	}

}

/**
 * Structure to represent the users in XMLNuke
 * @package xmlnuke
 */
class UserTable
{
	public $Table;
	public $Id;
	public $Name ;
	public $Email;
	public $Username ;
	public $Password ;
	public $Created;
	public $Admin ;
}

/**
 * Structure to represent the user's custom values in XMLNuke
 * @package xmlnuke
 */
class CustomTable
{
	public $Table;
	public $Id;
	public $Name;
	public $Value;
}

/**
 * Structure to represent the user roles used in XMLNuke. 
 * @package xmlnuke
 */
class RolesTable
{
	public $Table;
	public $Site;
	public $Role;
}

/**
 * Base implementation to search and handle users in XMLNuke.
 * @package xmlnuke
 */
abstract class UsersBase implements IUsersBase
{
	/**
	* Internal context
	*@var Context
	*/
	protected  $_context = null;
	/**
	*@var UserTable
	*/
	public  $_UserTable;
	/**
	*@var CustomTable
	*/
	public $_CustomTable;

	/**
	*@var RolesTable
	*/
	public $_RolesTable;

	/**
	* Save the current UsersAnyDataSet
	* */
	public function Save()
	{
	}

	/**
	 * Add new user in database
	 *
	 * @param string $name
	 * @param string $userName
	 * @param string $email
	 * @param string $password
	 * @return bool
	 */
	public function addUser( $name, $userName, $email, $password )
	{
	}

	/**
	* Get the user based on a filter.
	* Return SingleRow if user was found; null, otherwise
	*
	* @param IteratorFilter $filter Filter to find user
	* @return SingleRow
	**/
	public function getUser( $filter )
	{
	}

	/**
	* Get the user based on his email.
	* Return SingleRow if user was found; null, otherwise
	*
	* @param string $email
	* @return SingleRow
	* */
	public function getUserEMail( $email )
	{
		$filter = new IteratorFilter();
		$filter->addRelation($this->_UserTable->Email, Relation::Equal , strtolower($email));
		return $this->getUser($filter);
	}

	/**
	* Get the user based on his login.
	* Return SingleRow if user was found; null, otherwise
	*
	* @param string $username
	* @return SingleRow
	* */
	public function getUserName( $username )
	{
		$filter = new IteratorFilter();
		$filter->addRelation($this->_UserTable->Username, Relation::Equal , strtolower($username) );
		return $this->getUser($filter);
	}

	/**
	* Get the user based on his id.
	* Return SingleRow if user was found; null, otherwise
	*
	* @param string $id
	* @return SingleRow
	* */
	public function getUserId( $id )
	{
		$filter = new IteratorFilter();
		$filter->addRelation($this->_UserTable->Id, Relation::Equal , $id );
		return $this->getUser($filter);
	}

	/**
	* Remove the user based on his login.
	*
	* @param string $username
	* @return bool
	* */
	public function removeUserName( $username )
	{
	}

	/**
	* Get the SHA1 string from user password
	*
	* @param string $password Plain password
	* @return string
	* */
	public function getSHAPassword( $password )
	{
		return strtoupper(sha1($password));
	}

	/**
	* Validate if the user and password exists in the file
	* Return SingleRow if user exists; null, otherwise
	*
	* @param string $userName User login
	* @param string $password Plain text password
	* @return SingleRow
	* */
	public function validateUserName( $userName, $password )
	{
		$filter = new IteratorFilter();
		$filter->addRelation($this->_UserTable->Username, Relation::Equal , strtolower($userName));
		$filter->addRelation($this->_UserTable->Password, Relation::Equal , $this->getSHAPassword($password));
		return $this->getUser($filter);
	}

	/**
	* Check if the user have a property and it have a specific value.
	* Return True if have rights; false, otherwise
	*
	* @param mixed $userId User identification
	* @param string $propValue Property value
	* @param UserProperty $userProp Property name
	* @return bool
	* */
	public function checkUserProperty( $userId, $propValue, $userProp )
	{
		//anydataset.SingleRow
		$user = $this->getUserId( $userId );

		if ($user != null)
		{
			if ($user->getField($this->_UserTable->Admin) == "yes")
			{
				return true;
			}
			else
			{
				$values = $user->getFieldArray(UserProperty::getPropertyNodeName($userProp));
				return ($values != null ? in_array($propValue, $values) : false);
			}
		}
		else
		{
			return false;
		}
	}

	/**
	* Return all sites from a specific user
	* Return String vector with all sites
	*
	* @param string $userId User ID
	* @param UserProperty $userProp Property name
	* @return array
	* */
	public function returnUserProperty( $userId, $userProp )
	{
		//anydataset.SingleRow
		$user = $this->getUserId( $userId );
		if ($user != null)
		{
			//XmlNodeList
			$nodes = $user->getFieldArray(UserProperty::getPropertyNodeName($userProp));

			if ($user->getField($this->_UserTable->Admin) == "yes")
			{
				if ($userProp == UserProperty::Site)
				{
					//string[]
					$result = $this->_context->ExistingSites();
					for($i=0, $resultLength = count($result); $i<$resultLength ;$i++)
					{
						$result[FileUtil::ExtractFileName($result[$i])] = FileUtil::ExtractFileName($result[$i]);
					}
					return $result;
				}
				else
				{
					return array("admin" => "admin");

				}
			}
			else
			{
				if (count($nodes) == 0)
				{
					return null;
				}
				else
				{
					if ($userProp == UserProperty::Site)
					{
						foreach($nodes as $node)
						{
							$result[FileUtil::ExtractFileName($node)] = FileUtil::ExtractFileName($node);
						}
						return $result;
					}
					else
					{
						foreach($nodes as $node)
						{
							$result[$node] = $node;
						}
						return $result;

					}
				}
			}

		}
		else
		{
			return null;
		}
	}

	/**
	* Add a specific site to user
	* Return True or false
	*
	* @param string $userName User login
	* @param string $propValue Property value with a site
	* @param UserProperty $userProp Property name
	* @return bool
	* */
	public function addPropertyValueToUser( $userName, $propValue, $userProp )
	{
	}

	/**
	* Remove a specific site from user
	* Return True or false
	*
	* @param string $userName User login
	* @param string $propValue Property value with a site
	* @param UserProperty $userProp Property name
	* @return bool
	* */
	public function removePropertyValueFromUser( $userName, $propValue, $userProp )
	{
	}

	/**
	* Remove a specific site from all users
	* Return True or false
	*
	* @param string $propValue Property value with a site
	* @param UserProperty $userProp Property name
	* @return bool
	* */
	public function removePropertyValueFromAllUsers($propValue, $userProp)
	{
	}

	/**
	 * Config default name of the tables fields
	 *
	 */
	protected function configTableNames()
	{
		$this->_UserTable = new UserTable();
		$this->_UserTable->Table = "user";
		$this->_UserTable->Id = "userid";
		$this->_UserTable->Name = "name";
		$this->_UserTable->Email= "email";
		$this->_UserTable->Username = "username";
		$this->_UserTable->Password = "password";
		$this->_UserTable->Created = "created";
		$this->_UserTable->Admin = "admin";

		$this->_CustomTable = new CustomTable();
		$this->_CustomTable->Table = "custom";
		$this->_CustomTable->Id = "customid";
		$this->_CustomTable->Name = "name";
		$this->_CustomTable->Value = "value";
		// Table "CUSTOM" must have [$this->_UserTable->Id = "userid"].

		$this->_RolesTable = new RolesTable();
		$this->_RolesTable->Table = "roles";
		$this->_RolesTable->Site  = "site";
		$this->_RolesTable->Role = "role";
	}

	/**
	 * Get all roles
	 *
	 * @param string $site
	 * @param string $role
	 * @return IIterator
	 */
	public function getRolesIterator($site, $role = "")
	{
		throw new Exception("This method must be implemented");
	}

	/**
	 * Add a public role into a site
	 *
	 * @param string $site
	 * @param string $role
	 */
	public function addRolePublic($site, $role)
	{
		throw new Exception("This method must be implemented");
	}

	/**
	 * Edit a public role into a site. If new Value == null, remove the role)
	 *
	 * @param string $site
	 * @param string $role
	 * @param string $newValue
	 */
	public function editRolePublic($site, $role, $newValue = null)
	{
		throw new Exception("This method must be implemented");
	}

	/**
	 *
	 * @param int $userId
	 * @return bool
	 */
	public function userIsAdmin($userId = "")
	{
		if ($userId == "")
		{
			$userId = $this->_context->authenticatedUserId();
			if ($userId == "")
				throw new NotAuthenticatedException();
		}
		
		$user = $this->getUserId($userId);
		if ($user != null)
			return ($user->getField($this->_UserTable->Admin) == "yes");
		else
			throw new Exception("Cannot find the user");
	}

	/**
	 *
	 * @param string $role
	 * @param int $userId
	 * @return bool
	 */
	public function userHasRole($role, $userId = "")
	{
		if ($userId == "")
		{
			$userId = $this->_context->authenticatedUserId();
			if ($userId == "")
				throw new NotAuthenticatedException();
		}

		return $users->checkUserProperty($userId, $role, UserProperty::Role);
	}
}
?>