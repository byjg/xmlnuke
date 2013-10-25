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

#$_REQUEST["raw"] = "xml";

if (!isset($_REQUEST['raw']))
	$_REQUEST["raw"] = "xml";

include("xmlnuke.php");

?>
