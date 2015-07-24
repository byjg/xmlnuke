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

namespace AWS;

use ByJG\AnyDataset\Repository\AnyDataset;
use ByJG\AnyDataset\Repository\IteratorFilter;
use Xmlnuke\Core\Enum\Relation;
use Xmlnuke\Core\Processor\AnydatasetFilenameProcessor;

class AWSAuth
{
	protected $_accessKey = "";
	protected $_secretKey = "";
	protected $_extras = array();
	protected $_isValid = false;

	/**
	 *
	 * @param string $configName
	 */
	public function __construct($configName)
	{
		$file = new AnydatasetFilenameProcessor("_aws");
		$anydata = new AnyDataset($file);
		$itf = new IteratorFilter();
		$itf->addRelation("config", Relation::Equal, $configName);

		$it = $anydata->getIterator($itf);

		if ($it->hasNext())
		{
			$sr = $it->moveNext();
			$this->_accessKey = $sr->getField("access-key");
			$this->_secretKey = $sr->getField("secret-key");
			$this->_extras = $sr->toArray();
			$this->_isValid = true;
		}

	}

	/**
	 *
	 * @return bool
	 */
	public function getIsValid()
	{
		return $this->_isValid;
	}

	/**
	 *
	 * @return string
	 */
	public function getAccessKey()
	{
		return $this->_accessKey;
	}

	/**
	 * 
	 * @return string
	 */
	public function getSecretKey()
	{
		return $this->_secretKey;
	}

	/**
	 *
	 * @param string $key
	 * @return string
	 */
	public function getExtras($key)
	{
		if (array_key_exists($key, $this->_extras))
			return $this->_extras[$key];
		else
			return "";
	}
}

?>
