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

use InvalidArgumentException;
use Xmlnuke\Core\Enum\AuthMode;
use Xmlnuke\Core\Enum\SSLAccess;
use Xmlnuke\Core\Exception\NotAuthenticatedException;
use Xmlnuke\Core\Exception\ModuleNotFoundException;
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
	 * Important:
	 *   A module must reside on a folder named 'Modules'.
	 *   You can call a module by \namespace\Modules\ModuleName or just \namespace\ModuleName
	 *
	 * @param string $modulename
	 * @param object $o
	 * @return IModule
	 */
	public static function GetModule($modulename, $o = null)
	{		
		$context = Context::getInstance();
		
		$basePath = "";
		$classNameAr = explode('.', $modulename);
		if (strpos($modulename, '.Modules.') === false)
			array_splice( $classNameAr, count($classNameAr)-1, 0, array('Modules') );
		$className = '\\' . implode('\\', $classNameAr);
		
		if (class_exists($className, true))
			$result = new $className;
		else
			throw new ModuleNotFoundException("Module \"$modulename\" not found");

		if (!($result instanceof IModule))
			throw new InvalidArgumentException('Class "' . $className . '" is not a IModule object');

		// ----------------------------------------------------------
		// Activate the Module
		// ----------------------------------------------------------

		$xml = new XMLFilenameProcessor($modulename);
		$result->Setup($xml, $o);

		$urlSSL = "";
		$isHttps = ( ($context->get("HTTPS") == "on") || ($context->get("HTTP_X_FORWARDED_PROTO") == "https") );
		$requireSSL = $result->requiresSSL();

		if ( ($requireSSL == SSLAccess::ForcePlain) && $isHttps )
		{
			$urlSSL = "http://" . $context->get("HTTP_HOST") . $context->get("REQUEST_URI");
		}
		else if ( ($requireSSL == SSLAccess::ForceSSL) && !$isHttps )
		{
			$urlSSL = "https://" . $context->get("HTTP_HOST") . $context->get("REQUEST_URI");
		}

		if (strlen($urlSSL) > 0)
		{
			if ($context->get("REQUEST_METHOD") == "GET")
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
			if ($result->getAuthMode() == AuthMode::Form && !$context->IsAuthenticated())
			{
				throw new NotAuthenticatedException("You need login to access this feature");
			}
			elseif ($result->getAuthMode() == AuthMode::HttpBasic)
			{
				$realm = 'Restricted area';

				if (empty($_SERVER['PHP_AUTH_USER']))
				{
					header('WWW-Authenticate: Basic realm="' . $realm . '"');
					header('HTTP/1.0 401 Unauthorized');
					die('You have to provide your credentials before proceed.');
				}
				else
				{
					$usersDb = $context->getUsersDatabase();

					$users = $usersDb->getUserName($_SERVER['PHP_AUTH_USER']);
					if ($users == null)
					{
						header('HTTP/1.1 403 Forbiden');
						die('Wrong Credentials!');
					}
					$userTable = $usersDb->getUserTable();
					
					// Check if Username and plain password is valid. If dont try to check if the SHA1 password is ok
					if (!$usersDb->validateUserName($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']))
					{
						$password = $users->getField($userTable->Password);						
						if ($password != $_SERVER['PHP_AUTH_PW'])
						{
							header('HTTP/1.1 403 Forbiden');
							die('Wrong Credentials!');							
						}
					}
					$context->MakeLogin($users->getField($userTable->Username), $users->getField($userTable->Id));
				}
			}
			elseif ($result->getAuthMode() == AuthMode::HttpDigest)
			{
				$realm = 'Restricted area';

				if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
					header('HTTP/1.1 401 Unauthorized');
					header('WWW-Authenticate: Digest realm="'.$realm.
						   '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');

					die('You have to provide your credentials before proceed.');
				}


				// analyze the PHP_AUTH_DIGEST variable
				if (!($data = self::httpDigestParse($_SERVER['PHP_AUTH_DIGEST'])) ||
				   (!isset($data['username'])))
					die('Wrong Credentials!');

				// Validate if the username and password are valid
				$usersDb = $context->getUsersDatabase();
				$users = $usersDb->getUserName($data['username']);
				if ($users == null)
				{
					header('HTTP/1.1 403 Forbiden');
					die('Wrong Credentials!');
				}
				$userTable = $usersDb->getUserTable();
				$password = $users->getField($userTable->Password);

				// generate the valid response
				$A1 = md5($data['username'] . ':' . $realm . ':' . $password);
				$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
				$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

				if ($data['response'] != $valid_response)
				{
					header('HTTP/1.1 403 Forbiden');
					die('Wrong Credentials!');
				}

				// ok, valid username & password
				$context->MakeLogin($users->getField($userTable->Username), $users->getField($userTable->Id));
			}

			if (!$result->accessGranted())
			{
				$result->processInsufficientPrivilege();
			}
		}
		return $result;
	}

	// function to parse the http auth header
	private static function httpDigestParse($txt)
	{
		// protect against missing data
		$needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
		$data = array();
		$keys = implode('|', array_keys($needed_parts));

		preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

		foreach ($matches as $m) {
			$data[$m[1]] = $m[3] ? $m[3] : $m[4];
			unset($needed_parts[$m[1]]);
		}

		return $needed_parts ? false : $data;
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
			if (!is_array($context->get("xmlnuke.PHPLIBDIR")))
				throw new InvalidArgumentException('Config "xmlnuke.PHPLIBDIR" requires an associative array');

			ModuleFactory::$_phpLibDir = $context->get("xmlnuke.PHPLIBDIR");

			$autoLoad = AutoLoad::getInstance();
			foreach(ModuleFactory::$_phpLibDir as $lib => $path)
				$autoLoad->registrUserProject($path);
		}
		return ModuleFactory::$_phpLibDir;
	}
}
?>
