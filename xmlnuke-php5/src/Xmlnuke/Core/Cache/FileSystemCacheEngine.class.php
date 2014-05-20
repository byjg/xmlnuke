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

use Exception;
use Xmlnuke\Core\Classes\BaseSingleton;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Processor\CacheFilenameProcessor;
use Xmlnuke\Util\FileUtil;
use Xmlnuke\Util\LogWrapper;

class  FileSystemCacheEngine extends BaseSingleton implements ICacheEngine
{
	/**
	 *
	 * @var Context
	 */
	protected $_context = null;	

	protected function __construct()
	{ 
		$this->_context = Context::getInstance();
	}

	/**
	 * @param string $key The object KEY
	 * @param int $ttl IGNORED IN MEMCACHED.
	 * @return object Description
	 */
	public function get($key, $ttl = 0)
	{
		$log = LogWrapper::getLogger("cache.filesystemcacheengine");

		if ($ttl === false)
		{
			$log->trace("[Cache] Ignored  $key because TTL=FALSE");
			return false;
		}

		if ($this->_context->getReset())
		{
			$log->trace("[Cache] Failed to get $key because RESET=true");
			return false;
		}
		if ($this->_context->getNoCache())
		{
			$log->trace("[Cache] Failed to get $key because NOCACHE=true");
			return false;
		}

		// Check if file is Locked
		$fileKey = $this->fixKey($key);
		$lockFile = $fileKey . ".lock";
		if (file_exists($lockFile))
		{
			$log->trace("[Cache] Locked! $key. Waiting...");
			$lockTime = filemtime($lockFile);

			while(true)
			{
				if (!file_exists($lockFile))
				{
					$log->trace("[Cache] Lock released for '$key'");
					break;
				}
				if (intval(time() - $lockTime) > 20)  // Wait for 10 seconds
				{
					$log->trace("[Cache] Gave up to wait unlock. Release lock for '$key'");
					$this->unlock($key);
					return false;
				}
				sleep(1); // 1 second
			}
		}

		// Check if file exists
		if (file_exists($fileKey))
		{
			$fileAge = filemtime($fileKey);

			if (($ttl > 0) && (intval(time() - $fileAge) > $ttl))
			{
				$log->trace("[Cache] File too old. Ignoring '$key'");
				return false;
			}
			else
			{
				$log->trace("[Cache] Get '$key'");
				return unserialize(FileUtil::QuickFileRead($fileKey));
			}
		}
		else
		{
			$log->trace("[Cache] Not found '$key'");
			return false;
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
		$log = LogWrapper::getLogger("cache.filesystemcacheengine");

		$fileKey = $this->fixKey($key);

		if (!$this->_context->getNoCache())
		{
			$log->trace("[Cache] Set '$key' in FileSystem");

			try
			{
				if (FileUtil::Exists($fileKey))
					FileUtil::DeleteFileString ($fileKey);

				if (is_string($object) && (strlen($object) == 0))
					touch($fileKey);
				else
					FileUtil::QuickFileWrite($fileKey, serialize($object));
			}
			catch (Exception $ex)
			{
				echo "<br/><b>Warning:</b> I could not write to cache on file '" . basename($key) . "'. Switching to nocache=true mode. <br/>";
			}
		}
		else
		{
			$log->trace("[Cache] Not Set '$key' because NOCACHE=true");
		}
	}

	/**
	 * @param string $key The object Key
	 * @param object $object The object to be cached
	 * @param int $ttl The time to live in seconds of this objects
	 * @return bool If the object is successfully posted
	 */
	public function append($key, $content, $ttl = 0)
	{
		$log = LogWrapper::getLogger("cache.filesystemcacheengine");

		$fileKey = $this->fixKey($key);

		if (!$this->_context->getNoCache())
		{
			$log->trace("[Cache] Append '$key' in FileSystem");

			try
			{
				FileUtil::QuickFileWrite($fileKey, serialize($content), true);
			}
			catch (Exception $ex)
			{
				echo "<br/><b>Warning:</b> I could not write to cache on file '" . basename($key) . "'. Switching to nocache=true mode. <br/>";
			}
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
		$log = LogWrapper::getLogger("cache.filesystemcacheengine");
		$log->trace("[Cache] Lock '$key'");

		$lockFile = $this->fixKey($key) . ".lock";

		try
		{
			FileUtil::QuickFileWrite($lockFile, date('c'));
		}
		catch (Exception $ex)
		{
			// Ignoring... Set will cause an error
		}
	}

	/**
	 * UnLock resource after set it.
	 * @param string $key
	 */
	public function unlock($key)
	{
		$log = LogWrapper::getLogger("cache.filesystemcacheengine");
		$log->trace("[Cache] Unlock '$key'");

		$lockFile = $this->fixKey($key) . ".lock";
		
		if (file_exists($lockFile))
			FileUtil::DeleteFileString($lockFile);
	}

	protected function fixKey($key)
	{
		$cache = new CacheFilenameProcessor($key);
		$cachePathName = $cache->FullQualifiedNameAndPath();

		return $cachePathName;
	}
}
?>
