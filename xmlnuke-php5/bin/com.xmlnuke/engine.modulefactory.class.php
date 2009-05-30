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
* Locate and create custom user modules. 
* All modules must follow these rules: 
* <ul>
* <li>implement IModule interface or inherit from BaseModule (recommended); </li>
* <li>Compile into XMLNuke engine or have the file name com.xmlnuke.module.[modulename]; </li>
* <li>Have com.xmlnuke.module namespace. </li>
* </ul>
*/
class ModuleFactory
{
	/**
	* Doesn't need constructor because all methods are statics.
	*/
	public function ModuleFactory()
	{}

	/**
		* Locate and create custom module if exists. Otherwise throw exception.
		*
		* @param string $modulename
		* @param Context $context
		* @param object $o
		* @return IModule
		*/
	public static function GetModule($modulename, $context, $o)
	{
		// Module name options:
		// <xmlnukedir>/bin/com.xmlnuke/module.<modulename>.class.php
		//  - or -
		// <xmlnukedir>/lib/<subdir>/<modulename>.class.php
		//  - or -
		// <xmlnukedir>/modules/<subdir>/<modulename>.class.php

		$modulename = strtolower($modulename);
		$loaded = false;

		// ------------------------------------------------------------------------------------
		// Try to Load a XMLNuke module
		if ( (strpos($modulename, ".") === false) || (substr($modulename,0,6) == "admin.") )
		{
			if (substr($modulename,0,6) == "admin.")
			{
				$prefix = "admin.";
				$module = substr($modulename,6);
			}
			else
			{
				$prefix = "module.";
				$module = $modulename;
			}

			$path = PHPXMLNUKEDIR . strtolower("bin".FileUtil::Slash()."com.xmlnuke".FileUtil::Slash());
			$loaded = ModuleFactory::tryLoadModule($path, $prefix.$module);
		}
		// ------------------------------------------------------------------------------------
		// Try to Load a user generated module
		else
		{
			$path = "lib" . FileUtil::Slash();
			$arr = explode('.',$modulename);
			$moduledir = "";
			for ($i=0;$i<sizeof($arr)-1; $i++)
			{
				$moduledir .= $arr[$i] . FileUtil::Slash();
			}
			$path .= $moduledir."modules".FileUtil::Slash();
			$module = $arr[sizeof($arr)-1];
			
			if (!ModuleFactory::tryLoadModule($path, $module))
			{
				// ------------------------------------------------------------------------------------
				// Try to Load a user generated module located in PHPLIBDIR
				$arr = ModuleFactory::PhpLibDir($context);
				
				foreach ($arr as $key=>$value) 
				{
					if (strpos($modulename, $key . ".") !== FALSE)
					{
						$path = $value . FileUtil::Slash();
						$module = str_replace($key . ".", "", $modulename);

						$arr = explode('.',$module);
						$moduledir = "";
						for ($i=0;$i<sizeof($arr)-1; $i++)
						{
							$moduledir .= $arr[$i] . FileUtil::Slash();
						}
						$path .= $moduledir."modules".FileUtil::Slash();
						$module = $arr[sizeof($arr)-1];
						
						$loaded = ModuleFactory::tryLoadModule($path, $module);
						break;
					}
				}
			}
			else 
			{
				$loaded = true;
			}
		}

		if (!$loaded)
		{
			throw new NotFoundException("Module \"$modulename\" not found.");
		}

		try
		{
			$class = new ReflectionClass($module);
			$result = $class->newInstance();
			$xml = new XMLFilenameProcessor($modulename, $context);
			$result->Setup($xml, $context, $o);
		}
		catch (Exception $e)
		{
			throw new NotFoundException($e->getMessage());
		}

		if ($result->requiresAuthentication())
		{
			if (!$context->IsAuthenticated())
			{
				throw new NotAuthenticatedException("You need login to access this feature");
			}
			else
			{
				if (!$result->accessGranted())
				{
					$result->processInsufficientPrivilege();
				}
			}
		}
		return $result;
	}
	
	
	

	/**
	 * Try include module class library to load it
	 *
	 * @param string $pathRoot
	 * @param string $module
	 * @return bool
	 */
	private static function tryLoadModule($pathRoot, $module)
	{
		$moduleToLoad = "$pathRoot$module.class.php";
		
		$found = file_exists($moduleToLoad);
		if ($found)
		{
			include_once($moduleToLoad);
		}
		return $found;
	}
	
	private static $_phpLibDir = array();
	
	/**
	 * 
	 * @param Context $context
	 * @return array()
	 */
	public static function PhpLibDir($context)
	{
		if (ModuleFactory::$_phpLibDir == null)
		{
			$phpLibDir = $context->ContextValue("xmlnuke.PHPLIBDIR");
			if ($phpLibDir != "")
			{
				$phpLibDirArray = explode("|", $phpLibDir);
				foreach ($phpLibDirArray as $phpLibItem) 
				{
					$phpLib = explode("=", $phpLibItem);
					ModuleFactory::$_phpLibDir[$phpLib[0]] = $phpLib[1];
					set_include_path(get_include_path() . PATH_SEPARATOR . $phpLib[1]);
				}			
			}
		}
		return ModuleFactory::$_phpLibDir;
	}

	public static function LibPath($namespaceBase, $path)
	{
		if (ModuleFactory::$_phpLibDir != null)
		{
			$namespacePath = ModuleFactory::$_phpLibDir[$namespaceBase]; 
			if ($namespacePath != "")
			{
				$filePath = $namespacePath . FileUtil::Slash();	
			}
		}
		
		if ($filePath == "")
		{
			$filePath = "lib" . FileUtil::Slash() . str_replace(".", FileUtil::Slash(), $namespaceBase) . FileUtil::Slash();	
		}
		
		return $filePath . $path;
	}
	
	public static function IncludePhp($namespaceBase, $path)
	{
		$filePath = ModuleFactory::LibPath($namespaceBase, $path);
		
		if (file_exists($filePath))
		{
			include_once($filePath);
		}
		else
		{
			throw new XMLNukeException("Include file '$filePath' does not found. Namespace: $namespaceBase. Can't continue.");			 
		}
	}
}
?>
