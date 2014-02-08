<?php

use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Util\FileUtil;

## CHECK CONFIG.PHP ##
if (!file_exists("config.inc.php")) { header("Location: check_install.php"); exit(); }
## CHECK CONFIG.PHP ##

#############################################
# To create a XMLNuke capable PHP5 page
#
require_once("xmlnuke.inc.php");
#############################################

$context = Context::getInstance();

## CHECK CONFIG.PHP ##
if ($context->get("xmlnuke.ROOTDIR")=="")
{
	header("Location: check_install.php");
	exit();
}
else
{
	$perms = fileperms("config.inc.php");
	if ( ($perms & 0x0080) || ($perms & 0x0010) || ($perms & 0x002) )
	{
		Context::getInstance()->WriteWarningMessage("<font color=red>Security failure</font>!</b> The file 'config.inc.php' must be readonly.");
	}
	if ( file_exists("check_install.php") )
	{
		Context::getInstance()->WriteWarningMessage("<font color=red>Security failure</font>!</b> You have to delete the file 'check_install.php'.");
	}
}
## CHECK CONFIG.PHP ##

define("MAX_COLS", 3);
?>
<html>
<head>
  <title>XMLNuke Site Selector</title>
	<style>
		BODY
		{
			FONT-WEIGHT: normal; FONT-SIZE: 16px; FONT-FAMILY: Verdana, Helvetica, sans-serif
		}
		H1
		{
			FONT-WEIGHT: bold; FONT-SIZE: 20px
		}
	</style>
</head>
<body>
  <table cellspacing="1" cellpadding="3" bordercolor="White" border="0" style="background-color:White;border-color:White;border-width:2px;border-style:Ridge;">
	<tr>
		<td colspan="<?php echo MAX_COLS?>" style="color:#E7E7FF;background-color:#4A3C8C;font-size:Large;font-weight:bold;">
                XMLNuke Site Selector
    </td>
	</tr>
	<tr>
	<td align="Center" valign="Middle" style="color:Black;background-color:#DEDFDE;">
		<a href="<?php echo Context::getInstance()->get('xmlnuke.URLXMLNUKEENGINE'); ?>" style="font-size: XX-Small">
			<img src="common/icons/.png" alt="" onerror="this.onerror=null; this.src='common/icons/.png'"><br>
			== Access this site ==
		</a>
		<br>
		<a href="<?php echo Context::getInstance()->get('xmlnuke.URLXMLNUKEADMIN'); ?>" style="font-size: XX-Small">
			Admin
		</a>
	</td>
	</tr>
  </table>
</body>
</html>