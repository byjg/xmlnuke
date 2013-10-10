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

namespace Xmlnuke\Core\Engine;

use Xmlnuke\Core\Enum\SSLAccess;
use Xmlnuke\Core\Exception\NotAuthenticatedException;
use Xmlnuke\Core\Module\IModule;
use Xmlnuke\Core\Processor\XMLFilenameProcessor;

/**
 * Locate and create custom user modules.
 * All modules must follow these rules:
 
 * <ul>
 * <li>implement IModule interface or inherit from BaseModule (recommended); </li>
 * <li>Compile into XMLNuke engine or have the file name com.xmlnuke.module.[modulename]; </li>
 * <li>Have com.xmlnuke.module namespace. </li>
 * </ul>
 * 
 * @package xmlnuke
 */
class ModuleFactory
{
	/**
	 * Doesn't need constructor because all methods are statics.
	 */
	public function __construct()
	{}

	/**
		* Locate and create custom module if exists. Otherwise throw exception.
		*
		* @param string $modulename
		* @param object $o
		* @return IModule
		*/
	public static function GetModule($modulename, $o = null)
	{
		// Module name options:
		// <xmlnukedir>/src/com.xmlnuke/module/module.<modulename>.class.php
		//  - or -
		// <userdir>/lib/<subdir>/<modulename>.class.php
		//  - or -
		// <xmlnukedir>/modules/<subdir>/<modulename>.class.php
		
		$context = Context::getInstance();
		
		$modulename = preg_replace('/^(xmlnuke\.)(.*)$/i', 'Xmlnuke.Modules.\2', $modulename);
		
		$basePath = "";
		$classNameAr = explode('.', $modulename);
		if (strpos($modulename, '.Modules.') === false)
			array_splice( $classNameAr, count($classNameAr)-1, 0, array('Modules') );
		$className = '\\' . implode('\\', $classNameAr);
		
		$result = new $className;

		/* TODO
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

		$result = PluginFactory::LoadPlugin($className, $basePath);
		*/
		
		$xml = new XMLFilenameProcessor($modulename);
		$result->Setup($xml, $o);

		$urlSSL = "";
		$isHttps = ( ($context->Value("HTTPS") == "on") || ($context->Value("HTTP_X_FORWARDED_PROTO") == "https") );
		$requireSSL = $result->requiresSSL();

		if ( ($requireSSL == SSLAccess::ForcePlain) && $isHttps )
		{
			$urlSSL = "http://" . $context->ContextValue("HTTP_HOST") . $context->ContextValue("REQUEST_URI");
		}
		else if ( ($requireSSL == SSLAccess::ForceSSL) && !$isHttps )
		{
			$urlSSL = "https://" . $context->ContextValue("HTTP_HOST") . $context->ContextValue("REQUEST_URI");
		}

		if (strlen($urlSSL) > 0)
		{
			if ($context->ContextValue("REQUEST_METHOD") == "GET")
				$context->redirectUrl($urlSSL);
			else
			{
				echo "<html><body>";
				echo "<div style='font-family: arial; font-size: 14px; background-color: lightblue; line-height: 24px; width: 80px; text-align: center'>Switching...</div>";
				echo '<form action="' . $urlSSL . '" method="post">';
				foreach ($_POST as $key=>$value)
				{
					echo "<input type='hidden' name='$key' value='$value' />";
				}
				echo "<script language='JavaScript'>document.forms[0].submit()</script>";
				echo "</body></html>";
				die();
			}
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


	private static $_phpLibDir = array();

	/**
	 *
	 * @return array()
	 */
	public static function registerUserLibDir($context)
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
			
			foreach(ModuleFactory::$_phpLibDir as $lib => $path)
				AutoLoad::getInstance()->registrUserProject($lib, $path);
		}
		return ModuleFactory::$_phpLibDir;
	}
}
?>
