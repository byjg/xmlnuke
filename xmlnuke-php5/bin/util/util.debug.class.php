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
class Debug
{
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
		if (array_key_exists("rawxml", $_REQUEST) && ($_REQUEST["rawxml"] == "true"))
		{
			return;
		}

		for ($i = 0, $numArgs = func_num_args(); $i < $numArgs ; $i++)
		{
			echo "<b><font color='red'>Debug</font></b>: ";
			$var = func_get_arg($i);
			if (is_array($var))
			{
				foreach ($var as $key=>$value)
				{
					echo "[<b>$key</b>] => ";
					if (is_object($value))
					{
						echo "{ ";
						Debug::PrintValue($value);
						echo " }<br/>";
					}
					else
					{
						echo "$value <br>";
					}
				}
			}
			elseif ($var instanceof SingleRow)
			{
				echo "<b>SingleRow</b><br>";
				$arr = $var->getFieldNames();
				foreach ($arr as $key=>$value)
				{
					echo "<b>" . $value."</b>=>".$var->getField($value)."<br>";
				}
			}
			elseif ($var instanceof AnyDataSet)
			{
				echo get_class($var) . "<br>";
				Debug::PrintValue($var->getIterator());
			}
			elseif ( ($var instanceof IIterator) || ($var instanceof AnyIterator) )
			{
				$it = clone $var;
				echo "<hr>";
				if (!$it->hasNext())
				{
					echo "<b>Nao trouxe Registros</b>";
				}
				else
				{
					$i = 0;
					$arr = null;
					echo "<table border=1>";
					while ($it->hasNext())
					{
						$i++;
						$sr = $it->moveNext();
						if ($i>100)
						{
							break;
						}

						if (is_null($arr))
						{
							$arr = $sr->getFieldNames();
							echo "<tr>";
							foreach ($arr as $key=>$value)
							{
								echo "<td bgcolot=silver><b>" . $value . "</b></td>";
							}
							echo "</tr>";
						}

						echo "<tr>";
						foreach ($arr as $key=>$value)
						{
							echo "<td>" . $sr->getField($value). "</td>";
						}
						echo "</tr>";
					}
					echo "</table>";
				}
			}
			elseif ($var instanceof IteratorFilter)
			{
				echo "<b>" . get_class($var) . "</b><br>";
				echo "XPath = " . $var->getXPath() . "<br>";
				$filter = $var->getSql("ANYTABLE", $param, "*");
				$filter = substr($filter, strpos($filter, "where") + 6);
				echo "Filter = " . $filter . "<br>";
				Debug::PrintValue($param);

			}
			elseif ($var instanceof FilenameProcessor)
			{
				echo "<b>" . get_class($var) . "</b><br>";
				echo "<b>Path Suggested: </b>" . $var->PathSuggested() . "<br>";
				echo "<b>Private Path: </b>" . $var->PrivatePath() . "<br>";
				echo "<b>Shared Path: </b>" . $var->SharedPath() . "<br>";
				echo "<b>Name: </b>" . $var->ToString() . "<br>";
				echo "<b>Extension: </b>" . $var->Extension() . "<br>";
				echo "<b>Full Name: </b>" . $var->FullQualifiedName() . "<br>";
				echo "<b>Full Qualified Name And Path: </b>" . $var->FullQualifiedNameAndPath() . "<br>";
			}
			elseif ($var instanceof LanguageCollection)
			{
				echo "<b>" . get_class($var) . "</b><br>";
				echo "<b>Is Loaded?: </b>" . $var->loadedFromFile() . "<br/>";
				Debug::PrintValue($var->Debug());
			}
			elseif (is_object($var))
			{
				echo get_class($var) . ", ";
				//echo $var;
				if ($var instanceof DOMDocument)
				{
					$value->formatOutput = true;
					echo "<pre>\n". htmlentities($var->saveXML()) . "\n</pre>";
				}
				elseif ( ($var instanceof DOMElement) || ($var instanceof DOMNode) )
				{
					echo "<pre>\n";
					echo htmlentities('[' . $var->nodeName . "]");
					echo "\n";
					$doc = $var->ownerDocument;
					$doc->formatOutput = true;
					echo htmlentities($doc->saveXML($var)) . "</pre>";
				}
				else
				{
					echo "object<br>Public Method List:<ul>";
					$class = new ReflectionClass(get_class($var));
					$methods = $class->getMethods( ReflectionProperty::IS_PUBLIC );
					foreach ($methods as $met)
					{
						echo "<li>" . $met->getName() . "()";
						if (!(strpos($met->getName(), "get")===false))
						{
							echo " ==> ";
							try
							{
								$method = new ReflectionMethod(get_class($var), $met->getName());
								echo $method->invokeArgs($var, array());
							}
							catch (Exception $ex)
							{
								echo "Error: " . $ex->getMessage();
							}

						}
						echo "</li>";
					}

					echo "</ul>";

				}
			}
			elseif (gettype($var) == "boolean")
			{
				echo gettype($var) . ": ". ($var ? "true":"false");
			}
			else
			{
				echo gettype($var) . ": ". $var;
			}
			echo "<br>";
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
	 * @param exception $error
	 * @return void
	 */
	public static function LogError($module, $error)
	{
		Debug::LogText(get_class($error) . ": $module: " . $error->getMessage());
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