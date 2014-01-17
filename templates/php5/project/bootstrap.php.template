<?php
/**
 * Contains all commands necessary to run XMLNuke in PHP Unit CLI
 *
 * How to Run
 *
 * Command LINE:
 * ------------------------------------------------------------------------
 * cd <my_project root>/lib/Tests
 * phpunit --configuration configuration.xml <path_to_test>
 *
 * OR Calling Netbeans
 *
 */

$_SERVER['SERVER_SOFTWARE'] = 'CLI';
$_SERVER['SCRIPT_NAME'] = '';
$_SERVER['QUERY_STRING'] = '';
$_SERVER['REQUEST_URI'] = '/';

define('PHPUNIT_BASE_ROOT', '../../httpdocs');

set_include_path(get_include_path() . PATH_SEPARATOR . PHPUNIT_BASE_ROOT);

if (!file_exists(PHPUNIT_BASE_ROOT . '/config.inc.php') || !file_exists(PHPUNIT_BASE_ROOT . '/xmlnuke.inc.php'))
	die("ERROR: You need configure your httpdocs paths with the config.inc.php and xmlnuke.inc.php files");

include_once('config.inc.php');
include_once('xmlnuke.inc.php');
