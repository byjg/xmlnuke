<HTML>
<title>XMLNuke Check Install and Setup Manager</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">

<img src="common/imgs/logo_xmlnuke2.gif" border="0">

<style type="text/css">
body {background-color: #ffffff; color: #000000;}
body, td, th, h1, h2 {font-family: arial;}
pre {margin: 0px; font-family: monospace;}
a:link {color: #000099; text-decoration: none; background-color: #ffffff;}
a:hover {text-decoration: underline;}
table {border-collapse: collapse;}
.center {text-align: center;}
.center table { margin-left: auto; margin-right: auto; text-align: left;}
.center th { text-align: center !important; }
td, th { border: 1px solid #000000; font-size: 75%; vertical-align: baseline;}
h1 {font-size: 150%;}
h2 {font-size: 125%;}
.p {text-align: left;}
.e {background-color: #ccccff; font-weight: bold; color: #000000;}
.h {background-color: #9999cc; font-weight: bold; color: #000000;}
.v {background-color: #cccccc; color: #000000;}
.vr {background-color: #cccccc; text-align: right; color: #000000;}
hr {width: 600px; background-color: #cccccc; border: 0px; height: 1px; color: #000000;}
</style>

<?php
if (file_exists("config.inc.php"))
{
	include_once("config.inc.php");
}


$action = @$_GET["action"];

if ($action == "")
{
	WelcomeMessage();
}
elseif ($action == "check")
{
	CheckInstall();
}
elseif ($action == "config")
{
	EditConfig();
}

####
#### ACTION UTILITIES
####

function WelcomeMessage()
{
	echo "<h1>Install Validation</h1>";
	echo "Check if your PHP install have all required extension and setup. <br><br>"	;
	echo "<a href='check_install.php?action=check'>Click here to continue</a>";
}

function CheckInstall()
{
	$failed = false;

	echo "<h1>Result Tests</h1>";
	echo "<h2>PHP Version</h2>";
	echo "<ul>";
	echo "<li>" . phpversion() . " ";
	if (version_compare(phpversion(), "5.0.0", ">"))
	{
		echo "<font color='green'><b>Passed! </b>";
	}
	else
	{
		echo "<font color='red'><b>Failed! </b>Required minimum 5.0.0!";
		exit;
	}
	echo "</font></li>";
	echo "</ul>";

	echo "<h2>Required Extensions</h2><ul>";
	$required_ext =
		array(
			"session"=>"Core PHP",
			"Reflection"=>"Core PHP",
			"date"=>"Core PHP",
			"standard"=>"Core PHP",
			"libxml"=>"Core Xmlnuke",
			"dom"=>"Core Xmlnuke",
			"xml"=>"Core Xmlnuke",
			"xmlreader"=>"Core Xmlnuke",
			"xmlwriter"=>"Core Xmlnuke",
			"xsl"=>"Core Xmlnuke",
			"PDO"=>"Database Access (you must select one driver below)",
			"gd" => "For Captcha messages"
		);

	foreach ($required_ext as $key=>$value)
	{
		echo "<li>";
		if (extension_loaded($key))
		{
			echo "<font color='green'><b>Passed! </b>";
		}
		else
		{
			echo "<font color='red'><b>Failed! </b>";
			$failed = true;
		}
		echo $key . " (" . $value . ")</font></li>";
	}
	echo "</ul>";

	echo "<h2>Uninstall Extensions</h2><ul>";
	$required_ext =
		array(
			"domxml"=>"PHP Dom XML"
		);

	foreach ($required_ext as $key=>$value)
	{
		echo "<li>";
		if (!extension_loaded($key))
		{
			echo "<font color='green'><b>Already uninstalled! </b>";
		}
		else
		{
			echo "<font color='red'><b>Failed! You must uninstall this extension before proceed.</b>";
			$failed = true;
		}
		echo $key . " (" . $value . ")</font></li>";
	}
	echo "</ul>";


	echo "<h2>Optional Extensions</h2><ul>";
	$optional_ext =
		array(
			"mbstring" => "Improve performance in PHP. **Check it!**",
			"zlib" => "For backup and install modules",
			"pdo_dblib"=>"FreeTDS/Sybase/MSSQL driver for PDO",
			"pdo_firebird "=>"Firebird/InterBase 6 driver for PDO",
			"pdo_ibm"=>"PDO driver for IBM databases",
			"pdo_informix"=>"PDO driver for IBM Informix INFORMIX databases",
			"pdo_mysql"=>"MySQL driver for PDO",
			"pdo_oci"=>"Oracle Call Interface driver for PDO",
			"pdo_odbc"=>"ODBC v3 Interface driver for PDO",
			"pdo_pgsql"=>"PostgreSQL driver for PDO",
			"pdo_sqlite"=>"SQLite v3 Interface driver for PDO"
		);
	foreach ($optional_ext as $key=>$value)
	{
		echo "<li>";
		if (extension_loaded($key))
		{
			echo "<font color='green'><b>Found! </b>";
		}
		else
		{
			echo "<font color='#FF9900'><b>Not Found! </b>";
			//$failed = true;
		}
		echo "$key ($value) </font></li>";
	}
	echo "</ul>";

	$phpini = array("memory_limit" => "64M");
	echo "<h2>Required PHP.INI sets</h2><ul>";
	$failed = (validateIniSection($phpini, true) || $failed);
	echo "</ul>";

	$phpini = array("file_uploads" => true, "post_max_size"=>"5M", "register_globals" => false, "magic_quotes_gpc" => false, "register_long_arrays" => false, "register_argc_argv"=>false, "implicit_flush"=>false);
	echo "<h2>Optional PHP.INI sets</h2><ul>";
	validateIniSection($phpini, false);
	echo "</ul>";

	if ($failed)
	{
		echo "<br><b><font color='red'>Your system doesn't have the minimum requirements to run the XMLNuke. Please check your setup and try again</b></font>";
	}
	else
	{
		echo "<br><font color='green'><b>Congratulations!!</b>. Your system is ready to go with XMLNuke.</font><br><br>";
		echo "<center><a href='check_install.php?action=config'><font color='green'><b> == Click here to create your Config File ==</b></font></a></center>";
	}
}

function EditConfig()
{
	echo "<h1>Edit Configuration File</h1>";

	if (@$_POST["xmlnuke_ROOTDIR"] != '')
	{
		$errors = createConfigFile();
		if (sizeof($errors) > 0)
		{
			echo "<font color='red'><b>Some errors are found. Config file was not saved! Please check it and try again</b></font><ul>";
			foreach ($errors as $value)
			{
				echo "<li>$value</li>";
			}
			echo "</ul>";
		}
		else
		{
			echo "<font color='green'><b>Data sucessful updated. <a href='?action=config'>Click here to reload data</a></b></font><br>";
			echo "<a href='index.php'>Click here to go to Xmlnuke index page</a><br><br>";
			exit;
		}
	}

	$failed = true;
	if (class_exists("config", false))
	{
		$configValues = config::getValuesConfig();
		$failed = false;
	}

	if ($failed)
	{
		// Default Values
		$configValues = array();
		$configValues["xmlnuke.ROOTDIR"]="data";
		$configValues["xmlnuke.USEABSOLUTEPATHSROOTDIR"] = false;
		$configValues["xmlnuke.URLMODULE"]="xmlnuke.php";
		$configValues["xmlnuke.URLXMLNUKEADMIN"]="xmlnukeadmin.php";
		$configValues["xmlnuke.URLXMLNUKEENGINE"]="xmlnuke.php";
		$configValues["xmlnuke.DEFAULTSITE"]="sample";
		$configValues["xmlnuke.DEFAULTPAGE"]="page";
		$configValues["xmlnuke.LOGINMODULE"]="login";
		$configValues["xmlnuke.URLBASE"]="";
		$configValues["xmlnuke.ALWAYSUSECACHE"]=true;
		$configValues["xmlnuke.SHOWCOMPLETEERRORMESSAGES"]=true;
		$configValues["xmlnuke.LANGUAGESAVAILABLE"]="en-us=English (United States)|pt-br=Português (Brasil)";
		$configValues["xmlnuke.SMTPSERVER"]="localhost";
		$configValues["xmlnuke.USERSDATABASE"]="";
		$configValues["xmlnuke.USERSCLASS"]="";
		$configValues["xmlnuke.DEBUG"] = false;
		$configValues["xmlnuke.CAPTCHACHALLENGE"] = "hard";
		$configValues["xmlnuke.CAPTCHALETTERS"] = 5;
		$configValues["xmlnuke.ENABLEPARAMPROCESSOR"] = true;
		$configValues["xmlnuke.USEFULLPARAMETER"] = true;
		$configValues["xmlnuke.CHECKCONTENTTYPE"] = true;
		$configValues["xmlnuke.CACHESTORAGEMETHOD"] = "PLAIN";
		$configValues["xmlnuke.XMLSTORAGEMETHOD"] = "PLAIN";
		$configValues["xmlnuke.EXTERNALSITEDIR"] = "";
		$configValues["xmlnuke.PHPLIBDIR"] = "";
		$configValues["xmlnuke.PHPXMLNUKEDIR"] = "";

		if (file_exists("config.default.php"))
		{
		        include_once("config.default.php");
		}

	}

	$langs = array(
		'',
		'pt-br=Português (Brasil)',
		'en-us=English (United States)',
		'fr-fr=Français',
		'it-it=Italiano',
		'',
		'ar-dz=جزائري عربي',
		'bg-bg=Български',
		'ca-es=Català',
		'cs-cz=Čeština',
		'da-dk=Dansk',
		'de-de=Deutsch',
		'el-gr=Ελληνικά',
		'en-gb=English (Great Britain)',
		'es-es=Español',
		'et-ee=Eesti',
		'fi-fi=Suomi',
		'gl-gz=Galego',
		'he-il=עברית',
		'hu-hu=Magyar',
		'id-id=Bahasa Indonesia',
		'is-is=Íslenska',
		'ja-jp=Japanese',
		'lv-lv=Latviešu',
		'nl-nl=Nederlands',
		'no-no=Norsk',
		'pl-pl=Polski',
		'pt-pt=Português (Portugal)',
		'ro-ro=Română',
		'ru-ru=Русский',
		'sk-sk=Slovenčina',
		'sv-se=Svenska',
		'th-th=Thai',
		'uk-ua=Українська',
		'zh-cn=Chinese (Simplified)',
		'zh-tw=Chinese (Traditional)',
	);

	echo "<form method='post' action='?action=config'>";
	echo "<table width='600'><tr><td>";

	writeInputData($configValues, "xmlnuke.ROOTDIR",
		"Path where Snippets and sites are located (Don't add Slashs on the end). " .
		" This Path can be VIRTUAL ou ABSOLUTE (e.g. C:\XMLNuke\Sites).  " .
		" Set the parameter USEABSOLUTEPATHSROOTDIR to determine the behavior", 1);

	writeInputData($configValues, "xmlnuke.USEABSOLUTEPATHSROOTDIR",
		"Defines how the property <i>xmlnuke.ROOTDIR</i> will understand the directory. " .
		"If false, the directory is relative from current directory. " .
		"If true, the directory is an absolute path. ", 2);

	writeInputData($configValues, "xmlnuke.URLXMLNUKEENGINE",
		"The script name of XMLNuke front controller for run xmlnuke static pages", 1);

	writeInputData($configValues, "xmlnuke.URLMODULE",
		"The script name of XMLNuke front controller for execute modules", 1);

	writeInputData($configValues, "xmlnuke.URLXMLNUKEADMIN",
		"The script name of XMLNukeAdmin front controller", 1);

	writeInputData($configValues, "xmlnuke.DEFAULTSITE",
		"Default site name", 1);

	writeInputData($configValues, "xmlnuke.DEFAULTPAGE",
		"Default XSL Style", 1);

	writeInputData($configValues, "xmlnuke.LOGINMODULE",
		"Default Login Module", 1);

	writeInputData($configValues, "xmlnuke.URLBASE",
		"Define the base URL of XMLNuke installation. For example: " .
		"http://www.somesite.com/xmlnuke-php5/. " .
		"This is optional and you can safely leave blank this parameter.", 1);

	writeInputData($configValues, "xmlnuke.ALWAYSUSECACHE",
		"Enable/Disable the cache system on XMLNuke. Avoid set this option to false. " .
		"This option is useful when you cannot change the write permissions, ".
		"but the system will be slower", 2);

	writeInputData($configValues, "xmlnuke.SHOWCOMPLETEERRORMESSAGES",
		"Show complete and usefull information for debug. Disable this option in production environments", 2);

	writeInputData($configValues, "xmlnuke.LANGUAGESAVAILABLE",
		"Default set of Languages XMLNuke Expected to find. This set may override at admin " .
		"tool CustomConfig", 9999, $langs);

	writeInputData($configValues, "xmlnuke.SMTPSERVER",
		"Smtpserver. Smtp Server. You can use the format smtp://USER:PASS@SERVER:PORT for sending from an valid SMTP server;  " .
		"define a SERVERNAME or leave blank for use the sendmail PHP method.", 1);

	writeInputData($configValues, "xmlnuke.USERSDATABASE",
		"Where XMLNuke look up for the users. Leave empty to store in single XML, or put a value " .
		"for a valid connection string in XMLNuke.", 1);

	writeInputData($configValues, "xmlnuke.USERSCLASS",
		"XMLNuke will use this class for access custom access users. Empty values uses the default class. ", 1);

	writeInputData($configValues, "xmlnuke.DEBUG",
		"Put XMLNuke in Debug mode", 2);

    writeInputData($configValues, "xmlnuke.CAPTCHACHALLENGE",
    	"How will be the captcha challenge question.", 3,
    	array("easy" => "Easy", "hard" => "Hard"));

    writeInputData($configValues, "xmlnuke.CAPTCHALETTERS",
    	"How many letters will be use to build the captcha", 3,
    	array("5" => "5", "6" => "6", "7" => "7", "8" => "8", "9" => "9", "10" => "10"));

    writeInputData($configValues, "xmlnuke.ENABLEPARAMPROCESSOR",
		"Enable or disable the PARAMPROCESSOR diretive. ".
		"ParamProcessor enable post processing on your XML/XSL transformed looking for [param:....]. " .
		"This feature, if enable, uses resources from your Web Server causing low performance. ".
		"TIP: If high performance is critical, set to false this option. ", 2);

	writeInputData($configValues, "xmlnuke.USEFULLPARAMETER",
		"If true, XMLNuke will complete all basic parameters (xml, xsl, site and lang). " .
		"If false, XMLNuke will complete only the values are different from default values ", 2);

	writeInputData($configValues, "xmlnuke.CHECKCONTENTTYPE",
		"XMLNuke can check if a XSL transformation generate a document with a specific type. " .
		"The relation between XSL and content is located at: setup/content-type.anydata.xml ", 2);

    writeInputData($configValues, "xmlnuke.CACHESTORAGEMETHOD",
    	"How XMLNuke will be store the cache in filesystem", 3,
    	array("PLAIN" => "Plain(Flat) directory", "HASHED" => "Hashed Directory Structure"));

    writeInputData($configValues, "xmlnuke.XMLSTORAGEMETHOD",
    	"How XMLNuke will be store XML documents in filesystem", 3,
    	array("PLAIN" => "Plain(Flat) directory", "HASHED" => "Hashed Directory Structure"));

    writeInputData($configValues, "xmlnuke.EXTERNALSITEDIR",
    	"Sets the path for sites that are not stored within the structure of xmlnuke.ROOTDIR. " .
    	"You need to configure a pair of values in this option. The first value defines the name of the site,  " .
    	"and the second defines the physical path of the site. You can safely leave this option blank.", 4);

    writeInputData($configValues, "xmlnuke.PHPLIBDIR",
    	"Defines the search path directory for USER LIB generated projects. You need to configure a pair of values in this option. " .
    	"The first value defines the namespace prefix and the second defines the physical path of the files. " .
    	"If you are developing your own modules, you should consider to use this option", 4);

	writeInputData($configValues, "xmlnuke.PHPXMLNUKEDIR",
		"Path where all XMLNuke PHP5 files live. You need set this option if are using " .
		" XMLNuke from subversion repository", 1);

    echo "</td></tr></table>";

	echo "<input type='submit' value='Create Config File'></form>";

}


####
#### UTILITiES FUNCTION
####

function validateIniSection($phpini, $required)
{
	$failed = false;
	foreach ($phpini as $key=>$value)
	{
		echo "<li>";
		$keyCmp = parseValue(ini_get($key));
		$valueCmp = parseValue($value);
		if (gettype($valueCmp) == "string" && gettype($keyCmp) == "string")
			$result = $keyCmp == $valueCmp;
		elseif (is_numeric($valueCmp))
			$result = intval($keyCmp) >= intval($valueCmp);
		else
			$result = $keyCmp == $valueCmp;

		if ($result)
		{
			echo "<font color='green'><b>Passed! </b>";
		}
		else
		{
			if ($required)
				echo "<font color='red'><b>Failed! </b>";
			else
				echo "<font color='#FF9900'><b>Not Found! </b>";

			$failed = true;
		}

		$iniGet = ini_get($key);
		if (is_bool($value))
		{
			$value = ($value ? "On" : "Off");
			$iniGet = ($iniGet ? "On" : "Off");
		}

		echo "$key (Required: $value; Found: " . $iniGet . ") </font></li>";
	}
	return $failed;
}

function parseValue($value)
{
	if (is_bool($value))
		return $value;

	if (strlen($value) < 2)
		return $value;

	if ($value[strlen($value)-1] == "M")
		$value = intval(substr($value, 0, strlen($value)-1)) * 1024;

	return $value;
}

function writeInputData($configValues, $name, $desc, $type, $list = null)
{
	$curValue = @$configValues[$name];
	echo "<b>$name</b><br>";
	echo "$desc<br>";

	$name = str_replace(".", "_", $name);

	if ($type == 1)
	{
		echo "<input type='text' name='$name' value='$curValue'>";
	}
	elseif ($type == 2)
	{
		echo "<select name='$name'>";
		echo "<option value='true'" . ($curValue ? "selected" : "") . ">True</option>";
		echo "<option value='false'" . (!$curValue ? "selected" : "") . ">False</option>";
		echo "</select>";
	}
	elseif ($type == 3)
	{
		echo "<select name='$name'>";
		writeOptionList($list, $curValue);
		echo "</select>";
	}
	elseif ($type == 4)
	{
		$i = 0;
		if ($curValue != "")
		{
			$pairItemArray = explode("|", $curValue);
			foreach ($pairItemArray as $pairItem)
			{
				$pair = explode("=", $pairItem);
				echo "<input type='text' name='" . $name . $i . "_key' value='$pair[0]' size='8'>";
				echo " = <input type='text' name='" . $name . $i . "_value' value='$pair[1]' size='40'><br/>";
				$i++;
			}
		}
		for ($j=0; $j<3; $j++)
		{
			echo "<input type='text' name='" . $name . $i . "_key' value='' size='8'>";
			echo " = <input type='text' name='" . $name .$i . "_value' value='' size='40'><br/>";
			$i++;
		}
		echo "<input type='hidden' name='$name' value='$i'>";
	}
	elseif ($type == 9999)
	{
		$curValueArray = explode("|", $curValue);
		foreach ($curValueArray as $key=>$value)
		{
			echo "<select name='$name$key'>";
			writeOptionList($list, $value, true);
			echo "</select><br>";
		}
		echo "<select name='$name" . ++$key . "'>";
		writeOptionList($list, '', true);
		echo "</select><br>";
		echo "<select name='$name" . ++$key . "'>";
		writeOptionList($list, '', true);
		echo "</select><br>";
		echo "<input type='hidden' name='$name' value='$key'>";
	}

	echo "<br><br>";
}

function writeOptionList($list, $default, $keyEqualValue = false)
{
	foreach ($list as $key=>$value)
	{
		if ($keyEqualValue)
		{
			echo "<option value='$value'" . ($value == $default ? "selected" : "") . ">$value</option>";
		}
		else
		{
			echo "<option value='$key'" . ($key == $default ? "selected" : "") . ">$value</option>";
		}
	}
}

function createConfigFile()
{
	$errors = array();

	$fileContent =
		"## CONFIG FILE AUTO-GENERATED on " . date('c') . "\n" .
		"class config \n" .
		"{ \n" .
		"	public static function getValuesConfig()\n" .
		"	{\n" .
		"		\$values = array();\n";

	foreach ($_POST as $key=>$value)
	{
		$name =  "'" . str_replace('_', '.', $key) . "'";

		if ($key == 'xmlnuke_ROOTDIR')
		{
			if (!file_exists($value))
			{
				$errors[] = "Directory '$value' defined in 'xmlnuke.ROOTDIR' does not exists";
			}
			elseif (!is_writeable($value))
			{
				$errors[] = "Directory '$value' and its subdirectories must be write able in order to complete XMLNuke setup. Check this and try again.";
			}
		}
		elseif ($key == 'xmlnuke_LANGUAGESAVAILABLE')
		{
			$qty = intval($_POST[$key]);
			$value = "";
			for($i=0; $i<=$qty; $i++)
			{
				if ($_POST[$key . $i] != "") $value .= ($value!="" ? "|" : "") . $_POST[$key . $i];
			}
		}
		elseif ( ($key == 'xmlnuke_EXTERNALSITEDIR') || ($key == 'xmlnuke_PHPLIBDIR') )
		{
			$qty = intval($_POST[$key]);
			$value = "";
			for($i=0; $i<$qty; $i++)
			{
				$siteName = $_POST[$key . $i . "_key"];
				if ($siteName != "")
				{
					$sitePath = $_POST[$key . $i . "_value"];
					if (!file_exists($sitePath))
					{
						$errors[] =  "The config option '$key' has a hey '$siteName' which defines a directory '$sitePath' that does not exists.";
					}
					$value .= ($value!="" ? "|" : "") . $siteName . "=" . $sitePath;
				}
			}
		}
		elseif (($key == 'xmlnuke_PHPXMLNUKEDIR') && ($value != ""))
		{
			if (!file_exists($value))
			{
				$errors[] = "Directory '$value' defined in 'xmlnuke.PHPXMLNUKEDIR' does not exists";
			}
		}
		elseif ((strpos($key, 'xmlnuke_LANGUAGESAVAILABLE')!==false) ||
				(strpos($key, 'xmlnuke_EXTERNALSITEDIR')!==false) ||
				(strpos($key, 'xmlnuke_PHPLIBDIR')!==false) )
		{
			continue;
		}

		if ( ($value != "false") && ($value != "true") )
			$value = "'$value'";
		$fileContent .= "		\$values[$name] = $value;\n";
	}

	$fileContent .= "		return \$values;\n";
	$fileContent .= "	}\n" ;
	$fileContent .= "}\n" ;
	$fileContent .= "define('PHPXMLNUKEDIR', '" . ($_POST["xmlnuke_PHPXMLNUKEDIR"] != "" ? $_POST["xmlnuke_PHPXMLNUKEDIR"] . "/" : "") . "');\n";
	$fileContent .= "## END-OF-FILE\n" ;

	//echo "<pre>";
	//echo $fileContent;
	//echo "</pre>";

	if (sizeof($errors) == 0)
	{
		if (!is_writeable("config.inc.php"))
		{
			$errors[] = "'config.inc.php' must be write able in order to complete the setup<br/><br/>";
		}
		else
		{
			@file_put_contents("config.inc.php", "<?php\n$fileContent\n?>");
			$err = error_get_last();
			if ((intval($err["type"]) == 1) || (intval($err["type"]) == 2))
			{
				$errors[] = $err["message"];
			}
		}
	}

	return $errors;
}

?>



</HTML>
