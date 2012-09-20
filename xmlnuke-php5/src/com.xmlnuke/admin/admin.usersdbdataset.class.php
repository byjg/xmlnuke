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
class UsersDBDataSet extends UsersBase
{
	/**
	* @var DBDataset
	*/
	protected $_DB;

	protected $_cacheUserWork = array();
    protected $_cacheUserOriginal = array();


	/**
	  * DBDataSet constructor
	  */
	public function UsersDBDataSet($context, $dataBase)
	{
		$this->_context = $context;
		$this->_DB = new DBDataSet($dataBase, $context);
		$this->configTableNames();
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

            $changed = false;
            foreach ($srOri->getFieldNames() as $keyfld=>$fieldname)
            {
                if ($srOri->getField($fieldname) != $srMod->getField($fieldname))
                {
                    $changed = true;
                    break;
                }
            }

            if($changed)
			{
				$sql = "UPDATE ".$this->getUserTable()->Table;
				$sql .= " SET ".$this->getUserTable()->Name." = [[".$this->getUserTable()->Name."]] ";
				$sql .= ", ".$this->getUserTable()->Email." = [[".$this->getUserTable()->Email."]] ";
				$sql .= ", ".$this->getUserTable()->Username." = [[".$this->getUserTable()->Username."]] ";
				$sql .= ", ".$this->getUserTable()->Password." = [[".$this->getUserTable()->Password."]] ";
				$sql .= ", ".$this->getUserTable()->Created." = [[".$this->getUserTable()->Created."]] ";
				$sql .= ", ".$this->getUserTable()->Admin." = [[".$this->getUserTable()->Admin."]] ";
				$sql .= " WHERE ".$this->getUserTable()->Id." = [[".$this->getUserTable()->Id . "]]";

				$param = array();
				$param[$this->getUserTable()->Name] = $srMod->getField($this->getUserTable()->Name);
				$param[$this->getUserTable()->Email] = $srMod->getField($this->getUserTable()->Email);
				$param[$this->getUserTable()->Username] = $srMod->getField($this->getUserTable()->Username);
				$param[$this->getUserTable()->Password] = $srMod->getField($this->getUserTable()->Password);
				$param[$this->getUserTable()->Created] = $srMod->getField($this->getUserTable()->Created);
				$param[$this->getUserTable()->Admin] = $srMod->getField($this->getUserTable()->Admin);
				$param[$this->getUserTable()->Id] = $srMod->getField($this->getUserTable()->Id);

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
		$sql = " INSERT INTO ".$this->getUserTable()->Table." (".$this->getUserTable()->Name.", ".$this->getUserTable()->Email.", ".$this->getUserTable()->Username .", ".$this->getUserTable()->Password .", ".$this->getUserTable()->Created ." ) ";
		$sql .=" VALUES ([[".$this->getUserTable()->Name."]], [[".$this->getUserTable()->Email."]], [[".$this->getUserTable()->Username ."]], [[".$this->getUserTable()->Password ."]], [[".$this->getUserTable()->Created ."]] ) ";

		$param = array();
		$param[$this->getUserTable()->Name] = $name;
		$param[$this->getUserTable()->Email] = strtolower($email);
		$param[$this->getUserTable()->Username] = preg_replace('/(?:([\w])|([\W]))/', '\1', strtolower($userName));
		$param[$this->getUserTable()->Password] = $this->getSHAPassword($password);
		$param[$this->getUserTable()->Created] = date("Y-m-d H:i:s");

		$this->_DB->execSQL($sql, $param);

		return true;
	}

	/**
	* Get the users database information based on a filter.
	*
	* @param IteratorFilter $filter Filter to find user
	* @return IIterator
	**/
	public function getIterator($filter = null, $param = array())
	{
		$sql = "";
		$param = array();
		if (is_object($filter) && get_class($filter) == "IteratorFilter")
		{
			$sql = $filter->getSql($this->getUserTable()->Table, $param);
		}
		else
		{
			$sql = "select * from ".$this->getUserTable()->Table;
			if (!is_null($filter))
				$sql .= " where " . $filter;
		}
		$sql .= " order by ".$this->getUserTable()->Name;
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
		$param = array("login"=>$login);
		if ($this->getCustomTable()->Table != "")
		{
			$this->_DB->execSQL(" DELETE FROM ".$this->getCustomTable()->Table." WHERE ".$this->getUserTable()->Username ." = [[login]] ", $param);
		}
		$this->_DB->execSQL(" DELETE FROM ".$this->getUserTable()->Table." WHERE ".$this->getUserTable()->Username ." = [[login]] ", $param);
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
		$param = array("id"=>$userId);
		if ($this->getCustomTable()->Table != "")
		{
			$this->_DB->execSQL(" DELETE FROM ".$this->getCustomTable()->Table." WHERE ".$this->getUserTable()->Id." = [[id]] ", $param);
		}
		$this->_DB->execSQL(" DELETE FROM ".$this->getUserTable()->Table." WHERE ".$this->getUserTable()->Id." = [[id]] ", $param);
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
				$sql = " INSERT INTO ".$this->getCustomTable()->Table  ."( ".$this->getUserTable()->Id  .", ".$this->getCustomTable()->Name.", ".$this->getCustomTable()->Value.") ";
				$sql .=" VALUES ( [[id]], [[name]], [[value]] ) ";

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

			$sql =  " DELETE FROM ".$this->getCustomTable()->Table;
			$sql .= " WHERE ".$this->getUserTable()->Id ." = [[id]] AND ".$this->getCustomTable()->Name." = [[name]] ";
			if(!is_null($propValue))
			{
				$sql .= " AND ".$this->getCustomTable()->Value." = [[value]] ";
				$param["value"] = $propValue;
			}

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

		$this->_DB->execSQL(" DELETE FROM ".$this->getCustomTable()->Table." WHERE ".$this->getCustomTable()->Name." = [[name]] AND ".$this->getCustomTable()->Value." = [[value]] ", $param);
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
		$sql = "select * from ".$this->getCustomTable()->Table;
		$sql .= " where ".$this->getUserTable()->Id ." = [[" . $this->getUserTable()->Id . "]]";

		$param = array($this->getUserTable()->Id => $userId);
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
	public function getRolesIterator($site, $role = "")
	{
		$param = array();
		$param["site"] = $site;

		$sql = "select * from " . $this->getRolesTable()->Table .
			" where (" . $this->getRolesTable()->Site . " = [[site]] or " . $this->getRolesTable()->Site . " = '_all' ) ";

		if ($role != "")
		{
			$sql .= " and  " . $this->getRolesTable()->Role . " = [[role]] ";
			$param["role"] = $role;
		}

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

		$sql = "insert into " . $this->getRolesTable()->Table . "( " . $this->getRolesTable()->Site . ", " . $this->getRolesTable()->Role . " ) " .
			" values ( [[site]], [[role]] )";

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

		$sql = "DELETE FROM " . $this->getRolesTable()->Table .
			" WHERE " . $this->getRolesTable()->Site . " = [[site]] " .
			" AND " . $this->getRolesTable()->Role . " = [[role]] ";

		$param = array("site"=>$site, "role"=>$role);

		$this->_DB->execSQL($sql, $param);
	}

	protected function configTableNames()
	{
		parent::configTableNames();

		$this->_UserTable->Table = "xmlnuke_users";
		$this->_CustomTable->Table = "xmlnuke_custom";
		$this->_RolesTable->Table = "xmlnuke_roles";
	}

}
?>
