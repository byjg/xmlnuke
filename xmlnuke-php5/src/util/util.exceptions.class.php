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
class XMLNukeException extends Exception
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

/**
 * This is Not Found exception type
 * @package xmlnuke
 * @subpackage xmlnuke.kernel
 */
class NotFoundException extends XMLNukeException
{

	/**
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->showStackTrace = false;
	}

}

/**
 * This is Not Authenticated exception type
 * @package xmlnuke
 * @subpackage xmlnuke.kernel
 */
class NotAuthenticatedException extends XMLNukeException
{

	/**
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->showStackTrace = false;
	}

}

/**
 * This is Insufficient Privilege exception type
 * @package xmlnuke
 * @subpackage xmlnuke.kernel
 */
class InsufficientPrivilegeException extends XMLNukeException
{

	/**
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->showStackTrace = false;
	}

}

/**
 * This File Util exception type
 * Range code error: 100 to 249
 * @package xmlnuke
 * @subpackage xmlnuke.kernel
 */
class FileUtilException extends XMLNukeException
{

	/**
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->showStackTrace = true;
	}
}

/**
 * This Xml Util exception type
 * Range code error: 250 to 399
 * @package xmlnuke
 * @subpackage xmlnuke.kernel
 */
class XmlUtilException extends XMLNukeException
{

	/**
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->showStackTrace = true;
	}
}

/**
 * This Date Util exception type
 * Range code error: 700 to 749
 * @package xmlnuke
 * @subpackage xmlnuke.kernel
 */
class DateUtilException extends XMLNukeException
{

	/**
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->showStackTrace = true;
	}

}

/**
 * This is base engine exception
 * Range code error: 750 to 800
 * @package xmlnuke
 * @subpackage xmlnuke.kernel
 */
class EngineException extends XMLNukeException
{

	/**
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->showStackTrace = true;
	}

}

/**
 * This Image Util exception type
 * Range code error: 801 to 820
 * @package xmlnuke
 * @subpackage xmlnuke.kernel
 */
class ImageUtilException extends XMLNukeException
{

	/**
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->showStackTrace = true;
	}
}

/**
 * This Upload Util exception type
 * Range code error: 821 to 840
 * @package xmlnuke
 * @subpackage xmlnuke.kernel
 */
class UploadUtilException extends XMLNukeException
{

	/**
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->showStackTrace = true;
	}

}

/**
 * This is database exception type
 * Range code error: 1000 to 2000
 * @package xmlnuke
 * @subpackage xmlnuke.kernel
 */
class DataBaseException extends XMLNukeException
{

	/**
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->showStackTrace = true;
	}
}

/**
 * This is Module exception type
 * Range code error: 5000 to 7000
 * @package xmlnuke
 * @subpackage xmlnuke.kernel
 */
class ModuleException extends XMLNukeException
{

	/**
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->showStackTrace = true;
	}
}

class IteratorException extends XMLNukeException
{
	/**
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->showStackTrace = true;
	}
}

class DatasetException extends XMLNukeException
{
	/**
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->showStackTrace = true;
	}
}


class NotImplementedException extends XMLNukeException
{
	/**
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->showStackTrace = false;
	}
}

class UnsupportedFeatureException extends XMLNukeException
{
	/**
	 *
	 * @param type $message
	 * @param type $code
	 * @param type $previous
	 */
	public function __construct($message = "", $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->showStackTrace = false;
	}
}

class PHPWarning
{
	protected static function VerifyArgs($getLast, $backTrace)
	{
		if (!is_array($getLast) || !is_array($backTrace))
			throw new InvalidArgumentException("You have to call with error_get_last() and debug_backtrace() as parameters");

		if (!array_key_exists("function", $backTrace))
			throw new InvalidArgumentException("The array of debug_trace() seems not valid.");

		if (!array_key_exists("message", $getLast) || !array_key_exists("type", $getLast))
			throw new InvalidArgumentException("The array of error_get_last() seems not valid.");
	}

	protected static function ErrorDescription($type)
	{
		$array = array(
			1 => "E_ERROR",
			2 => "E_WARNING",
			4 => "E_PARSE",
			8 => "E_NOTICE",
			16 => "E_CORE_ERROR",
			32 => "E_CORE_WARNING",
			64 => "E_COMPILE_ERROR",
			128 => "E_COMPILE_WARNING",
			256 => "E_USER_ERROR",
			512 => "E_USER_WARNING",
			1024 => "E_USER_NOTICE",
			2048 => "E_STRICT"
		);

		if (array_key_exists($type, $array))
			return $array[$type];
		else
			return "PHP Error $type";
	}

	public static function LoadXml($expectedFunction, $xml)
	{
		$getLast = error_get_last();
		if ($getLast == null)
			return;

		$backTrace = debug_backtrace();
		$backTrace = $backTrace[1]; // Zero is the LoadXml;
		//print_r($getLast);

		//PHPWarning::VerifyArgs($getLast, $backTrace);

		if (($backTrace["function"] == $expectedFunction) && (strpos($getLast["message"], "DOMDocument::loadXML()") !== false))
		{
			echo "<b>" . PHPWarning::ErrorDescription($getLast["type"]) . "</b>: " . $getLast["message"] . " in " . basename($getLast["file"]) . " at line " . $getLast["line"] . " <br>\n";
			$match = array();
			if (preg_match("/\sline:\s(?<line>\d+)/", $getLast["message"], $match))
			{
				echo "Code at line " . $match["line"] . ":" . "<br>\n";

				$lines = explode("\n", $xml);
				echo "<xmp>" . $lines[$match["line"] - 1] . "</xmp>";

				echo "\n<br><br>\n";
			}
		}
	}
}

?>
