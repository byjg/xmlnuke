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

class DBDataSet {
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
	public function DBDataSet($dbname, $context) 
	{
		$this->_context = $context;
		
		$this->_connectionManagement = new ConnectionManagement ( $context, $dbname );
		
		if ($this->_connectionManagement->getDriver () == "sqlrelay") 
		{
			$this->_conn = sqlrcon_alloc ( $this->_connectionManagement->getServer(), $this->_connectionManagement->getPort(), $this->_connectionManagement->getExtraParam("unixsocket"), $this->_connectionManagement->getUsername(), $this->_connectionManagement->getPassword(), 0, 1 );
		}
		else
		{
			if ($this->_connectionManagement->getDriver () == "literal")
			{
				$strcnn = $this->_connectionManagement->getServer();
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
			throw new Exception(sqlrcur_errorMessage($cur));
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
			throw new Exception(sqlrcur_errorMessage($cur));
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
			$stmt->execute ();
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
			throw new Exception(sqlrcur_errorMessage($cur));
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




/**
 * Enter description here...
 *
 */
class ConnectionManagement {
	protected $_dbtype;
	public function setDbType($value) {
		$this->_dbtype = $value;
	}
	public function getDbType() {
		return $this->_dbtype;
	}
	
	protected $_dbconnectionstring;
	public function setDbConnectionString($value) {
		$this->_dbconnectionstring = $value;
	}
	public function getDbConnectionString() {
		return $this->_dbconnectionstring;
	}
	
	protected $_driver;
	public function setDriver($value) {
		$this->_driver = $value;
	}
	public function getDriver() {
		return $this->_driver;
	}
	
	protected $_username;
	public function setUsername($value) {
		$this->_username = $value;
	}
	public function getUsername() {
		return $this->_username;
	}
	
	protected $_password;
	public function setPassword($value) {
		$this->_password = $value;
	}
	public function getPassword() {
		return $this->_password;
	}
	
	protected $_server;
	public function setServer($value) {
		$this->_server = $value;
	}
	public function getServer() {
		return $this->_server;
	}
	
	protected $_port;
	public function setPort($value) {
		$this->_port = $value;
	}
	public function getPort() {
		return $this->_port;
	}
	
	protected $_database;
	public function setDatabase($value) {
		$this->_database = $value;
	}
	public function getDatabase() {
		return $this->_database;
	}
	
	protected $_extraParam = array();
	public function addExtraParam($key, $value)
	{
		$this->_extraParam[$key] = $value;
	}
	public function getExtraParam($key)
	{
		return $this->_extraParam[$key];
	}
	
	/**
	 * Enter description here...
	 *
	 * @var Context
	 */
	protected $_context;
	
	public function __construct($context, $dbname) 
	{
		$this->_context = $context;
		
		$configFile = new AnydatasetFilenameProcessor ( "_db", $context );
		$config = new AnyDataSet ( $configFile );
		$filter = new IteratorFilter ( );
		$filter->addRelation ( "dbname", Relation::Equal, $dbname );
		$it = $config->getIterator ( $filter );
		if (! $it->hasNext ()) 
		{
			throw new DataBaseException ( 1001, "Connection string " . $dbname . " not found in _db.anydata.xml config!" );
		}
		
		$data = $it->moveNext ();
		
		$this->setDbType ( $data->getField ( "dbtype" ) );
		$this->setDbConnectionString ( $data->getField ( "dbconnectionstring" ) );
		$this->addExtraParam("unixsocket", $data->getField("unixsocket") );
		$this->addExtraParam("parammodel", $data->getField("parammodel"));
		
		if ($this->getDbType () == 'dsn') 
		{
			/*
		    [0] => --IGNORE--
		    [1] => DRIVER
		    [2] => USERNAME
		    [3] => PASSWORD
		    [4] => SERVER
		    [5] => PORT
		    [6] => DATABASE
		    [7] => PARAMETERS (NOTUSED!)
		    
		    DSN=DRIVER://USERNAME[:PASSWORD]@SERVER/DATABASE[?PARAMETERS]
    		*/
			
			$pat = "/([\w\.]+)\:\/\/([\w\.$!%&]+)(?::([\w\.$!%&]+))?@([\w\.]+)(?::(\d+))?\/([\w\.]+)/i";
			$parts = preg_split ( $pat, $this->_dbconnectionstring, - 1, PREG_SPLIT_DELIM_CAPTURE );
			
			$this->setDriver ( $parts [1] );
			$this->setUsername ( $parts [2] );
			$this->setPassword ( $parts [3] );
			$this->setServer ( $parts [4] );
			$this->setPort ( $parts [5] );
			$this->setDatabase ( $parts [6] );
			
			$user = $parts [2];
			$pass = $parts [4];
		}
		else if ($this->_dbconnectionstring != "") 
		{
			if ( $this->getDbType() == "literal" )
			{
				$connection_string = explode( "|", $this->_dbconnectionstring );
				$this->setDriver ( $this->getDbType () );
				$this->setUsername ( $connection_string [1] );
				$this->setPassword ( $connection_string [2] );
				$this->setServer ( $connection_string [0] );
			}
			else
			{
				$connection_string = explode( ";", $this->_dbconnectionstring );
				$this->setDriver ( $this->getDbType () );
				$this->setUsername ( $connection_string [1] );
				$this->setPassword ( $connection_string [2] );
				$this->setServer ( $connection_string [0] );
				$this->setDatabase ( $connection_string [3] );
			}
		}
	}

}







/**
 * Class to create and manipulate Several Data Types
 *
 */
class XmlnukeProviderFactory 
{
	/**
	 * Each provider have your own model for pass parameter. This method define how each provider name define the parameters
	 *
	 * @param ConnectionManagement $connData
	 * @return string
	 */
	public function GetParamModel($connData) 
	{
		if ($connData->getExtraParam("parammodel") != "")
		{
			return $connData->getExtraParam("parammodel");
		}
		elseif ($connData->getDriver() == "sqlrelay") 
		{
			return "?";
		}
		else 
		{
			return ":_";
		}
	}
	
	/**
	 * Transform generic parameters [[PARAM]] in a parameter recognized by the provider name based on current DbParameter array.
	 *
	 * @param ConnectionManagement $connData
	 * @param string $SQL
	 * @param array $param
	 * @return string
	 */
	public static function ParseSQL($connData, $SQL, $params) 
	{
		$paramSubstName = XmlnukeProviderFactory::GetParamModel ( $connData );
		foreach ( $params as $key => $value ) 
		{
			$arg = str_replace ( "_", XmlnukeProviderFactory::KeyAdj ( $key ), $paramSubstName );
			$SQL = str_replace ( "[[" . $key . "]]", $arg, $SQL );
		}
		
		return $SQL;
	}
	
	public static function KeyAdj($key) 
	{
		return str_replace ( ".", "_", $key );
	}

}

?>
