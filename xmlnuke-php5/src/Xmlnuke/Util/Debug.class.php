<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A XML site content management.
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
* Generic functions to help you in debug process
*/
namespace Xmlnuke\Util;

use DOMDocument;
use DOMElement;
use DOMNode;
use Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Xmlnuke\Core\AnyDataset\AnyDataSet;
use Xmlnuke\Core\AnyDataset\AnyIterator;
use Xmlnuke\Core\AnyDataset\IIterator;
use Xmlnuke\Core\AnyDataset\IteratorFilter;
use Xmlnuke\Core\AnyDataset\SingleRow;
use Xmlnuke\Core\Engine\ErrorHandler;
use Xmlnuke\Core\Locale\LanguageCollection;
use Xmlnuke\Core\Processor\FilenameProcessor;

class Debug
{
	protected static $count = 1;

	/**
	 *
	 * @var LogWrapper
	 */
	protected static $_logger = null;

	protected static function writeLog($title = "", $contents = "", $preserve = false)
	{
		$stdClass = new \stdClass();
		$stdClass->debugXmlnuke = true;
		$stdClass->title = $title;
		$stdClass->contents = $contents;
		$stdClass->preserve = $preserve;

		self::$_logger->debug($stdClass);
	}

	/**
	 * Assist your to debug vars. Accept n vars parameters
	 * Included Debug on ARRAY an IIterator Object
	 *
	 * @param mixed $arg1
	 * @param mixed $arg2
	 * @param mixed $arg3
	 */
	public static function PrintValue($arg1, $arg2 = null)
	{
		if (self::$_logger == null)
			self::$_logger = new LogWrapper('debug.output');

		for ($i = 0, $numArgs = func_num_args(); $i < $numArgs ; $i++)
		{
			$var = func_get_arg($i);
			ErrorHandler::getInstance()->addExtraInfo('DEBUG_' . (Debug::$count++), $var);

			if (array_key_exists("raw", $_REQUEST))
			{
				continue;
			}

			if (is_array($var))
			{
				self::writeLog(null, print_r($var, true), true);
			}
			elseif ($var instanceof SingleRow)
			{
				self::writeLog('SingleRow', print_r($var->getRawFormat(), true), true);
			}
			elseif ($var instanceof AnyDataSet)
			{
				Debug::PrintValue($var->getIterator());
			}
			elseif ( ($var instanceof IIterator) || ($var instanceof AnyIterator) )
			{
				$it = $var;
				if (!$it->hasNext())
				{
					self::writeLog('IIterator', "Não trouxe registros.", false);
				}
				else
				{
					$result = "<style>.devdebug {border: 1px solid silver; padding: 2px;} table.devdebug {border-collapse:collapse;} th.devdebug {background-color: silver}</style>"
							. "<table class='devdebug'>";
					$arr = null;
					while ($it->hasNext())
					{
						$i++;
						$sr = $it->moveNext();
						if ($i>100)
							break;

						if (is_null($arr))
						{
							$arr = $sr->getFieldNames();
							$result .= '<tr><th class="devdebug">' . implode('</b></th><th class="devdebug">', $arr) . '</th></tr>';
						}

						$raw = $sr->getRawFormat();
						$result .= '<tr>';
						foreach($raw as $item)
						{
							$result .= '<td class="devdebug">';
							if (is_array($raw))
							{
								$result .= implode(',', $raw);
							}
							else
							{
								$result .= $raw;
							}
							$result .= '</td>';
						}
						$result .= '</tr>';
					}
					$result .= "</table>";
					self::writeLog('IIterator', $result, false);
				}
			}
			elseif ($var instanceof IteratorFilter)
			{
				$filter = $var->getSql("ANYTABLE", $param, "*");
				$filter = substr($filter, strpos($filter, "where") + 6);
				$result = array(
					"XPath" => $var->getXPath(),
					"Filter" => $filter
				) + $param;

				self::writeLog(get_class($var), print_r($result, true), true);
			}
			elseif ($var instanceof FilenameProcessor)
			{
				$result = array(
					"PathSuggested()" => $var->PathSuggested(),
					"PrivatePath()" => $var->PrivatePath(),
					"SharedPath()" => $var->SharedPath(),
					"Name" => $var->ToString(),
					"Extension()" => $var->Extension(),
					"FullQualifiedName()" => $var->FullQualifiedName(),
					"FullQualifiedNameAndPath()" => $var->FullQualifiedNameAndPath(),
					"getFilenameLocation()" =>  $var->getFilenameLocation(),
					"File Exists?" => file_exists($var->FullQualifiedNameAndPath()) ? "yes" : "no"
				);

				self::writeLog(get_class($var), print_r($result, true), true);
			}
			elseif ($var instanceof LanguageCollection)
			{
				self::writeLog("", get_class($var) . ": Is Loaded? " . ($var->loadedFromFile() ? 'yes' : 'no'), false);
				$var->Debug();
			}
			elseif (is_object($var))
			{
				if ($var instanceof DOMDocument)
				{
					$var->formatOutput = true;
					self::writeLog(get_class($var), htmlentities($var->saveXML()), true);
				}
				elseif ( ($var instanceof DOMElement) || ($var instanceof DOMNode) )
				{
					$doc = $var->ownerDocument;
					$doc->formatOutput = true;
					echo htmlentities($doc->saveXML($var)) . "</pre>";
					self::writeLog(get_class($var), htmlentities('[' . $var->nodeName . "]") . "\n" . htmlentities($doc->saveXML()), true);
				}
				else
				{
					self::writeLog(get_class($var), print_r($var, true), true);
				}
			}
			elseif (gettype($var) == "boolean")
			{
				self::writeLog("", '(boolean) ' . ($var ? "true":"false"), true);
			}
			else
			{
				self::writeLog("", gettype($var) . ": ". $var, true);
			}
		}
	}

	public static function GetBackTrace()
	{
		$raw = 	debug_backtrace();

        $output="";

        $i = sizeof($raw) - 1;
        foreach($raw as $entry)
        {
        	if ($entry['function'] != "GetBackTrace")
        	{
				$output.= "[" . $i-- . "] ";
				$output.="File: ".$entry['file']." (Line: ".$entry['line'].")\n";
				$output.="    Function: ".$entry['function']." ";
				$args = array();
				foreach ($entry['args'] as $arg)
				{
					if (is_object($arg))
					{
						$args[] = "object [" . get_class($arg) . "]";
					}
					elseif (is_array($arg))
					{
							//$args[] = "['" . implode("','", $arg) . "'] ";
					}
					else
					{
						$args[] = "\"" . $arg . "\"";
					}
				}
				$output.=" ( " . implode(",", $args) . " ) \n\n";
			}
        }

        $output .= "[0] {main}";
        return $output;
	}


	/**
	 * @param string $module
	 * @param Exception $error
	 * @return void
	 */
	public static function LogError($module, $error)
	{
		if ($error instanceof Exception)
		{
			$chamada = get_class($error);
			$mensagem = $error->getMessage();
		}
		else
		{
			$chamada = "ERROR";
			$mensagem = $error;
		}
		Debug::LogText($chamada . ": $module: " . $mensagem);
	}


	/**
	 * @param string $text
	 * @return void
	 */
	public static function LogText($text)
	{
		syslog(LOG_ERR, "XMLNUKE: " . $text);
	}
}
?>