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
 * Stores and retrieves user information from a database.
 * @see UsersAnyDataSet
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Admin;

/**
 * Authentication constants.
 */
define('AUTH_PASSWORD_NOT_CACHED', 'not cached'); // String used in password field when password is not stored.


use ErrorException;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Enum\UserProperty;
use Xmlnuke\Core\Exception\NotImplementedException;

class UsersMoodleDataSet extends UsersDBDataSet
{

	/**
	 * @var string
	 */
	protected $_siteSalt = "";

	/**
	  * DBDataSet constructor
	  */
	public function __construct(Context $context, $dataBase)
	{
		parent::__construct($context, $dataBase);

		$this->_siteSalt = $context->get('moodle.sitesalt');
	}

	/**
	 *
	 * Save the current UsersAnyDataSet
	 */
	public function Save()
	{
		throw new NotImplementedException('Save user is not implemented');
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
		throw new NotImplementedException('Add new user is not implemented');
	}

	protected function password_is_legacy_hash($password)
	{
		return (bool) preg_match('/^[0-9a-f]{32}$/', $password);
	}

	public function validateUserName($userName, $password)
	{
		$user = $this->getUserName($userName);
		if ($user == null)
			return null;

		$savedPassword = $user->getField($this->getUserTable()->Password);
		$validatedUser = null;

		if ($savedPassword === AUTH_PASSWORD_NOT_CACHED)
			return null;

		if ($this->password_is_legacy_hash($savedPassword))
		{
			if ($savedPassword === md5($password . $this->_siteSalt)
				|| $savedPassword === md5($password)
				|| $savedPassword === md5(addslashes($password) . $this->_siteSalt)
				|| $savedPassword === md5(addslashes($password))
				)
				$validatedUser = $user;
		}
		else
		{
			if (!function_exists('crypt'))
			{
				throw new ErrorException("Crypt must be loaded for password_verify to function");
			}

			$ret = crypt($password, $savedPassword);
			if (!is_string($ret) || strlen($ret) != strlen($savedPassword) || strlen($ret) <= 13)
			{
				return null;
			}

			$status = 0;
			for ($i = 0; $i < strlen($ret); $i++) {
				$status |= (ord($ret[$i]) ^ ord($savedPassword[$i]));
			}

			if ($status === 0)
				$validatedUser = $user;
		}

		return $validatedUser;
	}

	public function getUser($filter)
	{
		$user = parent::getUser($filter);

		if ($user != null)
		{
			// Get the user's roles from moodle
			$sqlRoles = 'SELECT shortname
						 FROM
							mdl_role AS r
						INNER JOIN
							mdl_role_assignments AS ra
								ON ra.roleid = r.id
						INNER JOIN mdl_user  AS u
								ON u.id = ra.userid
						WHERE userid = [[id]]
						group by shortname';
			$param = array("id" => $user->getField($this->getUserTable()->Id));
			$it = $this->_DB->getIterator($sqlRoles, $param);
			foreach ($it as $sr)
			{
				$user->AddField("roles", $sr->getField('shortname'));
			}

			// Find the moodle site admin (super user)
			$user->setField($this->getUserTable()->Admin, 'no');
			$sqlAdmin = "select value from mdl_config where name = 'siteadmins'";
			$it = $this->_DB->getIterator($sqlAdmin);
			if ($it->hasNext())
			{
				$sr = $it->moveNext();
				$siteAdmin = ',' . $sr->getField('value') . ',';
				$isAdmin = (strpos($siteAdmin, ",{$user->getField($this->getUserTable()->Id)},") !== false);
				$user->setField($this->getUserTable()->Admin, $isAdmin ? 'yes' : 'no');
			}
		}

		return $user;
	}


	/**
	* Remove the user based on his user login.
	*
	* @param string $login
	* @return bool
	* */
	public function removeUserName( $login )
	{
		throw new NotImplementedException('Remove user is not implemented');
	}

	/**
	* Remove the user based on his user id.
	*
	* @param int $userId
	* @return bool
	* */
	public function removeUserById( $userId )
	{
		throw new NotImplementedException('Remove user by Id is not implemented');
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
		throw new NotImplementedException('Remove property value from all users is not implemented');
	}


	/**
	 * Add a public role into a site
	 *
	 * @param string $site
	 * @param string $role
	 */
	public function addRolePublic($site, $role)
	{
		throw new NotImplementedException('Add role public is not implemented');
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
		throw new NotImplementedException('Edit role public is not implemented');
	}

	public function getUserTable()
	{
		if ($this->_UserTable == null)
		{
			parent::getUserTable();
			$this->_UserTable->Table = "mdl_user";
			$this->_UserTable->Id = "id";
			$this->_UserTable->Name = "concat(firstname, ' ', lastname)";  // This disable update data
			$this->_UserTable->Email= "email";
			$this->_UserTable->Username = "username";
			$this->_UserTable->Password = "password";
			$this->_UserTable->Created = "created";
			$this->_UserTable->Admin = "auth";							// This disable update data
		}
		return $this->_UserTable;
	}

	public function getCustomTable()
	{
		if ($this->_CustomTable == null)
		{
			parent::getCustomTable();
			$this->_CustomTable->Table = "mdl_user_info_data";
			$this->_CustomTable->Id = "id";
			$this->_CustomTable->Name = "fieldid";
			$this->_CustomTable->Value = "data";
		}
		return $this->_CustomTable;
	}

	public function getRolesTable()
	{
		if ($this->_RolesTable == null)
		{
			parent::getRolesTable();
			$this->_RolesTable->Table = "mdl_role";
			$this->_RolesTable->Site  = "'_all'";
			$this->_RolesTable->Role = "shortname";
		}
		return $this->_RolesTable;
	}
}
