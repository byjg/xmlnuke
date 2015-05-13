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
namespace Xmlnuke\Core\Engine;

// It is necessary this include, because autoload was not initiated :(
require_once(PHPXMLNUKEDIR . 'src/Xmlnuke/Core/Classes/BaseSingleton');
require_once(PHPXMLNUKEDIR . 'src/Xmlnuke/Util/FileUtil');

class AutoLoad extends \Xmlnuke\Core\Classes\BaseSingleton
{
	const FRAMEWORK_XMLNUKE = 'FRAMEWORK_XMLNUKE';
	const USER_PROJECTS = 'USER_PROJECTS';
	
	protected static $_folders = array();

	protected function __construct()
	{
		spl_autoload_register(array($this, "autoLoad_XmlnukeFramework"));
		spl_autoload_register(array($this, "autoLoad_UserProjects"));

		self::$_folders[AutoLoad::FRAMEWORK_XMLNUKE] =
				array
				(
					"src/",
					"src/Xmlnuke/Library/"
				);

		self::$_folders[AutoLoad::USER_PROJECTS] = array();
	}

	public function registrUserProject($path)
	{
		$path = str_replace('\\', '/', $path);
		AutoLoad::$_folders[AutoLoad::USER_PROJECTS][] = 
			(substr($path, -strlen('/')) === '/' ? substr($path, 0, strlen($path)-1) : $path);

		// For projects that use composer also
		$vendorDirs = glob(dirname($path) . '/vendor/*');
		foreach ($vendorDirs as $vendor)
		{
			AutoLoad::$_folders[AutoLoad::USER_PROJECTS][] = $vendor;
		}
	}
	
	// Auto Load method for Core Xmlnuke and 3rd Party
	protected function autoLoad_XmlnukeFramework($className)
	{
		foreach (AutoLoad::$_folders[AutoLoad::FRAMEWORK_XMLNUKE] as $prefix)
		{
			// PSR-0 Classes
			// convert namespace to full file path
			$class = PHPXMLNUKEDIR . $prefix . str_replace('\\', '/', $className);
			$class = (
				file_exists("$class.php")
					? "$class.php"
				    : (
						file_exists("$class.class.php")
							? "$class.class.php"
							: null
						)
					);
			
			if (!empty($class) && \Xmlnuke\Util\FileUtil::isReadable($class))
			{
				require_once($class);
				break;
			}
		}
    }
	
	// Auto Load method for User Projects (defined in config.inc.php)
	// MODULES HAVE AN SPECIAL WAY OF LOAD.
	protected function autoLoad_UserProjects($className)
	{
		$class = str_replace('\\', '/', ($className[0] == '\\' ? substr($className, 1) : $className));

		foreach (AutoLoad::$_folders[AutoLoad::USER_PROJECTS] as $libName => $libDir)
		{
			$file = $libDir . '/' . $class;
			$file = (
						file_exists("$file.php")
							? "$file.php"
							: (
								file_exists("$file.class.php")
									? "$file.class.php"
									: null
								)
					);

			if (!empty($file) && \Xmlnuke\Util\FileUtil::isReadable($file))
			{
				require_once $file;
				return;
			}
		}

		return;
	}
	
}



