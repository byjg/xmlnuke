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
require_once(PHPXMLNUKEDIR . 'src/Xmlnuke/Core/Classes/BaseSingleton.class.php');

class AutoLoad extends \Xmlnuke\Core\Classes\BaseSingleton
{
	const FRAMEWORK_XMLNUKE = 'FRAMEWORK_XMLNUKE';
	const USER_PROJECTS = 'USER_PROJECTS';
	
	protected function __construct()
	{
		spl_autoload_register(array($this, "autoLoad_XmlnukeFramework"));
		spl_autoload_register(array($this, "autoLoad_UserProjects"));
		
		$this->registrUserProject(PHPXMLNUKEDIR . 'src'); // For Xmlnuke.Modules.
	}

	public function registrUserProject($path)
	{
		$path = str_replace('\\', '/', $path);
		AutoLoad::$_folders[AutoLoad::USER_PROJECTS][] = 
			(substr($path, -strlen('/')) === '/' ? substr($path, 0, strlen($path)-1) : $path);
	}
	
	protected static $_folders =
		array(
			AutoLoad::FRAMEWORK_XMLNUKE =>
				array
				(
					"src/",
					"src/Xmlnuke/Library/",
					"src/modules/aws/"
				),
			AutoLoad::USER_PROJECTS => array()
		);

	// Auto Load method for Core Xmlnuke and 3rd Party
	protected function autoLoad_XmlnukeFramework($className)
	{
		foreach (AutoLoad::$_folders[AutoLoad::FRAMEWORK_XMLNUKE] as $prefix)
		{
			// PSR-0 Classes
			// convert namespace to full file path
			$class = PHPXMLNUKEDIR . $prefix . str_replace('\\', '/', $className);
			if (is_readable($class . '.class.php'))
			{
				require_once($class . '.class.php');
				break;
			}
			else if (is_readable($class . '.php'))
			{
				require_once($class . '.php');
				break;
			}
			
			// Non PSR-0 and No Namespace classes
			$filename = PHPXMLNUKEDIR . $prefix . strtolower($className) . ".class.php";
			if (is_readable($filename))
			{
				require_once $filename;
				return;
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
			if (is_readable($file . '.class.php'))
			{
				if (\Xmlnuke\Util\FileUtil::isWindowsOS() && (count(glob($file . '.*')) == 0))
					throw new \Xmlnuke\Core\Exception\EngineException(
						'The module file name "' . $className . '" does not match uppercase and lowercase. ' .
						'Your operating system supports this behavior, ' .
						'but Xmlnuke does not accept to ensure your code will run on any platform.'
					);

				require_once $file . '.class.php';
				return;
			}
		}

		return;
	}
	
}



