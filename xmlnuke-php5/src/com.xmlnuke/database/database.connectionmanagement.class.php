<?php

/*
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
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
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 */

/**
 * @package xmlnuke
 */

/**
 * Enter description here...
 *
 */
class ConnectionManagement
{
	protected $_dbtype;
	public function setDbType($value)
	{
	    $this->_dbtype = $value;
	}

	public function getDbType()
	{
	    return $this->_dbtype;
	}

	protected $_dbconnectionstring;
	public function setDbConnectionString($value)
	{
		$this->_dbconnectionstring = $value;
	}
	public function getDbConnectionString()
	{
		return $this->_dbconnectionstring;
	}

	protected $_driver;
	public function setDriver($value)
	{
		$this->_driver = $value;
	}
	public function getDriver()
	{
		return $this->_driver;
	}

	protected $_username;
	public function setUsername($value)
	{
		$this->_username = $value;
	}
	public function getUsername()
	{
		return $this->_username;
	}

	protected $_password;
	public function setPassword($value)
	{
		$this->_password = $value;
	}
	public function getPassword()
	{
		return $this->_password;
	}

	protected $_server;
	public function setServer($value)
	{
		$this->_server = $value;
	}
	public function getServer()
	{
		return $this->_server;
	}

	protected $_port;
	public function setPort($value)
	{
		$this->_port = $value;
	}
	public function getPort()
	{
		return $this->_port;
	}

	protected $_database;
	public function setDatabase($value)
	{
		$this->_database = $value;
	}
	public function getDatabase()
	{
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

		$configFile = new AnydatasetFilenameProcessor ( "_db");
		$config = new AnyDataSet ( $configFile );
		$filter = new IteratorFilter ( );
		$filter->addRelation ( "dbname", Relation::Equal, $dbname );
		$it = $config->getIterator ( $filter );
		if (! $it->hasNext ())
		{
			throw new DataBaseException ( "Connection string " . $dbname . " not found in _db.anydata.xml config!", 1001 );
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

			$pat = "/([\w\.]+)\:\/\/([\w\.$!%&\-_]+)(?::([\w\.$!%&#\*\+=\[\]\(\)\-_]+))?@([\w\-\.]+)(?::(\d+))?\/([\w\.]+)(?:\?((?:[\w\.]+=[\w\.]+&?)*))?/i";
			$parts = preg_split ( $pat, $this->_dbconnectionstring, - 1, PREG_SPLIT_DELIM_CAPTURE );

			$this->setDriver ( $parts [1] );
			$this->setUsername ( $parts [2] );
			$this->setPassword ( $parts [3] );
			$this->setServer ( $parts [4] );
			$this->setPort ( $parts [5] );
			$this->setDatabase ( $parts [6] );

			if ($parts[7] != null)
			{
				$arrAux = explode('&', $parts[7]);
				foreach($arrAux as $item)
				{
					$aux = explode("=", $item);
					$this->addExtraParam($aux[0], $aux[1]);
				}
			}

			$user = $parts [2];
			$pass = $parts [4];
		}
		else if ( $this->getDbType() == "literal" )
		{
			$parts = explode("|", $this->_dbconnectionstring);
			$this->_dbconnectionstring = $parts[0];
			$this->setUsername($parts[1]);
			$this->setPassword($parts[2]);
		}
		else if ($this->_dbconnectionstring != "")
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


?>