<?php

require_once __DIR__ . '/xmlnuke-php5/src/Xmlnuke/Util/CreatePhp5Project.class.php';

use Xmlnuke\Util\CreatePhp5Project;

if (PHP_SAPI == 'cli')
{

	echo "\n";
	echo "============================\n";
	echo "XMLNuke PHP5 Project Updater\n";
	echo "By JG @ 2014\n";
	echo "============================\n";
	echo "\n";

	if ($argc < 2)
	{
		echo "Use this script to update and existing XMLNuke PHP5 project with the essentials XMLNuke files. \n";
		echo "The config.php file must be configured properly before run this command. \n";
		echo "\n";
		echo "Usage: \n";
		echo "   update-php5-project.php PATHTOYOURPROJECT \n";
		echo "\n";
		echo "Where: \n";
		echo "   PATHTOYOURPROJECT is the full path for your project  \n";
		echo "\n";
	}
	else
	{
		try
		{
			$result = CreatePhp5Project::Update(dirname($argv[0]), $argv[1]);

			echo "Done.\n";
			echo "\n";
		}
		catch (Exception $ex)
		{
			echo "Error: " . $ex->getMessage() . "\n\n";
		}
	}
}

