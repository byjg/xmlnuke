#!/usr/bin/php
<?php

require_once __DIR__ . '/xmlnuke-php5/src/Xmlnuke/Util/CreatePhp5Project.php';

use Xmlnuke\Util\CreatePhp5Project;

if (PHP_SAPI == 'cli')
{

	echo "\n";
	echo "============================\n";
	echo "XMLNuke PHP5 Project Creator\n";
	echo "By JG @ 2014\n";
	echo "============================\n";
	echo "\n";

	if ($argc < 4)
	{
		echo "Use this script to create a XMLNuke PHP5 project ready run and edit in Netbeans, PDT Eclipse or another editor. \n";
		echo "\n";
		echo "Usage: \n";
		echo "   create-php5-project.php PATHTOYOURPROJECT project language1 language2... \n";
		echo "\n";
		echo "Where: \n";
		echo "   PATHTOYOURPROJECT is the full path for your project  \n";
		echo "   project is the name of the project, for example: MyProject  \n";
		echo "   language is the main language for your project. e.g.: pt-br or en-us or de-de  \n";
		echo "\n";
	}
	else
	{
		try
		{
			$result = call_user_func_array( array( '\Xmlnuke\Util\CreatePhp5Project', 'Run' ), $argv );

			echo "Done.\n";
			echo "\n";
			echo "You must do some configurations manualy:\n";
			echo "  - Create an alias \"/common\" pointing to \"{$result['XMLNUKE']}/xmlnuke-common\" \n";
			echo "  - Point the document root on your Web Server to \"{$result['HOME']}/httpdocs\" \n";
			echo "\n";
			echo "After this you can play with these URLs:\n";
			echo "http://localhost/xmlnuke.php?xml=home\n";
			echo "http://localhost/xmlnuke.php?module={$result['PROJECT']}.Home\n";
			echo "\n";
		}
		catch (Exception $ex)
		{
			echo "Error: " . $ex->getMessage() . "\n\n";
		}
	}
}

