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
 * Stores and retrieve user information based on AnyDataset file. 
 * 
 * This is the default method in XMLNuke. 
 * The file where the users, passwords and properties are stored is located on xmlnuke-data/shared/setup/users.anydata.xml
 * 
 * @see UsersDBDataset
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Admin;

use ByJG\AnyDataset\Repository\AnyDataset;
use ByJG\AnyDataset\Repository\IteratorInterface;
use ByJG\AnyDataset\Repository\IteratorFilter;
use ByJG\AnyDataset\Repository\SingleRow;
use ByJG\AnyDataset\Enum\Relation;
use Xmlnuke\Core\Enum\UserProperty;
use Xmlnuke\Core\Exception\DatasetException;
use Xmlnuke\Core\Processor\AnydatasetSetupFilenameProcessor;

class UsersAnyDataset extends UsersBase
{
	/**
	 * Internal AnyDataset structure to store the Users
	 * @var AnyDataset
	 */
	protected $_anyDataSet;


	/**
	 * Internal Users file name
	 *
	 * @var AnydatasetSetupFilenameProcessor
	 */
	protected $_usersFile;

	/**
	 * AnyDataset constructor
	*/
	public function __construct()
	{
		$this->_usersFile = new AnydatasetSetupFilenameProcessor("users");
		$this->_anyDataSet = new AnyDataset($this->_usersFile->FullQualifiedNameAndPath());
	}

	/**
	 * Save the current UsersAnyDataset
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

		$this->_anyDataSet->addField( $this->getUserTable()->Name, $name );
		$this->_anyDataSet->addField( $this->getUserTable()->Username, preg_replace('/(?:([\w])|([\W]))/', '\1', strtolower($userName)));
		$this->_anyDataSet->addField( $this->getUserTable()->Email, strtolower($email));
		$this->_anyDataSet->addField( $this->getUserTable()->Password, $this->getSHAPassword($password) );
		$this->_anyDataSet->addField( $this->getUserTable()->Admin, "" );
		$this->_anyDataSet->addField( $this->getUserTable()->Created, date("Y-m-d H:i:s") );
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
	 * @return IteratorInterface
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
			if(!$this->checkUserProperty($user->getField($this->getUserTable()->Id), $propValue, $userProp ))
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
			$this->removePropertyValueFromUser($user->getField($this->getUserTable()->Username), $propValue, $userProp);
		}
	}

	/**
	 * Enter description here...
	 *
	 * @return AnyDataset
	 */
	protected function getRoleAnydataSet()
	{
		$fileRole = new AnydatasetSetupFilenameProcessor($this->getRolesTable()->Table);
		$roleDataSet = new AnyDataset($fileRole->FullQualifiedNameAndPath());
		return $roleDataSet;
	}

	/**
	 * Get all roles
	 *
	 * @param string $site
	 * @param string $role
	 * @return IteratorInterface
	 */
	public function getRolesIterator($site, $role = "")
	{
		$itf = new IteratorFilter();
		if ($role != "")
		{
			$itf->addRelation($this->getRolesTable()->Role,  Relation::EQUAL, $role);
		}
		$itf->startGroup();
		$itf->addRelation($this->getRolesTable()->Site,  Relation::EQUAL, $site);
		$itf->addRelationOr($this->getRolesTable()->Site,  Relation::EQUAL, "_all");
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
		$dataFilter->addRelation($this->getRolesTable()->Site,  Relation::EQUAL, $site);
		$iterator = $dataset->getIterator($dataFilter);
		if(!$iterator->hasNext())
		{
			$dataset->appendRow();
			$dataset->addField($this->getRolesTable()->Site, $site);
			$dataset->addField($this->getRolesTable()->Role, $role);
		}
		else
		{
			$dataFilter->addRelation($this->getRolesTable()->Role,  Relation::EQUAL, $role);
			$iteratorCheckDupRole = $dataset->getIterator($dataFilter);
			if (!$iteratorCheckDupRole->hasNext())
			{
				$sr = $iterator->moveNext();
				$sr->AddField($this->getRolesTable()->Role, $role);
			}
			else
			{
				throw new DatasetException("Role exists");
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
		$dataFilter->addRelation($this->getRolesTable()->Site,  Relation::EQUAL, $site);
		$dataFilter->addRelation($this->getRolesTable()->Role,  Relation::EQUAL, $role);
		$it = $roleDataSet->getIterator($dataFilter);
		if ($it->hasNext()) {
			$sr = $it->moveNext();
			$sr->removeFieldName($role);
		}
		$roleDataSet->Save();
	}

	public function getUserTable()
	{
		if ($this->_UserTable == null)
		{
			parent::getUserTable();
			$this->_UserTable->Id = $this->_UserTable->Username;
		}
		return $this->_UserTable;
	}

}
?>
