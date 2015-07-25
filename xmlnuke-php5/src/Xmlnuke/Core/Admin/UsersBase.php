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

namespace Xmlnuke\Core\Admin;

use ByJG\AnyDataset\Repository\IIterator;
use ByJG\AnyDataset\Repository\IteratorFilter;
use ByJG\AnyDataset\Repository\SingleRow;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Enum\CustomTable;
use ByJG\AnyDataset\Enum\Relation;
use Xmlnuke\Core\Enum\RolesTable;
use Xmlnuke\Core\Enum\UserProperty;
use Xmlnuke\Core\Enum\UserTable;
use Xmlnuke\Core\Exception\NotAuthenticatedException;
use Xmlnuke\Core\Exception\NotFoundException;
use Xmlnuke\Core\Exception\NotImplementedException;
use Xmlnuke\Util\FileUtil;

/**
 * Base implementation to search and handle users in XMLNuke.
 * @package xmlnuke
 */
abstract class UsersBase implements IUsersBase
{
	/**
	 * Internal context
 	 * @var Context
	 */
	protected  $_context = null;

	/**
	 * @var UserTable
	 */
	protected  $_UserTable;

	/**
	 * @var CustomTable
	 */
	protected $_CustomTable;

	/**
	*@var RolesTable
	*/
	protected $_RolesTable;


	/**
	 *
	 * @return UserTable
	 */
	public function getUserTable()
	{
		if ($this->_UserTable == null)
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
		}
		return $this->_UserTable;
	}

	/**
	 *
	 * @return CustomTable
	 */
	public function getCustomTable()
	{
		if ($this->_CustomTable == null)
		{
			$this->_CustomTable = new CustomTable();
			$this->_CustomTable->Table = "custom";
			$this->_CustomTable->Id = "customid";
			$this->_CustomTable->Name = "name";
			$this->_CustomTable->Value = "value";
			// Table "CUSTOM" must have [$this->_UserTable->Id = "userid"].
		}
		return $this->_CustomTable;
	}

	/**
	 *
	 * @return RolesTable
	 */
	public function getRolesTable()
	{
		if ($this->_RolesTable == null)
		{
			$this->_RolesTable = new RolesTable();
			$this->_RolesTable->Table = "roles";
			$this->_RolesTable->Site  = "site";
			$this->_RolesTable->Role = "role";
		}
		return $this->_RolesTable;
	}

	/**
	* Save the current UsersAnyDataset
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
		$filter->addRelation($this->getUserTable()->Email,  Relation::EQUAL , strtolower($email));
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
		$filter->addRelation($this->getUserTable()->Username,  Relation::EQUAL , strtolower($username) );
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
		$filter->addRelation($this->getUserTable()->Id,  Relation::EQUAL , $id );
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
		$filter->addRelation($this->getUserTable()->Username,  Relation::EQUAL , strtolower($userName));
		$filter->addRelation($this->getUserTable()->Password,  Relation::EQUAL , $this->getSHAPassword($password));
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
			if ($this->userIsAdmin($userId))
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

			if ($this->userIsAdmin($userId))
			{
				return array("admin" => "admin");
			}
			else
			{
				if (count($nodes) == 0)
				{
					return null;
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
	 * Get all roles
	 *
	 * @param string $site
	 * @param string $role
	 * @return IIterator
	 */
	public function getRolesIterator($site, $role = "")
	{
		throw new NotImplementedException("This method must be implemented");
	}

	/**
	 * Add a public role into a site
	 *
	 * @param string $site
	 * @param string $role
	 */
	public function addRolePublic($site, $role)
	{
		throw new NotImplementedException("This method must be implemented");
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
		throw new NotImplementedException("This method must be implemented");
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
			return (
                            ($user->getField($this->getUserTable()->Admin) == "yes") ||
                            ($user->getField($this->getUserTable()->Admin) == "y") ||
                            ($user->getField($this->getUserTable()->Admin) == "true") ||
                            ($user->getField($this->getUserTable()->Admin) == "t") ||
                            ($user->getField($this->getUserTable()->Admin) == "1")
                        );
		else
			throw new NotFoundException("Cannot find the user");
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