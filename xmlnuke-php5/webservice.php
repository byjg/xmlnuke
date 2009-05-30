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
	$path = ModuleFactory::LibPath(dirname($name), basename($name));
	
	$file = basename($path);
	$path = dirname($path);

	try
	{
		$filename = $path . FileUtil::Slash() . $file . ".class.php";
		if (!FileUtil::Exists($filename))
		{
			$message = "The Requested webservice '<b>$name</b>' not found<br><br>";
			$message .= "<b>Tips</b><ul>";
			$message .= "<li>The webservice '$name' must reside on $filename</li>";
			$message .= "<li>Follow the sample class</li>";
			$message .= "</ul>";
			print_error_message(404, "WebService Not Found", $message);
		}
		include_once($filename);
		//$class = new ReflectionClass($file);
		//$result = $class->newInstance($context);
	}
	catch (Exception $e)
	{
		print_error_message(500, "WebService Programming Error", $e->getMessage() . "<br/><br/>" . $e->getTrace());
	}
}

function print_error_message($code, $title, $message)
{
	ob_clean();
	header("HTTP/1.0 $code $title");
	header("Status: $code $title");
	echo "<html><head>";
	echo "<title>$title</title>";
	echo "</head><body>";
	echo "<h1>$title</h1>";
	echo "<p>$message<br />";
	echo "</p>";
	echo "<hr>";
	echo "<address>WebService Wrapper By XMLNuke.com</address>";
	echo "</body></html>";
	exit;
}

?>