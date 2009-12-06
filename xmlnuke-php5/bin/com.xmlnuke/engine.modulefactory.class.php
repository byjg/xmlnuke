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

		$basePath = "";
		$className = $modulename;

		// ------------------------------------------------------------------------------------
		// Try to Load a XMLNuke module
		if ( (strpos($modulename, ".") === false) || (substr($modulename,0,6) == "admin.") )
		{
			if (substr($modulename,0,6) == "admin.")
			{
				$basePath = "admin";
				$className = substr($modulename, 6);
			}
			else
			{
				$basePath = "module";
				$className = $modulename;
			}
		}
		// ------------------------------------------------------------------------------------
		// Try to Load a user generated module
		else
		{
			$r = strrpos($modulename, ".");
			$className = substr($modulename, 0, $r) . ".modules" . substr($modulename, $r);
			$basePath = "";
		}

		try
		{
			$result = PluginFactory::LoadPlugin($className, $basePath);
		}
		catch (ReflectionException $e)
		{
			$r = strrpos($modulename, ".");
			$onlyModule = substr($modulename, ($r===false ? 0 : $r+1));
			if (strpos($e->getMessage(), " $onlyModule ") === false)
			{
				throw $e;
			}
			else
			{
				throw new NotFoundException("Module name '$modulename' not found.");
			}
		}
		catch (NotFoundClassException $e)
		{
			$r = strrpos($modulename, ".");
			$onlyModule = substr($modulename, ($r===false ? 0 : $r+1));
			if (strpos($e->getMessage(), $onlyModule . ".class.php") === false)
			{
				throw $e;
			}
			else
			{
				throw new NotFoundException("Module name '$modulename' not found.");
			}
		}

		$xml = new XMLFilenameProcessor($modulename, $context);
		$result->Setup($xml, $context, $o);

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

	protected static function GetLibDir($key)
	{
		if (ModuleFactory::$_phpLibDir != "")
		{
			if (array_key_exists($key, ModuleFactory::$_phpLibDir))
			{
				return ModuleFactory::$_phpLibDir[$key];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return "??";
		}
	}

	protected static function SetLibDir($key, $path)
	{
		ModuleFactory::$_phpLibDir[$key] = $path;
		$_SESSION["SESS_XMLNUKE_PHPLIBDIR"] = ModuleFactory::$_phpLibDir;
	}

	/**
	 *
	 * @param Context $context
	 * @return array()
	 */
	public static function PhpLibDir($context)
	{
		if (ModuleFactory::$_phpLibDir == null)
		{
			if ( (empty($_SESSION["SESS_XMLNUKE_PHPLIBDIR"])) || ($context->getNoCache()) || ($context->getReset()) )
			{
				$phpLibDir = $context->ContextValue("xmlnuke.PHPLIBDIR");
				if ($phpLibDir != "")
				{
					$phpLibDirArray = explode("|", $phpLibDir);
					foreach ($phpLibDirArray as $phpLibItem)
					{
						$phpLib = explode("=", $phpLibItem);
						ModuleFactory::$_phpLibDir[$phpLib[0]] = $phpLib[1];
						//set_include_path(get_include_path() . PATH_SEPARATOR . $phpLib[1]);
					}
				}
				$_SESSION["SESS_XMLNUKE_PHPLIBDIR"] = ModuleFactory::$_phpLibDir;
			}
			else
			{
				ModuleFactory::$_phpLibDir = $_SESSION["SESS_XMLNUKE_PHPLIBDIR"];
			}
		}
		return ModuleFactory::$_phpLibDir;
	}


	public static function LibPath($namespaceBase, $path = "")
	{
		if ($path == "")
		{
			$i = strrpos($namespaceBase, ".");
			if ($i !== false)
			{
				$path = substr($namespaceBase, $i+1) . ".class.php";
				$namespaceBase = substr($namespaceBase, 0, $i);
			}
		}

		if (ModuleFactory::$_phpLibDir != null)
		{
			if (ModuleFactory::GetLibDir($namespaceBase))
			{
				$filePath = ModuleFactory::GetLibDir($namespaceBase);
				if ($filePath[strlen($filePath)-1] != FileUtil::Slash())
				{
					$filePath .= FileUtil::Slash();
					ModuleFactory::SetLibDir($namespaceBase, $filePath);
				}
			}
			else
			{
				$auxArBase = explode(".", $namespaceBase);
				$end = sizeof($auxArBase);
				$namespace = "";
				while($i++<$end)
				{
					$namespace .= ($namespace != "" ? "." : "") . array_shift($auxArBase);
					$namespacePath = ModuleFactory::GetLibDir($namespace);
					if ($namespacePath != "")
					{
						$filePath = $namespacePath . FileUtil::Slash() . implode(FileUtil::Slash(), $auxArBase) . (sizeof($auxArBase) > 0 ? FileUtil::Slash() : "");
						ModuleFactory::SetLibDir($namespaceBase, $filePath);
						break;
					}
				}
			}
		}

		if ($filePath == "")
		{
			$filePath = "lib" . FileUtil::Slash() . str_replace(".", FileUtil::Slash(), $namespaceBase) . FileUtil::Slash();
			ModuleFactory::SetLibDir($namespaceBase, $filePath);
		}

		return $filePath . $path;
	}

	public static function IncludePhp($namespaceBase, $path = "")
	{
		$filePath = ModuleFactory::LibPath($namespaceBase, $path);

		if (file_exists($filePath))
		{
			include_once($filePath);
		}
		else
		{
			throw new NotFoundClassException("Include file '$filePath' does not found. Namespace: $namespaceBase. Can't continue.");
		}
	}
}
?>
