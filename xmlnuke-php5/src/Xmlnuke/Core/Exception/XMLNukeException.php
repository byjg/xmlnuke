<?php
/*
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
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
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 */

/**
 * This is base engine exception
 * @package xmlnuke
 * @subpackage xmlnuke.kernel
 */
namespace Xmlnuke\Core\Exception;

use Xmlnuke\Util\Debug;

class XMLNukeException extends \Exception
{
	/**
	 * Module occurred erro
	 *
	 * @var string
	 */
	public $moduleName;

	/**
	 * Show stack trace?
	 *
	 * @var bool
	 */
	public $showStackTrace = true;

	/**
	 * Enter description here...
	 *
	 * @var string
	 */
	public $backTrace = "";

	/**
	 * XMLNukeException base Exception
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		$this->message = $message;
		$this->backTrace = Debug::GetBackTrace();
		$this->showStackTrace = true;

		if (PHP_VERSION_ID < 50300)
			parent::__construct($message, $code);
		else
			parent::__construct($message, $code, $previous);
	}

}
