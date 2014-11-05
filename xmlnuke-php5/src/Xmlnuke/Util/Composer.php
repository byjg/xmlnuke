<?php

/* 
 * The file extension of this class have intentionaly changed to work with
 * autoload composer
 */

namespace Xmlnuke\Util;

use Composer\Script\Event;

class Composer
{
	public static function postInstallCmd(Event $event)
	{
		$baseXmlnuke = realpath(dirname(__FILE__). "/../../../.." );
		$baseProject = realpath(dirname($baseXmlnuke) . "/../..");
		
		$extra = $event->getComposer()->getPackage()->getExtra();
		if (isset($extra) && isset($extra['project-name']))
		{
			$projectName = $extra['project-name'];
		}
		else
		{
			$projectName = basename($baseProject);
		}

		$output = $event->getIO();
		$output->write('=== XMLNUKE ===', true);
		$output->write('Xmlnuke Dir: ' . $baseXmlnuke, true);
		$output->write('Project Dir: ' . $baseProject, true);

		if (!file_exists($baseProject . '/httpdocs'))
		{
			$output->write("Creating Project...", true);
			$result = call_user_func_array( array( '\Xmlnuke\Util\CreatePhp5Project', 'Run' ), array(
					$baseXmlnuke . '/Composer.php',
					$baseProject,
					preg_replace('/[^A-Za-z0-0]/', '', $projectName),
					"en-us"
				)
			);
		}

		if (!file_exists($baseProject . '/httpdocs/config.inc.php'))
		{
			$output->write("Setting the project ...", true);
			$configInc = file_get_contents($baseProject . "/httpdocs/config.inc-dist.php");
			$configInc = str_replace('#XMLNUKE#', $baseXmlnuke,
						str_replace('#PROJECT#', $baseProject,
							$configInc
						));
			file_put_contents($baseProject . "/httpdocs/config.inc.php", $configInc);
		}

		$output->write("Updating Project References...", true);
		CreatePhp5Project::Update($baseXmlnuke, $baseProject);
	}
}

