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
// Config database ADO exception to throw up
class ErrorType
{
	const NotFound = "ERR_NOTFOUND";
	const ClassNotFound = "ERR_CLASSNOTFOUND";
	const InsufficientPrivilege = "ERR_INSUFFICIENTPRIVILEGE";
	const NotAuthenticated = "ERR_NOTAUTHENTICATED";
	const Kernel = "ERR_KERNEL";
	const Generality = "ERR_GENERALITY";
	const DataBase = "ERR_DATABASE";
	const Engine = "ERR_ENGINE";
	const Processor = "ERR_PROCESSOR";
	const Util = "ERR_UTIL";
	const Object = "ERR_OBJECT";
	const Module = "ERR_MODULE";
}
/**
*This is base engine exception
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class XMLNukeException extends Exception 
{
	/**
	 * Error Type
	 *
	 * @var ErrorType
	 */
	public $errorType = ErrorType::Kernel;
	/**
	 * Exception Class name
	 *
	 * @var string
	 */
	public $erroClass;
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
	 * XMLNukeException constructor
	 *
	 * @return XMLNukeException
	 * @param int $code
	 * @param string $message
	 */
	function XMLNukeException($code = 0, $message = "")
	{
		if ($message != "")
		{
			$this->code = $code;
			$this->message = $message;
		}
		else
		{
			$this->message = $code;
		}
		$this->setExceptionClassName($this);
		$this->backTrace = Debug::GetBackTrace();
	}
	/**
	 * Config exception type
	 *
	 * @param ErrorType $type
	 */
	public function setErrorType($type)
	{
		$this->errorType = $type;
	}
	/**
	 * Config Exception class name
	 *
	 * @param Exception $ex
	 */
	public function setExceptionClassName($ex)
	{
		$this->erroClass = get_class($ex);
	}
	/**
	 * Config range code error
	 *
	 * @param int $start
	 * @param int $end
	 */
	public function setRangeCode($start, $end)
	{
		if ($this->code < $start) {
			$this->code += $start;
		}
		if ($this->code > $end) {
			while (($this->code = $this->code / 10) > $end) {}
			$this->code = round($this->code) + $start;
		}
	}
}
/**
*This is Not Found exception type
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class NotFoundException extends XMLNukeException 
{
	/**
	 * NotFoundException constructor
	 *
	 * @return NotFoundException
	 * @param string $message
	 */
	function NotFoundException($message = "")
	{
		parent::XMLNukeException(404, $message);
		$this->errorType = ErrorType::NotFound;
		$this->showStackTrace = false;
	}
}
/**
*This is Not Found exception type
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class NotFoundClassException extends XMLNukeException 
{
	/**
	 * NotFoundException constructor
	 *
	 * @return NotFoundException
	 * @param string $message
	 */
	function NotFoundClassException($message = "")
	{
		parent::XMLNukeException(405, $message);
		$this->errorType = ErrorType::ClassNotFound;
		$this->showStackTrace = true;
	}
}
/**
*This is Not Authenticated exception type
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class NotAuthenticatedException extends XMLNukeException 
{
	/**
	 * NotAuthenticatedException constructor
	 *
	 * @return NotAuthenticatedException
	 * @param string $message
	 */
	function NotAuthenticatedException($message = "")
	{
		parent::XMLNukeException(402, $message);
		$this->errorType = ErrorType::NotAuthenticated; 
	}
}
/**
*This is Insufficient Privilege exception type
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class InsufficientPrivilegeException extends XMLNukeException 
{
	/**
	 * InsufficientPrivilegeException constructor
	 *
	 * @return InsufficientPrivilegeException
	 * @param string $message
	 */
	function InsufficientPrivilegeException($message = "")
	{
		parent::XMLNukeException(403, $message);
		$this->errorType = ErrorType::InsufficientPrivilege; 
	}
}
/**
*This File Util exception type
* Range code error: 100 to 249
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class FileUtilException extends XMLNukeException 
{
	/**
	 * FileUtilException constructor
	 * Range code error: 100 to 299
	 *
	 * @return FileUtilException
	 * @param int $code
	 * @param string $message
	 */
	function FileUtilException($code, $message = "")
	{
		parent::XMLNukeException($code, $message);
		$this->errorType = ErrorType::Util;
		$this->setRangeCode(100, 249);
	}
}
/**
*This Xml Util exception type
* Range code error: 250 to 399
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class XmlUtilException extends XMLNukeException 
{
	/**
	 * XmlUtilException constructor
	 *
	 * @return XmlUtilException
	 * @param int $code
	 * @param string $message
	 */
	function XmlUtilException($code, $message = "")
	{
		parent::XMLNukeException($code, $message);
		$this->errorType = ErrorType::Util;
		$this->setRangeCode(250, 399);
	}
}
/**
*This is base engine too exception type
* Range code error: 500 to 699
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class KernelException extends XMLNukeException 
{
	/**
	 * KernelException constructor
	 *
	 * @return KernelException
	 * @param int $code
	 * @param string $message
	 */
	function KernelException($code, $message = "")
	{
		parent::XMLNukeException($code, $message);
		$this->setRangeCode(500, 699);
	}
}
/**
*This Date Util exception type
* Range code error: 700 to 749
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class DateUtilException extends XMLNukeException 
{
	/**
	 * DateUtilException constructor
	 *
	 * @return DateUtilException
	 * @param int $code
	 * @param string $message
	 */
	function DateUtilException($code = 0, $message = "")
	{
		parent::XMLNukeException($code, $message);
		$this->errorType = ErrorType::Util;
		$this->setRangeCode(700, 749);
	}
}
/**
*This is base engine exception
* Range code error: 750 to 800
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class EngineException extends XMLNukeException 
{
	/**
	 * EngineException constructor
	 *
	 * @return EngineException
	 * @param int $code
	 * @param string $message
	 */
	function EngineException($code = 0, $message = "")
	{
		parent::XMLNukeException($code, $message);
		$this->errorType = ErrorType::Engine;
		$this->setRangeCode(750, 800);
	}
}
/**
*This Image Util exception type
* Range code error: 801 to 820
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class ImageUtilException extends XMLNukeException 
{
	/**
	 * ImageUtilException constructor
	 *
	 * @return ImageUtilException
	 * @param int $code
	 * @param string $message
	 */
	function ImageUtilException($code = 0, $message = "")
	{
		parent::XMLNukeException($code, $message);
		$this->errorType = ErrorType::Util;
		$this->setRangeCode(801, 820);
	}
}
/**
*This Upload Util exception type
* Range code error: 821 to 840
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class UploadUtilException extends XMLNukeException 
{
	/**
	 * UploadUtilException constructor
	 *
	 * @return UploadUtilException
	 * @param int $code
	 * @param string $message
	 */
	function UploadUtilException($code = 0, $message = "")
	{
		parent::XMLNukeException($code, $message);
		$this->errorType = ErrorType::Util;
		$this->setRangeCode(821, 840);
	}
}
/**
*This is database exception type
* Range code error: 1000 to 2000
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class DataBaseException extends XMLNukeException 
{
	/**
	 * DataBaseException constructor
	 *
	 * @return DataBaseException
	 * @param int $code
	 * @param string $message
	 */
	function DataBaseException($errno, $errmsg)
	{
		parent::XMLNukeException($errno, $errmsg);
	}
}
/**
*This is XmlNukeObject exception type
* Range code error: 850 to 999
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class XmlNukeObjectException extends XMLNukeException 
{
	/**
	 * XmlNukeObjectException constructor
	 *
	 * @return XmlNukeObjectException
	 * @param int $code
	 * @param string $message
	 */
	function XmlNukeObjectException($code = 0, $message = "")
	{
		parent::XMLNukeException($code, $message);
		$this->errorType = ErrorType::Object;
		$this->setRangeCode(850, 999);
	}
}
/**
* This is Module exception type
* Range code error: 5000 to 7000
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class ModuleException extends XMLNukeException 
{
	/**
	 * ModuleException constructor
	 *
	 * @return ModuleException
	 * @param int $code
	 * @param string $message
	 */
	function ModuleException($message = "", $code = 0)
	{
		parent::XMLNukeException($code, $message);
		$this->errorType = ErrorType::Module;
		$this->setRangeCode(5000, 7000);
	}
}
?>
