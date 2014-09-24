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

use MongoClient;
use MongoCollection;
use MongoDate;
use MongoDB;
use stdClass;

class MongoDBDriver implements INoSQLDriver
{	
	/**
	 * @var MongoDB
	 */
	protected $_db = null;
	
	/**
	 *
	 * @var MongoClient; 
	 */
	protected $_client = null;

	/**
	 * Enter description here...
	 *
	 * @var ConnectionManagement
	 */
	protected $_connectionManagement;

	/**
	 *
	 * @var MongoCollection MongoDB collection
	 */
	protected $_collection;

	/**
	 *
	 * @var string
	 */
	protected $_collectionName;

	/**
	 * Creates a new MongoDB connection. This class is managed from NoSQLDataSet
	 *
	 * @param ConnectionManagement $connMngt
	 * @param string $collection
	 */
	public function __construct($connMngt, $collection)
	{	
		$this->_connectionManagement = $connMngt;
		
		$hosts = $this->_connectionManagement->getServer();
		$port = $this->_connectionManagement->getPort() == '' ? 27017 : $this->_connectionManagement->getPort();
		$database = $this->_connectionManagement->getDatabase();
		$username = $this->_connectionManagement->getUsername();
		$password = $this->_connectionManagement->getPassword();

		if ($username != '' && $password != '')
			$auth = array('username'=>$username, 'password'=>$password, 'connect' => 'true');
		else
			$auth = array('connect' => 'true');

		$connecting_string =  sprintf('mongodb://%s:%d', $hosts, $port);
		$this->_client = new MongoClient($connecting_string, $auth);
		$this->_db = new MongoDB($this->_client, $database);

		$this->setCollection($collection);
	}

	/**
	 * Closes and destruct the MongoDB connection
	 */
	public function __destruct() 
	{
		$this->_client->close();
		$this->_db = null;
	}
	

	/**
	 *
	 * @return string
	 */
	public function getCollection()
	{
		return $this->_collectionName;
	}

	/**
	 * Gets the instance of MongoDB; You do not need uses this directly. If you have to, probably something is missing in this class
	 * @return \MongoDB
	 */
	public function getDbConnection()
	{
		return $this->_db;
	}

	/**
	 * Return a XMLNuke Iterator
	 * @param array $filter
	 * @param array $fields
	 * @return \Xmlnuke\Core\AnyDataset\ArrayIIterator
	 */
	public function getIterator($filter = null, $fields = null)
	{
		if (is_null($filter))
		{
			$filter = array();
		}
		if (is_null($fields))
		{
			$fields = array();
		}
		$cursor = $this->_collection->find($filter, $fields);
		$arrIt = iterator_to_array($cursor);

		return new \Xmlnuke\Core\AnyDataset\ArrayIIterator($arrIt);
	}

	/**
	 * Insert a document in the MongoDB
	 * @param mixed $document
	 * @return bool
	 */
	public function insert($document)
	{
		if (is_array($document))
		{
			$document['created_at'] = new MongoDate();
		}
		else if ($document instanceof stdClass)
		{
			$document->created_at = new MongoDate();
		}

		return $this->_collection->insert($document);
	}

	/**
	 * Defines the new Collection
	 * @param string $collection
	 */
	public function setCollection($collection)
	{
		$this->_collection = $this->_db->selectCollection($collection);
		$this->_collectionName = $collection;
	}

	/**
	 * Update a document based on your criteria
	 * @param mixed $document
	 * @param type $filter
	 * @return bool
	 */
	public function update($document, $filter = null)
	{
		//TODO: Get the document and update
		if (is_null($filter))
		{
			$filter = array();
		}
		return $this->_collection->update($filter, $document);
	}


	/*
	public function getAttribute($name)
	{
		$this->_db->getAttribute($name);
	}

	public function setAttribute($name, $value)
	{
		$this->_db->setAttribute ( $name, $value );
	}
	*/
}
