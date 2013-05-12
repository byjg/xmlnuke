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

/**
 * @package xmlnuke
 */
class CachedDBDataset extends DBDataSet
{

	/**
	 *
	 * @var ICacheEngine
	 */
	protected $_cacheEngine = null;

	/**
	 *
	 * @param type $dbname
	 * @param ICacheEngine $cacheEngine
	 * @throws InvalidArgumentException
	 */
	public function __construct($dbname, $cacheEngine)
	{
		if (!($cacheEngine instanceof ICacheEngine))
		{
			throw new InvalidArgumentException("I expected ICacheEngine object");
		}
		$this->_cacheEngine = $cacheEngine;
		parent::__construct($dbname);
	}

	public function getIterator($sql, $array = null, $ttl = 600)
	{
		$key1 = md5($sql);

		if (is_array($array))
			$key2 = ":" . md5(json_encode ($array));
		else
			$key2 = "";

		$key = "qry:" . $key1 . $key2;
		$cache = $this->_cacheEngine->get($key);
		if ($cache === false)
		{
			$cache = array();
			$it = parent::getIterator($sql, $array);
			foreach ($it as $value)
			{
				$cache[] = $value->getRawFormat();
			}

			$this->_cacheEngine->set($key, $cache, $ttl);
		}

		$arrayDS = new ArrayDataSet($cache);
		return $arrayDS->getIterator();
	}

}
?>
