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
 * @package xmlnuke
 */
class DBDataSet 
{
	/**
	 * @var PDO
	 */
	protected $_db = null;
	
	protected $_context = null;
	
	/** Used for SQL Relay connections **/
	protected $_conn;
	
	/**
	 * Enter description here...
	 *
	 * @var ConnectionManagement
	 */
	protected $_connectionManagement;
	
	/**
	 *@param string $dbname - Name of file without '_db' and extention '.xml'. 
	 *@param Context $context
	 *@desc Constructor
	 */
	public function __construct($dbname, $context)
	{
		$this->_context = $context;
		
		$this->_connectionManagement = new ConnectionManagement ( $context, $dbname );
		
		if ($this->_connectionManagement->getDriver () == "sqlrelay") 
		{
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
		else
		{
			if ($this->_connectionManagement->getDriver () == "literal")
			{
				$strcnn = $this->_connectionManagement->getDbConnectionString();
			}
			else if ($this->_connectionManagement->getDriver () == "odbc")
			{
				$strcnn = $this->_connectionManagement->getDriver () . ":" . $this->_connectionManagement->getServer ();
			}
			else 
			{
				$strcnn = $this->_connectionManagement->getDriver () . ":host=" . $this->_connectionManagement->getServer () . ";dbname=" . $this->_connectionManagement->getDatabase ();
			}

			// Create Connection
			$this->_db = new PDO ( $strcnn, $this->_connectionManagement->getUsername (), $this->_connectionManagement->getPassword () );
			$this->_connectionManagement->setDriver($this->_db->getAttribute(PDO::ATTR_DRIVER_NAME));
			
			// Set Specific Attributes
			$this->_db->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			if ($this->_connectionManagement->getDriver() == "mysql")
			{
				$this->_db->setAttribute ( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true );
				if ((PHP_VERSION_ID < 50300) || (PHP_VERSION_ID > 50301))
				{
					$this->_db->setAttribute ( PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8" );
				}
			}
			if (($this->_connectionManagement->getDriver() != "dblib") && ($this->_connectionManagement->getDriver() != "odbc"))
			{
				if ( defined( 'PDO::ATTR_EMULATE_PREPARES' ) ) {
					$this->_db->setAttribute ( PDO::ATTR_EMULATE_PREPARES, true );
				}
			}
			// Solve the error:
			// SQLSTATE[HY000]: General error: 1934 General SQL Server error: Check messages from the SQL Server [1934] (severity 16) [(null)]
			// http://gullele.wordpress.com/2010/12/15/accessing-xml-column-of-sql-server-from-php-pdo/
			// http://stackoverflow.com/questions/5499128/error-when-using-xml-in-stored-procedure-pdo-ms-sql-2008
			if ($this->_connectionManagement->getDriver() == "dblib")
			{
				$this->_db->exec('SET QUOTED_IDENTIFIER ON');
				$this->_db->exec('SET ANSI_WARNINGS ON');
				$this->_db->exec('SET ANSI_PADDING ON');
				$this->_db->exec('SET ANSI_NULLS ON');
				$this->_db->exec('SET CONCAT_NULL_YIELDS_NULL ON');
				//$this->execSql('SET NUMERIC_ROUNDABORT OFF');
				//$this->execSql('set dateformat ymd');
			}
		}
	}
	
	public function getDbType() 
	{
		return $this->_connectionManagement->getDbType ();
	}
	
	public function getDbConnectionString() 
	{
		return $this->_connectionManagement->getDbConnectionString ();
	}
	
	public function TestConnection() 
	{
		return true;
	}
	
	public function __destruct() 
	{
		$this->_db = null;
		if (! is_null ( $this->_conn )) 
		{
			sqlrcon_free ( $this->_conn );
		}
	}
	
	/**
	 *@access public
	 *@param string $sql
	 *@param array $array
	 *@return IIterator
	 */
	public function getIterator($sql, $array = null) 
	{
		if ($this->_connectionManagement->getDriver () == "sqlrelay") {
			return $this->getSQLRelayIterator ( $sql, $array );
		} else {
			return $this->getDBIterator ( $sql, $array );
		}
	}
	
	protected function getDBIterator($sql, $array = null) 
	{
		if ($array) 
		{
			$sql = XmlnukeProviderFactory::ParseSQL ( $this->_connectionManagement, $sql, $array );
			$stmt = $this->_db->prepare ( $sql );
			foreach ( $array as $key => $value ) 
			{
				$stmt->bindValue ( ":" . XmlnukeProviderFactory::KeyAdj ( $key ), $value );
			}
			$result = $stmt->execute ();
		}
		else 
		{
			$stmt = $this->_db->prepare ( $sql );
			$stmt->execute ();
		}
		$it = new DBIterator ( $stmt, $this->_context );
		return $it;
	}
	
	protected function getSQLRelayIterator($sql, $array = null) 
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
		$it = new SQLRelayIterator ( $cur, $this->_context );
		return $it;
	}
	
	/**
	 *@access public
	 *@param string $tablename
	 *@return array
	 */
	public function getAllFields($tablename) 
	{
		if ($this->_connectionManagement->getDriver () == "sqlrelay") {
			return $this->getSQLRelayAllFields($tablename);
		} else {
			return $this->getDBAllFields($tablename);
		}
	}
	
	protected function getDBAllFields($tablename)
	{
		$fields = array ();
		$rs = $this->_db->query ( "select * from " . $tablename . " where 0=1" );
		$fieldLength = $rs->columnCount ();
		for($i = 0; $i < $fieldsLength; $i ++) 
		{
			$fld = $rs->getColumnMeta ( $i );
			$fields [] = strtolower ( $fld ["name"] );
			//Debug::PrintValue("<xmp>".strtolower($fld->name)." => ".$this->_rs->fields[$i]."</xmp>");
		}
		return $fields;
	}
	
	protected function getSQLRelayAllFields($tablename)
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
	
	/**
	 *@access public
	 *@param string $sql
	 *@param array $array
	 *@return Resource
	 */
	public function execSQL($sql, $array = null) 
	{
		if ($this->_connectionManagement->getDriver () == "sqlrelay") {
			return $this->execSQLRelayQuery($sql, $array);
		} else {
			return $this->execDBQuery($sql, $array);
		}
	}
	
	public function beginTransaction()
	{
		$this->_db->beginTransaction();
	}
	
	public function commitTransaction()
	{
		$this->_db->commit();
	}
	
	public function rollbackTransaction()
	{
		$this->_db->rollBack();
	}
	
	protected function execDBQuery($sql, $array = null)
	{
		if ($array) 
		{
			$sql = XmlnukeProviderFactory::ParseSQL ( $this->_connectionManagement, $sql, $array );
			$stmt = $this->_db->prepare ( $sql );
			foreach ( $array as $key => $value ) 
			{
				$stmt->bindValue ( ":" . XmlnukeProviderFactory::KeyAdj ( $key ), $value );
			}
			$result = $stmt->execute ();
		} 
		else 
		{
			$stmt = $this->_db->prepare ( $sql );
			$result = $stmt->execute ();
		}
		
		return $result;
	}

	protected function execSQLRelayQuery($sql, $array = null)
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

		return true;
	}
	
	/**
	 *@access public
	 *@param Iterator $it
	 *@param string $fieldPK
	 *@param string $fieldName
	 *@return Resource
	 */
	public function getArrayField($it, $fieldPK, $fieldName) 
	{
		$result = array ();
		//$it = $this->getIterator($sql);
		while ( $it->hasNext () ) 
		{
			$registro = $it->MoveNext ();
			$result [$registro->getField ( $fieldPK )] = $registro->getField ( $fieldName );
		}
		return $result;
	}
	
	/**
	 *@access public 
	 *@return PDO
	 */
	public function getDBConnection() 
	{
		return $this->_db;
	}
	
	/**
	 * 
	 * @var IDbFunctions
	 */
	protected $_dbFunction = null;
	
	/**
	 * Get a IDbFunctions class to execute Database specific operations.
	 * @return IDbFunctions
	 */
	public function getDbFunctions()
	{
		if ($this->_dbFunction == null)
		{
			$this->_dbFunction = PluginFactory::LoadPlugin("db" . $this->_connectionManagement->getDriver() . "functions", "database");
		}
		
		return $this->_dbFunction;
	}
}

?>
