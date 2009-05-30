<?php
	##################################################
	# If 'module' didnt passed define the ControlPanel
	#
	##################################################
	if ($_REQUEST["module"] == "")
	{
		$_GET["module"] = "admin.ControlPanel";
		$_REQUEST["module"] = $_GET["module"];
		$GLOBALS["_GET"]["module"] = $_GET["module"];
		$GLOBALS["_REQUEST"]["module"] = $_GET["module"];
	}
	
	#############################################
	# To create a XMLNuke capable PHP5 page
	#
	require_once("xmlnuke.inc.php");
	require_once($context->ContextValue("xmlnuke.URLMODULE"));
	#############################################

?>