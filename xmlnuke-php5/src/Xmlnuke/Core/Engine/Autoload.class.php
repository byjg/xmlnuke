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

class AutoLoad
{
	public function __construct()
	{
		spl_autoload_register(array($this, "splAutoLoad"));
	}


	protected static $arrayNameSpace =
			array
			(
				"src/",
				"src/Xmlnuke/Library/",
				"src/modules/oauthclient/20/",
				"src/modules/oauthclient/10/",
				"src/modules/aws/"
			);

	protected function splAutoLoad($className)
	{
		foreach (AutoLoad::$arrayNameSpace as $prefix)
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
	
}



