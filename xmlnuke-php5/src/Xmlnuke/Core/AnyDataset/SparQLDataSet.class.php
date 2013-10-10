<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  PHP Implementation: Joao Gilberto Magalhaes
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

require_once(PHPXMLNUKEDIR . "src/modules/sparql/sparqllib.php");

/**
 * @package xmlnuke
 */
namespace Xmlnuke\Core\AnyDataset;

use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Exception\DatasetException;

class SparQLDataSet
{
	/**
	 * @var object
	 */
	private $_db;
	
	/**
	 * Enter description here...
	 *
	 * @var Context
	 */
	private $_context;

	/**
	 *
	 * @param string $json 
	 */
	public function __construct($url, $namespaces = null)
	{
		$this->_context = Context::getInstance();

		$this->_db = sparql_connect( $url );
		
		if( !$this->_db ) 
		{ 
			throw new DatasetException($this->_db->errno() . ": " . $this->_db->error());
		}
		
		if (is_array($namespaces))
		{
			foreach ($namespaces as $key => $value) 
			{
				$this->_db->ns( $key, $value );
			}
		}		
	}
	
	public function getCapabilities()
	{
		$cache = $this->_context->CachePath() . "caps.db";		
		$this->_db->capabilityCache( $cache );

		$return = array();
		
		foreach( $this->_db->capabilityCodes() as $code )
		{
			$return[$code] = array($this->_db->supports( $code ), $this->_db->capabilityDescription($code));
		}

		return $return;
	}

	/**
	*@access public
	*@param string $sql
	*@param array $array
	*@return DBIterator
	*/
	public function getIterator($sparql)
	{
		$result = $this->_db->query( $sparql ); 
		
		if( !$result ) 
		{ 
			throw new DatasetException($this->_db->errno() . ": " . $this->_db->error());
		}

		return new SparQLIterator($result);
	}
	
}
?>