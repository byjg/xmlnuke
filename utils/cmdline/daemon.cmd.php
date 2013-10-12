<?php
if (!defined('SLEEP_SERVICE'))
	define('SLEEP_SERVICE', 1000);

// Read parameters and convert to XMLNuke context
foreach ($argv as $pair)
{
	$arPair = split("=", $pair);
	if (sizeof($arPair) > 1)
	{
		$_REQUEST[$arPair[0]] = $arPair[1];
		$_GET[$arPair[0]] = $arPair[1];
	}
}

$svcname = (array_key_exists("service", $_REQUEST) ? $_REQUEST['service'] : '');
$baseLogPath = "/var/log/xmlnuke.daemon";
if (!file_exists($baseLogPath))
	mkdir($baseLogPath);
$svcLog = ($svcname == '' ? 'main' : $svcname);

fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen($baseLogPath . '/' . $svcLog . '.log', 'ab');
$STDERR = fopen($baseLogPath . '/' . $svcLog . '.error.log', 'ab');

//chdir(__DIR__);

#############################################
# To create a XMLNuke capable PHP5 page
#
require_once("xmlnuke.inc.php");
#############################################

if ($svcname == "")
{
	die("Error: Paramenter 'service' is required and must contain a full namespace for the class\n");
}

$svcname = str_replace('.', '\\', $svcname);

$service = new $svcname();
$continue = true;

echo "Service " . $svcname . " started at " . date('c') . "\n";
ob_flush();

// Execute routine
while ($continue)
{
	try
	{
		$continue = $service->execute();
		ob_flush();
	}
	catch (Exception $ex)
	{
		fwrite($STDERR, date('c') . ' [' . get_class($ex) . '] in ' . $ex->getFile() . ' at line ' . $ex->getLine() . ' -- ' . "\n");
		fwrite($STDERR, 'Message: '. $ex->getMessage() . "\n");
		fwrite($STDERR, "Stack Trace:\n" . $ex->getTraceAsString());
		fwrite($STDERR, "\n\n");
	}

	if ($continue)
		usleep(SLEEP_SERVICE * 1000);
}

//while (true)

?>
