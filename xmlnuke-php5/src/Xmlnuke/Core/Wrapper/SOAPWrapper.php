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
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Wrapper;

use InvalidArgumentException;
use Services_Webservice;
use Xmlnuke\Core\Classes\BaseSingleton;
use Xmlnuke\Core\Engine\Context;

class SOAPWrapper extends BaseSingleton implements IOutputWrapper
{
	public function Process()
	{

		/**
		 * @var Context
		 */
		$context = Context::getInstance();

		$name = $context->getVirtualCommand();

		if ($name == "")
		{
			$this->printHelp();
			// END
			exit;
		}


		require_once(PHPXMLNUKEDIR . "src/Xmlnuke/Library/webservice/webservice.php");

		$className = '\\' . str_replace('.', '\\', $name);

		$rClass = new \ReflectionClass($className);
		$class = $rClass->newInstance();

		if ($class instanceof \Services_Webservice)
		{
			$class->handle();
		}
		else
		{
			throw new InvalidArgumentException("Class '$name' is not a WebServices");
		}

	}




	protected function printHelp()
	{
		$code = "
		// START: Sample class to create a PHP WebService
		class XMLNukeWebService extends Services_Webservice
		{

			/**
			* Retorna a versao do WebService
			* @return string
			*/
			public function getVersion()
			{
				return \"XMLNuke WebService Helper. V1.0\";
			}

		}

		\$myService = new XMLNukeWebService(
			\"http://www.xmlnuke.com\",
			\"Sample class to create a WebService using XMLNuke facilities. To acess this module you \" .
			\" *must* call: webservice.php/namespace.webservice\",
			array('uri' => 'http://www.xmlnuke.com','encoding'=>SOAP_ENCODED ));
		\$myService->handle();
		";

		$message = "You must pass to WebService Wrapper where your service is located.<br><br>";
		$message .= "First, you need to create a WebService. Code example: <br><pre>";
		$message .= "$code</pre>";
		$message .= "<br><br>";
		$message .= "After created your class, you need put it in a directory inside your LIB folder.";
		$this->printErrorMessage(500, "WebService Missing Parameters", $message);
	}

	protected function printErrorMessage($code, $title, $message, $trace = null)
	{
		ob_clean();
		header("HTTP/1.0 $code $title");
		header("Status: $code $title");
		echo "<html><head>";
		echo "<title>$title</title>";
		echo "</head><body>";
		echo "<h1>$title</h1>";
		echo "<p>$message<br />";
		if ($trace != null)
		{
			echo "<br />";
			foreach ($trace as $key=>$value)
			{
				$args = (is_array($value["args"]) ? implode(", ", $value["args"]) : "");
				echo "File <b>" . basename($value["file"]). "</b> (line " . $value["line"] . "): " . $value["class"] . $value["type"] . $value["function"] . "( " . $args . " ) <br/>";
			}
			echo "<br/>";
		}
		echo "</p>";
		echo "<hr>";
		echo "<address>WebService Wrapper By XMLNuke.com</address>";
		echo "</body></html>";
		exit;
	}


}
