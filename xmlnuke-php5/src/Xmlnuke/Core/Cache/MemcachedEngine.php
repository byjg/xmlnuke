<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A XML site content management.
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


namespace Xmlnuke\Core\Cache;

use InvalidArgumentException;
use Memcached;
use Xmlnuke\Core\AnyDataset\AnyDataSet;
use Xmlnuke\Core\AnyDataset\IteratorFilter;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Enum\Relation;
use Xmlnuke\Core\Processor\AnydatasetFilenameProcessor;
use Xmlnuke\Util\LogWrapper;


class  MemcachedEngine implements ICacheEngine
{
	use \ByJG\DesignPattern\Singleton;
	
	/**
	 *
	 * @var Context
	 */
	protected $_context = null;

	/**
	 *
	 * @var Memcached
	 */
	protected $_memCached = null;

	protected $_debug = false;

	protected $_L1Cache = array();

	protected function __construct()
	{
		$this->_context = Context::getInstance();

		$anyproc = new AnydatasetFilenameProcessor("_cacheengine");
		$anydata = new AnyDataSet($anyproc);

		$itf = new IteratorFilter();
		$itf->addRelation("engine", Relation::Equal, get_class($this));
		$it = $anydata->getIterator($itf);

		if ($it->hasNext())
		{
			$this->_memCached = new Memcached();

			$sr = $it->moveNext();
			$servers = $sr->getFieldArray("server");
			foreach ($servers as $server)
			{
				$data = explode(":", $server);
				$this->_memCached->addServer($data[0], $data[1]);
			}
		}
		else
		{
			throw new InvalidArgumentException("You have to configure the memcache in the file _cacheengine.anydata.xml");
		}

		$this->_debug = $this->_context->getDebugStatus();
	}

	/**
	 * @param string $key The object KEY
	 * @param int $ttl IGNORED IN MEMCACHED.
	 * @return object Description
	 */
	public function get($key, $ttl = 0)
	{
		$log = LogWrapper::getLogger("cache.memcachedengine");
		if ($this->_context->getReset())
		{
			$log->trace("[Cache] Get $key failed because RESET=true");
			return false;
		}
		
		if ($this->_context->getNoCache())
		{
			$log->trace("[Cache] Failed to get $key because NOCACHE=true");
			return false;
		}

		if (array_key_exists($key, $this->_L1Cache))
		{
			$log->trace("[Cache] Get '$key' from L1 Cache");
			return $this->_L1Cache[$key];
		}

		$value = $this->_memCached->get($key);
		if ($this->_memCached->getResultCode() == Memcached::RES_NOTFOUND)
		{
			$log->trace("[Cache] Not found '$key'");
			return false;
		}
		else
		{
			$log->trace("[Cache] Get '$key' from Memcached");
			$this->_L1Cache[$key] = $value;
			return $value;
		}
		
	}

	/**
	 * @param string $key The object Key
	 * @param object $object The object to be cached
	 * @param int $ttl The time to live in seconds of this objects
	 * @return bool If the object is successfully posted
	 */
	public function set($key, $object, $ttl = 0)
	{
		$log = LogWrapper::getLogger("cache.memcachedengine");

		if (!$this->_context->getNoCache())
		{
			$log->trace("[Cache] Set '$key' in Memcached");
			$this->_L1Cache[$key] = $object;
			return $this->_memCached->set($key, $object, $ttl);
		}
		else
		{
			$log->trace("[Cache] Not Set '$key' because NOCACHE=true");
			return true;
		}
	}

	/**
	 * Unlock resource
	 * @param string $key
	 */
	public function release($key)
	{
		unset($this->_L1Cache[$key]);
		$this->_memCached->delete($key);
	}

	/**
	 *
	 * @param string $key
	 * @param string $str
	 * @return bool
	 */
	public function append($key, $str)
	{
		$log = LogWrapper::getLogger("cache.memcachedengine");

		if (!$this->_context->getNoCache())
		{
			$log->trace("[Cache] Append '$key' in Memcached");

			$this->_L1Cache[$key] = $this->_L1Cache[$key] . $str;

			return $this->_memCached->append($key, $str);
		}
		else
		{
			$log->trace("[Cache] Not Set '$key' because NOCACHE=true");
		}
			
	}

	/**
	 * Lock resource before set it.
	 * @param string $key
	 */
	public function lock($key)
	{
		return;
	}

	/**
	 * UnLock resource after set it
	 * @param string $key
	 */
	public function unlock($key)
	{
		return;
	}

}
?>