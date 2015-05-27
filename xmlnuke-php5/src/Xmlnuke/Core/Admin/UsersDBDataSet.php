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

use InvalidArgumentException;
use Xmlnuke\Core\AnyDataset\AnyDataSet;
use Xmlnuke\Core\AnyDataset\DBDataSet;
use Xmlnuke\Core\AnyDataset\IIterator;
use Xmlnuke\Core\AnyDataset\IteratorFilter;
use Xmlnuke\Core\AnyDataset\SingleRow;
use Xmlnuke\Core\Database\SQLHelper;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Enum\UserProperty;
use Xmlnuke\Core\Exception\DatasetException;

class UsersDBDataSet extends UsersBase
{
	/**
	* @var DBDataset
	*/
	protected $_DB;

	protected $_SQLHelper;

	protected $_cacheUserWork = array();
    protected $_cacheUserOriginal = array();


	/**
	  * DBDataSet constructor
	  */
	public function __construct(Context $context, $dataBase)
	{
		$this->_context = $context;
		$this->_DB = new DBDataSet($dataBase, $context);
		$this->_SQLHelper = new SQLHelper($this->_DB);
	}

	/**
	 *
	 * Save the current UsersAnyDataSet
	 */
	public function Save()
	{
        foreach ($this->_cacheUserOriginal as $key=>$value)
        {
            $srOri = $this->_cacheUserOriginal[$key];
            $srMod = $this->_cacheUserWork[$key];

			// Look for changes
            $changeUser = false;
            foreach ($srMod->getFieldNames() as $keyfld=>$fieldname)
            {
				$userField = ($fieldname == $this->getUserTable()->Name
					|| $fieldname == $this->getUserTable()->Email
					|| $fieldname == $this->getUserTable()->Username
					|| $fieldname == $this->getUserTable()->Password
					|| $fieldname == $this->getUserTable()->Created
					|| $fieldname == $this->getUserTable()->Admin
					|| $fieldname == $this->getUserTable()->Id
				);
                if ($srOri->getField($fieldname) != $srMod->getField($fieldname))
                {
					// This change is in the Users table or is a Custom property?
					if ($userField)
					{
						$changeUser = true;
					}
					else
					{
						// Erase Old Custom Properties
						$sql = $this->_SQLHelper->createSafeSQL("DELETE FROM :Table "
								. " WHERE :Id = [[id]] "
								. "   AND :Name = [[name]] "
								. "   AND :Value = [[value]] ",
								array(
									":Table" => $this->getCustomTable()->Table,
									":Id" => $this->getUserTable()->Id,
									":Name" => $this->getCustomTable()->Name,
									":Value" => $this->getCustomTable()->Value
								)
						);

						$param = array(
							'id' => $srMod->getField($this->getUserTable()->Id),
							'name' => $fieldname,
							'value' => $srOri->getField($fieldname)
						);
						$this->_DB->execSQL($sql, $param);

						// If new Value is_empty does not add
						if ($srMod->getField($fieldname) == "")
							continue;

						// Insert new Value
						$sql = $this->_SQLHelper->createSafeSQL("INSERT INTO :Table "
								. "( :Id, :Name, :Value ) "
								. " VALUES ( [[id]], [[name]], [[value]] ) ",
								array(
									":Table" => $this->getCustomTable()->Table,
									":Id" => $this->getUserTable()->Id,
									":Name" => $this->getCustomTable()->Name,
									":Value" => $this->getCustomTable()->Value
								)
						);

						$param = array();
						$param["id"] = $srMod->getField($this->getUserTable()->Id);
						$param["name"] = $fieldname;
						$param["value"] = $srMod->getField($fieldname);

						$this->_DB->execSQL($sql, $param);

					}
                }
            }

            if($changeUser)
			{
				$sql = "UPDATE :Table ";
				$sql .= " SET :Name  = [[name]] ";
				$sql .= ", :Email = [[email]] ";
				$sql .= ", :Username = [[username]] ";
				$sql .= ", :Password = [[password]] ";
				$sql .= ", :Created = [[created]] ";
				$sql .= ", :Admin = [[admin]] ";
				$sql .= " WHERE :Id = [[id]]";

				$sql = $this->_SQLHelper->createSafeSQL($sql, array(
						':Table' => $this->getUserTable()->Table,
						':Name' => $this->getUserTable()->Name,
						':Email' => $this->getUserTable()->Email,
						':Username' => $this->getUserTable()->Username,
						':Password' => $this->getUserTable()->Password,
						':Created' => $this->getUserTable()->Created,
						':Admin' => $this->getUserTable()->Admin,
						':Id' => $this->getUserTable()->Id
					)	
				);

				$param = array();
				$param['name'] = $srMod->getField($this->getUserTable()->Name);
				$param['email'] = $srMod->getField($this->getUserTable()->Email);
				$param['username'] = $srMod->getField($this->getUserTable()->Username);
				$param['password'] = $srMod->getField($this->getUserTable()->Password);
				$param['created'] = $srMod->getField($this->getUserTable()->Created);
				$param['admin'] = $srMod->getField($this->getUserTable()->Admin);
				$param['id'] = $srMod->getField($this->getUserTable()->Id);

				$this->_DB->execSQL($sql, $param);
			}
        }
        $this->_cacheUserOriginal = array();
        $this->_cacheUserWork = array();
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
		$sql = " INSERT INTO :Table (:Name, :Email, :Username, :Password, :Created ) ";
		$sql .=" VALUES ([[name]], [[email]], [[username]], [[password]], [[created]] ) ";

		$sql = $this->_SQLHelper->createSafeSQL($sql, array(
				':Table' => $this->getUserTable()->Table,
				':Name' => $this->getUserTable()->Name,
				':Email' => $this->getUserTable()->Email,
				':Username' => $this->getUserTable()->Username,
				':Password' => $this->getUserTable()->Password,
				':Created' => $this->getUserTable()->Created,
			)
		);

		$param = array();
		$param['name'] = $name;
		$param['email'] = strtolower($email);
		$param['username'] = preg_replace('/(?:([\w])|([\W]))/', '\1', strtolower($userName));
		$param['password'] = $this->getSHAPassword($password);
		$param['created'] = date("Y-m-d H:i:s");

		$this->_DB->execSQL($sql, $param);

		return true;
	}

	/**
	 * Get the users database information based on a filter.
	 *
	 * @param IteratorFilter $filter Filter to find user
	 * @param array $param
	 * @return IIterator
	 */
	public function getIterator(IteratorFilter $filter = null, $param = array())
	{
		$sql = "";
		$param = array();
		if (is_null($filter))
		{
			$filter = new IteratorFilter();
		}
		$sql = $filter->getSql($this->getUserTable()->Table, $param);
		return $this->_DB->getIterator($sql, $param);
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
		$it = $this->getIterator($filter);
		if ($it->hasNext())
		{
			// Get the Requested User
			$sr = $it->moveNext();
			$this->getCustomFields($sr);

                // Clone the User Properties
                $anyOri = new AnyDataSet();
                $anyOri->appendRow();
                foreach ($sr->getFieldNames() as $key=>$fieldName)
                {
                    $anyOri->addField($fieldName, $sr->getField($fieldName));
                }
                $itOri = $anyOri->getIterator();
                $srOri = $itOri->moveNext();

                // Store and return to the user the proper single row.
                $this->_cacheUserOriginal[$sr->getField($this->getUserTable()->Id)] = $srOri;
                $this->_cacheUserWork[$sr->getField($this->getUserTable()->Id)] = $sr;
                return $this->_cacheUserWork[$sr->getField($this->getUserTable()->Id)];
		}
		else
		{
			return null;
		}
	}

	/**
	* Remove the user based on his user login.
	*
	* @param string $login
	* @return bool
	* */
	public function removeUserName( $login )
	{
		$baseSql = "DELETE FROM :Table WHERE :Username = [[login]] ";
		$param = array( "login" => $login );
		if ($this->getCustomTable()->Table != "")
		{
			$sql = $this->_SQLHelper->createSafeSQL($baseSql, array(
				':Table' => $this->getCustomTable()->Table,
				':Username' => $this->getUserTable()->Username
			));
			$this->_DB->execSQL($sql, $param);
		}
		$sql = $this->_SQLHelper->createSafeSQL($baseSql, array(
			':Table' => $this->getUserTable()->Table,
			':Username' => $this->getUserTable()->Username
		));
		$this->_DB->execSQL($sql, $param);
		return true;
	}

	/**
	* Remove the user based on his user id.
	*
	* @param int $userId
	* @return bool
	* */
	public function removeUserById( $userId )
	{
		$baseSql = "DELETE FROM :Table WHERE :Id = [[login]] ";
		$param = array("id"=>$userId);
		if ($this->getCustomTable()->Table != "")
		{
			$sql = $this->_SQLHelper->createSafeSQL($baseSql, array(
				':Table' => $this->getCustomTable()->Table,
				':Id' => $this->getUserTable()->Id
			));
			$this->_DB->execSQL($sql, $param);
		}
		$sql = $this->_SQLHelper->createSafeSQL($baseSql, array(
			':Table' => $this->getUserTable()->Table,
			':Id' => $this->getUserTable()->Id
		));
		$this->_DB->execSQL($sql, $param);
		return true;
	}

	/**
	* Add a specific site to user
	* Return True or false
	*
	* @param int $userId User Id
	* @param string $propValue Property value with a site
	* @param UserProperty $userProp Property name
	* @return bool
	* */
	public function addPropertyValueToUser( $userId, $propValue, $userProp )
	{
		//anydataset.SingleRow
		$user = $this->getUserId( $userId );
		if ($user != null)
		{
			if(!$this->checkUserProperty($userId, $propValue, $userProp))
			{
				$sql = " INSERT INTO :Table ( :Id, :Name, :Value ) ";
				$sql .=" VALUES ( [[id]], [[name]], [[value]] ) ";

				$sql = $this->_SQLHelper->createSafeSQL($sql, array(
					":Table" => $this->getCustomTable()->Table,
					":Id" => $this->getUserTable()->Id, 
					":Name" => $this->getCustomTable()->Name,
					":Value" => $this->getCustomTable()->Value
				));

				$param = array();
				$param["id"] = $userId;
				$param["name"] = UserProperty::getPropertyNodeName($userProp);
				$param["value"] = $propValue;

				$this->_DB->execSQL($sql, $param);
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
	* @param int $userId User Id
	* @param string $propValue Property value with a site
	* @param UserProperty $userProp Property name
	* @return bool
	* */
	public function removePropertyValueFromUser( $userId, $propValue, $userProp )
	{
		$user = $this->getUserId( $userId );
		if ($user != null)
		{
			$param = array();
			$param["id"] = $userId;
			$param["name"] = UserProperty::getPropertyNodeName($userProp);

			$sql =  "DELETE FROM :Table ";
			$sql .= " WHERE :Id = [[id]] AND :Name = [[name]] ";
			if(!is_null($propValue))
			{
				$sql .= " AND :Value = [[value]] ";
				$param["value"] = $propValue;
			}
			$sql = $this->_SQLHelper->createSafeSQL($sql, array(
					':Table' => $this->getCustomTable()->Table,
					':Name' => $this->getCustomTable()->Name,
					':Id' => $this->getUserTable()->Id,
					':Value' => $this->getCustomTable()->Value
				)
			);

			$this->_DB->execSQL($sql, $param);
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
		$param = array();
		$param["name"] = UserProperty::getPropertyNodeName($userProp);
		$param["value"] = $propValue;

		$sql = "DELETE FROM :Table WHERE :Name = [[name]] AND :Value = [[value]] ";

		$sql = $this->_SQLHelper->createSafeSQL($sql, array(
			":Table" => $this->getCustomTable()->Table,
			":Name" => $this->getCustomTable()->Name,
			":Value" => $this->getCustomTable()->Value
		));

		$this->_DB->execSQL($sql, $param);
	}


	/**
	 * Return all custom's fields from this user
	 *
	 * @param unknown_type $userRow
	 * @return unknown
	 */
	protected function getCustomFields($userRow)
	{
		if ($this->getCustomTable()->Table == "")
		{
			return null;
		}

		$userId = $userRow->getField($this->getUserTable()->Id);
		$sql = "select * from :Table where :Id = [[id]]";

		$sql = $this->_SQLHelper->createSafeSQL($sql, array(
			":Table" => $this->getCustomTable()->Table,
			":Id" => $this->getUserTable()->Id
		));
				
		$param = array('id' => $userId);
		$it = $this->_DB->getIterator($sql, $param);
		while ($it->hasNext())
		{
			$sr = $it->moveNext();
			$userRow->AddField($sr->getField($this->getCustomTable()->Name), $sr->getField($this->getCustomTable()->Value));
		}
	}

	/**
	 * Get all roles
	 *
	 * @param string $site
	 * @param string $role
	 * @return IIterator
	 */
	public function getRolesIterator($site = "_all", $role = "")
	{
		$param = array();
		$param["site"] = $site;

		$sql = "select * from :Table " .
			" where (:Site = [[site]] or :Site = '_all' ) ";

		if ($role != "")
		{
			$sql .= " and  :Role = [[role]] ";
			$param["role"] = $role;
		}

		$sql = $this->_SQLHelper->createSafeSQL($sql, array(
			":Table" => $this->getRolesTable()->Table,
			":Site" => $this->getRolesTable()->Site,
			":Role" => $this->getRolesTable()->Role
		));

		return $this->_DB->getIterator($sql, $param);
	}


	/**
	 * Add a public role into a site
	 *
	 * @param string $site
	 * @param string $role
	 */
	public function addRolePublic($site, $role)
	{
		$it = $this->getRolesIterator($site, $role);
		if ($it->hasNext())
		{
			throw new DatasetException("Role exists.");
		}

		$sql = "insert into :Table ( :Site, :Role ) values ( [[site]], [[role]] )";

		$sql = $this->_SQLHelper->createSafeSQL($sql, array(
			":Table" => $this->getRolesTable()->Table,
			":Site" => $this->getRolesTable()->Site 
		));

		$param = array("site"=>$site, "role"=>$role);

		$this->_DB->execSQL($sql, $param);
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
		if (!is_null($newValue))
		{
			$this->addRolePublic($site, $newValue);
		}

		$sql = "DELETE FROM :Table " .
			" WHERE :Site = [[site]] " .
			" AND :Role = [[role]] ";

		$sql = $this->_SQLHelper->createSafeSQL($sql, array(
			":Table" => $this->getRolesTable()->Table, 
			":Site" => $this->getRolesTable()->Site,
			":Role" => $this->getRolesTable()->Role
		));

		$param = array("site"=>$site, "role"=>$role);

		$this->_DB->execSQL($sql, $param);
	}

	public function getUserTable()
	{
		if ($this->_UserTable == null)
		{
			parent::getUserTable();
			$this->_UserTable->Table = "xmlnuke_users";
		}
		return $this->_UserTable;
	}

	public function getCustomTable()
	{
		if ($this->_CustomTable == null)
		{
			parent::getCustomTable();
			$this->_CustomTable->Table = "xmlnuke_custom";
		}
		return $this->_CustomTable;
	}

	public function getRolesTable()
	{
		if ($this->_RolesTable == null)
		{
			parent::getRolesTable();
			$this->_RolesTable->Table = "xmlnuke_roles";
		}
		return $this->_RolesTable;
	}

}
?>
