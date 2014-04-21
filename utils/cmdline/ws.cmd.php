<?php

unset($argv[0]);   // Remove itself
unset($argv[1]);   // Remove command - WS

$queryStr = "";

foreach ($argv as $pair)
{
	$arPair = split("=", $pair);
	if (sizeof($arPair) > 1)
	{
		$_REQUEST[$arPair[0]] = $arPair[1];
		$_GET[$arPair[0]] = $arPair[1];

		if ($arPair[0] != 'ws')
			$queryStr .= ($queryStr != '' ? '&' : '') . $pair;
	}
	else
	{
		$queryStr .= ($queryStr != '' ? '&' : '') . $pair;		
	}


}

$_SERVER['QUERY_STRING'] = $queryStr;
$_SERVER['REQUEST_URI'] = 'ws.cmd.php';

$_SERVER["PHP_SELF"] .= $_REQUEST["ws"];

include("webservice.php");

?>
