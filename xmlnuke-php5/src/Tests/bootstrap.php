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

define('PHPXMLNUKEDIR', realpath(__DIR__ . '/../..') . '/');

$_SERVER['SERVER_SOFTWARE'] = 'CLI';
$_SERVER['SCRIPT_NAME'] = '';
$_SERVER['QUERY_STRING'] = '';
$_SERVER['REQUEST_URI'] = '/';


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

// Activate AutoLoad
if (!is_readable(PHPXMLNUKEDIR . "src/Xmlnuke/Core/Engine/Autoload.class.php"))
	die("<b>Fatal error:</b> Bad Xmlnuke configuration. Check your constant 'PHPXMLNUKEDIR'");

require_once PHPXMLNUKEDIR . "src/Xmlnuke/Core/Engine/Autoload.class.php";
$autoload = AutoLoad::getInstance();

// Error Handler
ErrorHandler::getInstance()->register();

class config
{
	public static function getValuesConfig()
	{
		$values = array();
		$values['xmlnuke.ROOTDIR'] = realpath(__DIR__ . '/../../..');
		$values['xmlnuke.PHPXMLNUKEDIR'] = realpath(__DIR__ . '/../..');
		$values['xmlnuke.USEABSOLUTEPATHSROOTDIR'] = true;
		$values['xmlnuke.XSLCACHE'] = '\Xmlnuke\Core\Cache\NoCacheEngine';
		$values['xmlnuke.SMTPSERVER'] = '';
		$values['xmlnuke.USEFULLPARAMETER'] = true;
		$values['xmlnuke.USERSDATABASE'] = '';
		$values['xmlnuke.USERSCLASS'] = '';
		$values['xmlnuke.LOGINMODULE'] = 'Xmlnuke.Login';
		$values['xmlnuke.EXTERNALSITEDIR'] =
				array();
		$values['xmlnuke.PHPLIBDIR'] =
				array();
		$values['xmlnuke.URLXMLNUKEENGINE'] = 'xmlnuke.php';
		$values['xmlnuke.URLMODULE'] = 'xmlnuke.php';
		$values['xmlnuke.URLXMLNUKEADMIN'] = 'xmlnukeadmin.php';
		$values['xmlnuke.DEFAULTSITE'] = 'teste';
		$values['xmlnuke.DEFAULTPAGE'] = 'page';
		$values['xmlnuke.URLBASE'] = '';
		$values['xmlnuke.DETECTMOBILE'] = true;
		$values['xmlnuke.SHOWCOMPLETEERRORMESSAGES'] = true;
		$values['xmlnuke.LANGUAGESAVAILABLE'] =
				array(
					'en-us' => 'English'
				);
		$values['xmlnuke.DEBUG'] = false;
		$values['xmlnuke.DEVELOPMENT'] = true;
		$values['xmlnuke.CAPTCHACHALLENGE'] = 'hard';
		$values['xmlnuke.CAPTCHALETTERS'] = '5';
		$values['xmlnuke.ENABLEPARAMPROCESSOR'] = true;
		$values['xmlnuke.CHECKCONTENTTYPE'] = true;
		$values['xmlnuke.CACHESTORAGEMETHOD'] = 'PLAIN';
		$values['xmlnuke.XMLSTORAGEMETHOD'] = 'PLAIN';
		$values['xmlnuke.RESTRICTACCESS'] = '';
		$values['xmlnuke.OUTPUT_FORMAT'] = '';
		$values['xmlnuke.POST_PROCESS_RESULT'] = '\Xmlnuke\Core\Processor\BaseProcessResult';
		return $values;
	}
}
## END-OF-FILE

?>


?>
