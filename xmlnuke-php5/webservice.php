<?php
#############################################
# To create a XMLNuke capable PHP5 page
#
require_once("xmlnuke.inc.php");
#############################################

require_once(PHPXMLNUKEDIR . "bin/modules/webservice/webservice.php");

$context = new Context();

$name = $context->getVirtualCommand();

if ($name == "")
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
	print_error_message(500, "WebService Missing Parameters", $message);
	
	
	// END
	exit;
}
else 
{
	// Detect the place were the WebService is located.
	$namespace = $name;
	$class = "";
	$filename = "";
	$failover = 0;
	while ( ($namespace != ".") && ($filename == "") && ($failover++ < 10) )
	{
		$class = basename($namespace) . ($class != "" ? "/" . $class : "");
		$namespace = dirname($namespace); 
		$path = ModuleFactory::LibPath(str_replace("/", ".", $namespace), $class);
		
		$file = basename($path);
		$path = dirname($path);
		
		if (FileUtil::Exists($path . FileUtil::Slash() . $file . ".class.php"))
		{
			$filename = $path . FileUtil::Slash() . $file . ".class.php";
		}
	}

	// If not found shows an error
	if ($filename == "")
	{
		$message = "The Requested webservice '<b>$name</b>' not found<br><br>";
		$message .= "<b>Tips</b><ul>";
		$message .= "<li>Follow the sample class</li>";
		$message .= "</ul>";
		print_error_message(404, "WebService Not Found", $message);
	}
	
	// Execute the Webservice
	try
	{
		include_once($filename);
		//$class = new ReflectionClass($file);
		//$result = $class->newInstance($context);
	}
	catch (Exception $e)
	{
		print_error_message(500, "WebService Programming Error", $e->getMessage(), $e->getTrace());
	}
}

function print_error_message($code, $title, $message, $trace = null)
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

?>