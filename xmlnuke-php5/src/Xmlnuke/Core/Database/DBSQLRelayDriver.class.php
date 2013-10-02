<?php

/*
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification and Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
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
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 */

/**
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Database;

class DBSQLRelayDriver
{
	/**
	 * Enter description here...
	 *
	 * @var ConnectionManagement
	 */
	protected $_connectionManagement;
	
	/** Used for SQL Relay connections **/
	protected $_conn;
	

	public function __construct($connMngt)
	{	
		$this->_connectionManagement = $connMngt;
		
		$this->_conn = sqlrcon_alloc ( 
				$this->_connectionManagement->getServer(), 
				$this->_connectionManagement->getPort(), 
				$this->_connectionManagement->getExtraParam("unixsocket"), 
				$this->_connectionManagement->getUsername(), 
				$this->_connectionManagement->getPassword(), 
				0, 
				1 
			);
	}
	
	public function __destruct() 
	{
		if (! is_null ( $this->_conn )) 
		{
			sqlrcon_free ( $this->_conn );
		}
	}

	protected function getSQLRelayCursor($sql, $array = null)
	{
		$cur = sqlrcur_alloc ( $this->_conn );
		$success = true;

		if ($array)
		{
			$sql = XmlnukeProviderFactory::ParseSQL ( $this->_connectionManagement, $sql, $array );

			sqlrcur_prepareQuery ( $cur, $sql );
			$bindCount = 1;
			foreach ( $array as $key => $value )
			{
				$field = strval ( $bindCount ++ );
				sqlrcur_inputBind ( $cur, $field, $value );
			}
			$success = sqlrcur_executeQuery ( $cur );
			sqlrcon_endSession ( $this->_conn );
		}
		else
		{
			$success = sqlrcur_sendQuery ( $cur, $sql );
			sqlrcon_endSession ( $this->_conn );
		}
		if (!$success)
		{
			throw new DatasetException(sqlrcur_errorMessage($cur));
		}

		return $cur;
	}

	public function getIterator($sql, $array = null)
	{
		$cur = $this->getSQLRelayCursor($sql, $array);
		$it = new SQLRelayIterator ( $cur, $this->_context );
		return $it;
	}
	
	public function getScalar($sql, $array = null)
	{
		$cur = $this->getSQLRelayCursor($sql, $array);
		$scalar = sqlrcur_getField($cur,0,0);
		sqlrcur_free($cur);

		return $scalar;
	}
	
	public function getAllFields($tablename) 
	{
		$cur=sqlrcur_alloc($this->_conn);

		$success = sqlrcur_sendQuery($cur,"select * from " . $tablename);
		sqlrcon_endSession($con);
		
		if (!$success)
		{
			throw new DatasetException(sqlrcur_errorMessage($cur));
		}

		$fields = array ();
		for ($col=0; $col<sqlrcur_colCount($cur); $col++) 
		{
			$fields[] = strtolower(sqlrcur_getColumnName($cur, $col));
		}

		sqlrcur_free($cur);
	}
	
	public function beginTransaction()
	{ }
	
	public function commitTransaction()
	{ }
	
	public function rollbackTransaction()
	{ }
	
	public function executeSql($sql, $array = null) 
	{
		$cur = $this->getSQLRelayCursor($sql, $array);
		sqlrcur_free($cur);
		return true;
	}
	
	/**
	 * 
	 * @return handle
	 */
	public function getDbConnection()
	{
		return $this->_conn;
	}
}
