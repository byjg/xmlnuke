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

class PluginFactory
{

	/**
	 * Load a Plugin in RunTime mode
	 * 
	 * Example: You want to load a class called "MyClass" in namespace "My.NameSpace" and it is inside the path "plugindir" in you namespace.
	 * You have to call:
	 * 
	 * $class = PluginFactory("My.NameSpace.MyClass", "plugindir");
	 * 
	 * The system will try load the class "MyClass" inside the file:
	 * lib/my/namespace/plugindir/myclass.class.php
	 * 
	 * an Exception will be thrown if the process fail
	 * 
	 * To load a XMLNuke core class you have to call a class without namespace. The NameSpace will be in $basePath 
	 * 
	 * @param string $className
	 * @param string $basePath
	 * @param string $param1
	 * @param string $param2
	 * @param string $param3
	 * @param string $param4
	 * @return instance of class
	 */
	public static function LoadPlugin($className, $basePath = null, $param1 = null, $param2 = null, $param3 = null, $param4 = null)
	{
		return PluginFactory::LoadPluginInFile($className, "", $basePath, $param1, $param2, $param3, $param4);
	}
	
	/**
	 * Load a Plugin in RunTime mode
	 * 
	 * Example: You want to load a class called "MyLoadedClass" in namespace "My.NameSpace" and it is inside the path "plugindir" in you namespace.
	 * This class will be phisically stored in the file "MyFile".
	 * 
	 * You have to call:
	 * 
	 * $class = PluginFactory("My.NameSpace.MyFile", "MyLoadedClass", "plugindir");
	 * 
	 * The system will try load the class "MyLoadedClass" inside the file:
	 * lib/my/namespace/plugindir/MyFile.class.php
	 * 
	 * an Exception will be thrown if the process fail
	 * 
	 * To load a XMLNuke core class you have to call a class without namespace. The NameSpace will be in $basePath 
	 * 
	 * @param string $phpFile
	 * @param string $className
	 * @param string $basePath
	 * @param string $param1
	 * @param string $param2
	 * @param string $param3
	 * @param string $param4
	 * @return instance of class
	 */
	public static function LoadPluginInFile($phpFile, $className, $basePath = null, $param1 = null, $param2 = null, $param3 = null, $param4 = null)
	{
		$phpFile = strtolower($phpFile);
		$namespace = "";
		$r = strrpos($phpFile, ".");
		if ($r !== false)
		{
			$namespace = substr($phpFile, 0, $r);
			$phpFile = substr($phpFile, $r+1);
		}
		
		if ($className == "")
		{
			$className = $phpFile; 
		}

		$key = $namespace . "-" . $phpFile . "-" . $className . "-" . $basePath;

		// Try Include if not included before
		if (!array_key_exists($key, PluginFactory::$_loaded))
		{
			if (!empty($namespace))
			{
				$basePath .= (!empty($basePath) ? FileUtil::Slash() : "");
				ModuleFactory::IncludePhp($namespace, $basePath . "$phpFile.class.php");
			}
			else
			{
				@include_once(PHPXMLNUKEDIR . "bin/com.xmlnuke/$basePath.$phpFile.class.php");
			}
			
			PluginFactory::$_loaded[$key] = true;
		}
		
		// Instantiate and Execute Contructor		
		$class = new ReflectionClass($className);
		if ($param1 == null)
		{
			$plugin = $class->newInstance();	
		}
		elseif ($param2 == null)
		{
			$plugin = $class->newInstance($param1);
		}
		elseif ($param3 == null)
		{
			$plugin = $class->newInstance($param1, $param2);
		}
		elseif ($param4 == null)
		{
			$plugin = $class->newInstance($param1, $param2, $param3);
		}
		else
		{
			$plugin = $class->newInstance($param1, $param2, $param3, $param4);
		}
		

		return $plugin;
	}
	
	protected static $_loaded = array();
	
}

?>
