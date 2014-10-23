<?php
/**
 * Contains all commands necessary to run XMLNuke. 
 * 
 * To run the XMLNuke as a Web site you need to have the file xmlnuke.php. 
 * 
 * You can use the XMLNuke as a library. In this case, you have to include in your PHP files this files. 
 * 
 * @package xmlnuke
 */

## Profiling Tool ########
# xdebug_enable();
##########################

use Xmlnuke\Core\Engine\AutoLoad;
use Xmlnuke\Core\Engine\ErrorHandler;

ob_start();
session_start();

set_include_path(get_include_path() . PATH_SEPARATOR . '.');
// Solve problem Page Expired when Back button was selected
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter("must-revalidate");

header("Content-Type: text/html; charset=utf-8");

define("SESSION_XMLNUKE_AUTHUSER", "SESSION_XMLNUKE_AUTHUSER");
define("SESSION_XMLNUKE_AUTHUSERID", "SESSION_XMLNUKE_AUTHUSERID");
define("SESSION_XMLNUKE_USERCONTEXT", "SESSION_XMLNUKE_USERCONTEXT");

/* This main of engine */
if (!class_exists('config') && !file_exists("config.inc.php"))
	die("<b>Fatal error:</b> Could not find required 'config.inc.php'");

require_once("config.inc.php");
if (!class_exists('config')) { header("Location: check_install.php"); exit(); }

// Activate AutoLoad
if (!is_readable(PHPXMLNUKEDIR . "src/Xmlnuke/Core/Engine/Autoload.class.php"))
	die("<b>Fatal error:</b> Bad Xmlnuke configuration. Check your constant 'PHPXMLNUKEDIR'");

require_once PHPXMLNUKEDIR . "src/Xmlnuke/Core/Engine/Autoload.class.php";
$autoload = AutoLoad::getInstance();

if (file_exists(PHPXMLNUKEDIR . 'src/vendor/autoload.php'))
{
	require(PHPXMLNUKEDIR . 'src/vendor/autoload.php');
}
if (file_exists(PHPXMLNUKEDIR . '../../../autoload.php'))
{
	require(PHPXMLNUKEDIR . '../../../autoload.php');
}

// Error Handler
ErrorHandler::getInstance()->register();

?>
