<?php
/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
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
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 */

abstract class BaseDBAccess
{
	/**
	* @var DBDataSet
	*/
	protected $_db = null;

	/**
	* @var Context
	*/
	protected $_context = null;

	/**
	 * Wrapper for SQLHelper
	 *
	 * @var SQLHelper
	 */
	protected $_sqlhelper = null;

	/**
	* Base Class Constructor. Don't must be override. The Context Class is required.
	*
	* @param Context $context
	*/
	public function __construct($context)
	{
		if (is_null($context))
		{
			throw new Exception("Erro de programacao: O Construtor da classe precisa receber um Engine.Context");
		}
		$this->_context = $context;
	}

	/**
	 * This method must be overrided and the return must be a valid DBDataSet name.
	 *
	 * @return string
	 */
	public abstract function getDataBaseName();

	/**
	 * Create a instance of DBDataSet to connect database
	 * @return DBDataSet
	 */
	protected function getDBDataSet()
	{
		if (is_null($this->_db))
		{
			$this->_db = new DBDataSet($this->getDataBaseName(), $this->_context);
		}

		return $this->_db;
	}

	/**
	 * Execute a SQL and dont wait for a response.
	 * @param string $sql
	 * @param string $param
	 * @param bool getId
	 */
	protected function executeSQL($sql, $param = null, $getId = false)
	{
		$dbfunction = $this->getDbFunctions();

		$debug = $this->_context->getDebugInModule();
		$start = 0;
		if ($debug)
		{
			Debug::PrintValue("<hr>");
			Debug::PrintValue("Class name: " . get_class($this));
			Debug::PrintValue("SQL: " . $sql);
			if ($param != null)
			{
				$s = "";
				foreach($param as $key=>$value)
				{
					if ($s!="")
					{
						$s .= ", ";
					}
					$s .= "[$key]=$value";
				}
				Debug::PrintValue("Params: $s");
			}
			$start = microtime(true);
		}

		if ($getId)
		{
			$id = $dbfunction->executeAndGetInsertedId($this->getDBDataSet(), $sql, $param);
		}
		else
		{
			$id = null;
			$this->getDBDataSet()->execSQL($sql, $param);
		}

		if ($debug)
		{
			$end = microtime(true);
			Debug::PrintValue("Execution time: " . ($end - $start) . " seconds ");
		}

		return $id;
	}


	/**
	 * Execulte SELECT SQL Query
	 *
	 * @param string $sql
	 * @param array $param
	 * @return IIterator
	 */
	protected function getIterator($sql, $param=null)
	{
		$this->getDBDataSet();

		$debug = $this->_context->getDebugInModule();
		$start = 0;
		if ($debug)
		{
			Debug::PrintValue("<hr>");
			Debug::PrintValue("Class name: " . get_class($this));
			Debug::PrintValue("SQL: " . $sql);
			if ($param != null)
			{
				$s = "";
				foreach($param as $key=>$value)
				{
					if ($s!="")
					{
						$s .= ", ";
					}
					$s .= "[$key]=$value";
				}
				Debug::PrintValue("Params: $s");
			}
			$start = microtime(true);
		}
		$it = $this->_db->getIterator($sql, $param);
		if ($debug)
		{
			$end = microtime(true);
			Debug::PrintValue("Execution Time: " . ($end - $start) . " segundos ");
		}
		return $it;
	}

	/**
	 * Get a SQLHelper object
	 *
	 * @return SQLHelper
	 */
	public function getSQLHelper()
	{
		$this->getDBDataSet();

		if (is_null($this->_sqlhelper))
		{
			$this->_sqlhelper = new SQLHelper($this->_db);
		}

		return $this->_sqlhelper;
	}

	/**
	 * Get an Interator from an ID. Ideal for get data from PK
	 *
	 * @param string $tablename
	 * @param string $key
	 * @param string $value
	 * @return IIterator
	 */
	protected function getIteratorbyId($tablename, $key, $value)
	{
		$sql = "select * from $tablename where $key = [[$key]] ";
		$param = array();
		$param[$key] = $value;
		return $this->getIterator($sql, $param);
	}

	/**
	 * Get an Array from an existing Iterator
	 *
	 * @param IIterator $it
	 * @param string $key
	 * @param string $value
	 * @return array()
	 */
	public static function getArrayFromIterator($it, $key, $value, $firstElement = "-- Selecione --")
	{
		$retArray = array();
		if ($firstElement != "")
		{
			$retArray[""] = $firstElement;
		}
		while ($it->hasNext())
		{
			$sr = $it->moveNext();
			$retArray[$sr->getField($key)] = $sr->getField($value);
		}
		return $retArray;
	}

	/**
	 * Get a IDbFunctions class containing specific database operations
	 * @return IDbFunctions
	 */
	public function getDbFunctions()
	{
		return $this->getDBDataSet()->getDbFunctions();
	}


	public function beginTransaction()
	{
		$this->_db->beginTransaction();
	}

	public function commitTransaction()
	{
		$this->_db->commitTransaction();
	}

	public function rollbackTransaction()
	{
		$this->_db->rollbackTransaction();
	}

}
?>
