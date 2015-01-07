<?php

foreach ($argv as $pair)
{
        $arPair = explode("=", $pair);
        if (sizeof($arPair) > 1)
        {
                $_REQUEST[$arPair[0]] = $arPair[1];
                $_GET[$arPair[0]] = $arPair[1];
        }
}
$_SERVER['QUERY_STRING'] = implode('&', $argv);
$_SERVER['REQUEST_URI'] = 'xmlnuke.cmd.php';

#$_SERVER["PHP_SELF"] .= $_REQUEST["ws"];

#$_REQUEST["raw"] = "xml";

if (!isset($_REQUEST['raw']))
	$_REQUEST["raw"] = "xml";

include("xmlnuke.php");

?>
