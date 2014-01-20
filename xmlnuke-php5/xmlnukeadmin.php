<?php
	##################################################
	# If 'module' didnt passed define the ControlPanel
	#
	##################################################
	if (!isset($_REQUEST["module"]))
	{
		$_REQUEST["module"] = "Xmlnuke.Admin.ControlPanel";
	}
	
	#############################################
	# To create a XMLNuke capable PHP5 page
	#
	require_once("xmlnuke.inc.php");
	require_once(Xmlnuke\Core\Engine\Context::getInstance()->get("xmlnuke.URLMODULE"));
	#############################################

?>