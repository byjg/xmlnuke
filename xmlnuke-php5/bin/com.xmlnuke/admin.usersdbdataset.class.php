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
				$sql = "UPDATE ".$this->_UserTable->Table;
				$sql .= " SET ".$this->_UserTable->Name." = [[".$this->_UserTable->Name."]] ";
				$sql .= ", ".$this->_UserTable->Email." = [[".$this->_UserTable->Email."]] ";
				$sql .= ", ".$this->_UserTable->Username." = [[".$this->_UserTable->Username."]] ";
				$sql .= ", ".$this->_UserTable->Password." = [[".$this->_UserTable->Password."]] ";
				$sql .= ", ".$this->_UserTable->Created." = [[".$this->_UserTable->Created."]] ";
				$sql .= ", ".$this->_UserTable->Admin." = [[".$this->_UserTable->Admin."]] ";
				$sql .= " WHERE ".$this->_UserTable->Id." = [[".$this->_UserTable->Id . "]]";	
		
				$param = array();
				$param[$this->_UserTable->Name] = $srMod->getField($this->_UserTable->Name);
				$param[$this->_UserTable->Email] = $srMod->getField($this->_UserTable->Email);
				$param[$this->_UserTable->Username] = $srMod->getField($this->_UserTable->Username);
				$param[$this->_UserTable->Password] = $srMod->getField($this->_UserTable->Password);
				$param[$this->_UserTable->Created] = $srMod->getField($this->_UserTable->Created);
				$param[$this->_UserTable->Admin] = $srMod->getField($this->_UserTable->Admin);
				$param[$this->_UserTable->Id] = $srMod->getField($this->_UserTable->Id);
		
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
		$sql = " INSERT INTO ".$this->_UserTable->Table." (".$this->_UserTable->Name.", ".$this->_UserTable->Email.", ".$this->_UserTable->Username .", ".$this->_UserTable->Password .", ".$this->_UserTable->Created ." ) ";
		$sql .=" VALUES ([[".$this->_UserTable->Name."]], [[".$this->_UserTable->Email."]], [[".$this->_UserTable->Username ."]], [[".$this->_UserTable->Password ."]], [[".$this->_UserTable->Created ."]] ) ";			
		
		$param = array();
		$param[$this->_UserTable->Name] = $name;
		$param[$this->_UserTable->Email] = strtolower($email);
		$param[$this->_UserTable->Username] = strtolower($userName);
		$param[$this->_UserTable->Password] = $this->getSHAPassword($password);
		$param[$this->_UserTable->Created] = date("Y-m-d H:i:s");
			
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
		if (get_class($filter) == "IteratorFilter")
		{
			$sql = $filter->getSql($this->_UserTable->Table, $param);
		}
		else
		{
			$sql = "select * from ".$this->_UserTable->Table;
			if (!is_null($filter))
				$sql .= " where " . $filter;
		}
		$sql .= " order by ".$this->_UserTable->Name;
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
                $this->_cacheUserOriginal[$sr->getField($this->_UserTable->Id)] = $srOri;
                $this->_cacheUserWork[$sr->getField($this->_UserTable->Id)] = $sr;
                return $this->_cacheUserWork[$sr->getField($this->_UserTable->Id)];		
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
		if ($this->_CustomTable->Table != "")
		{
			$this->_DB->execSQL(" DELETE FROM ".$this->_CustomTable->Table." WHERE ".$this->_UserTable->Username ." = [[login]] ", $param);
		}
		$this->_DB->execSQL(" DELETE FROM ".$this->_UserTable->Table." WHERE ".$this->_UserTable->Username ." = [[login]] ", $param);
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
		if ($this->_CustomTable->Table != "")
		{
			$this->_DB->execSQL(" DELETE FROM ".$this->_CustomTable->Table." WHERE ".$this->_UserTable->Id." = [[id]] ", $param);
		}
		$this->_DB->execSQL(" DELETE FROM ".$this->_UserTable->Table." WHERE ".$this->_UserTable->Id." = [[id]] ", $param);
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
				$sql = " INSERT INTO ".$this->_CustomTable->Table  ."( ".$this->_UserTable->Id  .", ".$this->_CustomTable->Name.", ".$this->_CustomTable->Value.") ";
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
			
			$sql =  " DELETE FROM ".$this->_CustomTable->Table;
			$sql .= " WHERE ".$this->_UserTable->Id ." = [[id]] AND ".$this->_CustomTable->Name." = [[name]] ";
			if(!is_null($propValue))
			{
				$sql .= " AND ".$this->_CustomTable->Value." = [[value]] ";	
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
		
		$this->_DB->execSQL(" DELETE FROM ".$this->_CustomTable->Table." WHERE ".$this->_CustomTable->Name." = [[name]] AND ".$this->_CustomTable->Value." = [[value]] ", $param);
	}
	
	
	/**
	 * Return all custom's fields from this user
	 *
	 * @param unknown_type $userRow
	 * @return unknown
	 */
	protected function getCustomFields($userRow)
	{
		if ($this->_CustomTable->Table == "")
		{
			return null;
		}

		$userId = $userRow->getField($this->_UserTable->Id);
		$sql = "select * from ".$this->_CustomTable->Table;
		$sql .= " where ".$this->_UserTable->Id ." = [[" . $this->_UserTable->Id . "]]";
		
		$param = array($this->_UserTable->Id => $userId);
		$it = $this->_DB->getIterator($sql, $param);
		while ($it->hasNext())
		{
			$sr = $it->moveNext();
			$userRow->AddField($sr->getField($this->_CustomTable->Name), $sr->getField($this->_CustomTable->Value));
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
		
		$sql = "select * from " . $this->_RolesTable->Table . 
			" where (" . $this->_RolesTable->Site . " = [[site]] or " . $this->_RolesTable->Site . " = '_all' ) ";
			
		if ($role != "")
		{
			$sql .= " and  " . $this->_RolesTable->Role . " = [[role]] ";
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
			throw new Exception("Role exists.");
		}
		
		$sql = "insert into " . $this->_RolesTable->Table . "( " . $this->_RolesTable->Site . ", " . $this->_RolesTable->Role . " ) " .
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
		
		$sql = "DELETE FROM " . $this->_RolesTable->Table . 
			" WHERE " . $this->_RolesTable->Site . " = [[site]] " .
			" AND " . $this->_RolesTable->Role . " = [[role]] ";

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
