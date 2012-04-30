<?php

foreach ($argv as $pair)
{
        $arPair = split("=", $pair);
        if (sizeof($arPair) > 1)
        {
                $_REQUEST[$arPair[0]] = $arPair[1];
                $_GET[$arPair[0]] = $arPair[1];
        }
}

#$_SERVER["PHP_SELF"] .= $_REQUEST["ws"];

#$_REQUEST["rawxml"] = true;

if (!array_key_exists("rawxml", $_REQUEST) && !array_key_exists("rawjson", $_REQUEST))
	$_REQUEST["rawxml"] = true;

include("xmlnuke.php");

?>
