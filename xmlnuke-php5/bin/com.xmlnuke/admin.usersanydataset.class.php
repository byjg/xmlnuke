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
 * UserAnyDataSet is a class to Store and Retrive USERS from an AnyDataSet structure.
 * Note that UsersAnyDataSet doesn't inherits from AnyDataSet, because some funcionalities
 * from AnyDataSet didn't used in this class.
*/
class UsersAnyDataSet extends UsersBase
{
	/**
	 * Internal AnyDataSet structure to store the Users
	 * @var AnyDataSet
	 */
	protected $_anyDataSet;


	/**
	 * Internal Users file name
	 *
	 * @var AnydatasetSetupFilenameProcessor
	 */
	protected $_usersFile;

	/**
	 * AnyDataSet constructor
	*/
	public function UsersAnyDataSet($context)
	{
		$this->_context = $context;
		$this->_usersFile = new AnydatasetSetupFilenameProcessor("users", $context);
		$this->_anyDataSet = new AnyDataSet($this->_usersFile);
		$this->configTableNames();
		$this->_UserTable->Id = $this->_UserTable->Username;
	}

	/**
	 * Save the current UsersAnyDataSet
	*/
	public function Save()
	{
		$this->_anyDataSet->Save($this->_usersFile);
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
		if ($this->getUserEMail($email) != null)
		{
			return false;
		}
		if ($this->getUserName($userName) != null)
		{
			return false;
		}
		$this->_anyDataSet->appendRow();

		$this->_anyDataSet->addField( $this->_UserTable->Name, $name );
		$this->_anyDataSet->addField( $this->_UserTable->Username, preg_replace('/(?:([\w])|([\W]))/', '\1', strtolower($userName)));
		$this->_anyDataSet->addField( $this->_UserTable->Email, strtolower($email));
		$this->_anyDataSet->addField( $this->_UserTable->Password, $this->getSHAPassword($password) );
		$this->_anyDataSet->addField( $this->_UserTable->Admin, "" );
		$this->_anyDataSet->addField( $this->_UserTable->Created, date("Y-m-d H:i:s") );
		return true;
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
		$it = $this->_anyDataSet->getIterator($filter);
		if (!$it->hasNext())
		{
			return null;
		}
		else
		{
			return $it->moveNext();
		}
	}

	/**
	* Get the user based on his login.
	* Return SingleRow if user was found; null, otherwise
	*
	* @param string $username
	* @return SingleRow
	* */
	public function removeUserName( $username )
	{
		//anydataset.SingleRow
		$user = $this->getUserName( $username );
		if  ($user != null)
		{
			$this->_anyDataSet->removeRow( $user );
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get an Iterator based on a filter
	 *
	 * @param IteratorFilter $filter
	 * @return IIterator
	 */
	public function getIterator($filter = null)
	{
		return $this->_anyDataSet->getIterator($filter);
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
		//anydataset.SingleRow
		$user = $this->getUserName( $userName );
		if ($user != null)
		{
			if(!$this->checkUserProperty($user->getField($this->_UserTable->Id), $propValue, $userProp ))
			{
				$user->AddField(UserProperty::getPropertyNodeName($userProp), $propValue );
			}
			return true;
		}
		else
		{
			return false;
		}
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
		$user = $this->getUserName( $userName );
		if ($user != null)
		{
			$user->removeFieldNameValue(UserProperty::getPropertyNodeName($userProp), $propValue);
			$this->Save();
			return true;
		}
		else
		{
			return false;
		}
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
		$it = $this->getIterator(null);
		while ($it->hasNext())
		{
			//anydataset.SingleRow
			$user = $it->moveNext();
			$this->removePropertyValueFromUser($user->getField($this->_UserTable->Username), $propValue, $userProp);
		}
	}

	/**
	 * Enter description here...
	 *
	 * @return AnyDataSet
	 */
	protected function getRoleAnydataSet()
	{
		$fileRole = new AnydatasetSetupFilenameProcessor($this->_RolesTable->Table, $this->_context);
		$roleDataSet = new AnyDataSet($fileRole);
		return $roleDataSet;
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
		$itf = new IteratorFilter();
		if ($role != "")
		{
			$itf->addRelation($this->_RolesTable->Role, Relation::Equal, $role);
		}
		$itf->startGroup();
		$itf->addRelation($this->_RolesTable->Site, Relation::Equal, $site);
		$itf->addRelationOr($this->_RolesTable->Site, Relation::Equal, "_all");
		$itf->endGroup();

		$roleDataSet = $this->getRoleAnydataSet();
		return $roleDataSet->getIterator($itf);
	}

	/**
	 * Add a public role into a site
	 *
	 * @param string $site
	 * @param string $role
	 */
	public function addRolePublic($site, $role)
	{
		$dataset = $this->getRoleAnydataSet();
		$dataFilter = new IteratorFilter();
		$dataFilter->addRelation($this->_RolesTable->Site, Relation::Equal, $site);
		$iterator = $dataset->getIterator($dataFilter);
		if(!$iterator->hasNext())
		{
			$dataset->appendRow();
			$dataset->addField($this->_RolesTable->Site, $site);
			$dataset->addField($this->_RolesTable->Role, $role);
		}
		else
		{
			$dataFilter->addRelation($this->_RolesTable->Role, Relation::Equal, $role);
			$iteratorCheckDupRole = $dataset->getIterator($dataFilter);
			if (!$iteratorCheckDupRole->hasNext())
			{
				$sr = $iterator->moveNext();
				$sr->AddField($this->_RolesTable->Role, $role);
			}
			else
			{
				throw new Exception("Role exists");
			}
		}
		$dataset->Save();
	}

	/**
	 * Edit a public role into a site. If new Value == null, remove the role)
	 *
	 * @param string $site
	 * @param string $role
	 * @param string $newValue Null remove the value
	 */
	public function editRolePublic($site, $role, $newValue = null)
	{
		if ($newValue != null)
		{
			$this->addRolePublic($site, $newValue);
		}

		$roleDataSet = $this->getRoleAnydataSet();
		$dataFilter = new IteratorFilter();
		$dataFilter->addRelation($this->_RolesTable->Site, Relation::Equal, $site);
		$dataFilter->addRelation($this->_RolesTable->Role, Relation::Equal, $role);
		$it = $roleDataSet->getIterator($dataFilter);
		if ($it->hasNext()) {
			$sr = $it->moveNext();
			$sr->removeFieldName($role);
		}
		$roleDataSet->Save();
	}

}
?>
