<?php
## CHECK CONFIG.PHP ##
if (!file_exists("config.inc.php")) { header("Location: check_install.php"); exit(); } 
## CHECK CONFIG.PHP ##

#############################################
# To create a XMLNuke capable PHP5 page
#
require_once("xmlnuke.inc.php");
#############################################

## CHECK CONFIG.PHP ##
if ($context->ContextValue("xmlnuke.ROOTDIR")=="") 
{
	header("Location: check_install.php"); 
	exit(); 
}
else 
{
	$perms = fileperms("config.inc.php");
	if ( ($perms & 0x0080) || ($perms & 0x0010) || ($perms & 0x002) )
	{
		echo "\n<br><b>Warning! <font color=red>Security failure</font>!</b> The file 'config.inc.php' must be readonly.<br>\n";
	}
	if ( file_exists("check_install.php") )
	{
		echo "\n<br><b>Warning! <font color=red>Security failure</font>!</b> You have to delete the file 'check_install.php'.<br>\n";
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
<?php
$context = new Context();
$arr = $context->ExistingSites();
$count = 0;

foreach($arr as $key)
{
	if (basename($key) != '.svn')
	{
		$site = FileUtil::ExtractFileName($key);
		escreveColuna($site);
		$count = $count + 1;
		if ($count%MAX_COLS == 0)
		{
			echo "</tr><tr>";
		}
	}
}
for($i=($count%MAX_COLS);$i<MAX_COLS;$i++)
{
		echo "<td style='color:Black;background-color:#DEDFDE;'>&nbsp;</td>";
}
?>
	</tr>
	<tr>
		<td colspan="<?php echo MAX_COLS?>" align="Right" style="color:Black;background-color:#C6C3C6;font-size:X-Small;font-weight:bold;">
			<?php echo getVersion()?>
			<br />
			Platform: <?php echo PHP_OS ?>
		</td>
	</tr>
	</table>
</body>
</html>


<?php
// ============= FUNCOES ====================

function getUrlImage($site)
{
	return "common/icons/$site.png";	
}

function getUrlEnterSite($site)
{
	global $context;

	$targetPage = $context->UrlXmlNukeEngine() . "?xml=home";	
	return  $targetPage . "&site=" . $site . "&xsl=page";
}

function getUrlAdmin($site)
{
	global $context;
	$targetAdmin = $context->UrlXmlNukeAdmin();
	
	return  $targetAdmin . "?site=" . $site;
}

function getVersion()
{
	global $context;
	return $context->XmlNukeVersion();
}


function escreveColuna($site)
{
	?>
	<td align="Center" valign="Middle" style="color:Black;background-color:#DEDFDE;">
		<a href="<?php echo getUrlEnterSite($site)?>" style="font-size: XX-Small">
			<img src="<?php echo getUrlImage($site)?>" alt="<?php echo $site?>" onerror="this.onerror=null; this.src='common/icons/.png'"><br>
			== Access this site ==
		</a>
		<br>
		<a href="<?php echo getUrlAdmin($site)?>" style="font-size: XX-Small">
			Admin
		</a>
	</td>
	<?php
}
?>
