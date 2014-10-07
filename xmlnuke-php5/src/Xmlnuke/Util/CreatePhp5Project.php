<?php

namespace Xmlnuke\Util;

use Exception;

class CreatePhp5Project
{
	public static function Run($arg = null)
	{

		if (func_num_args() < 4)
			throw new Exception('I expected at least 4 parameters: file, homepath, projectname and language ');

		$argc = func_num_args();
		$argv = func_get_args();

		$HOME = $argv[1];
		$HTTPDOCS = $argv[1] . "/httpdocs";
		$PROJECT = $argv[2];
		if (!preg_match('~^[A-Za-z]([A-Za-z0-9])*$~', $PROJECT))
			throw new Exception('Project musct contain only letters and numbers and start with a letter');
		$PROJECT_FILE = strtolower($PROJECT);
		$XMLNUKE = dirname(dirname(dirname(dirname(__DIR__))));
		$TEMPLATE = "$XMLNUKE/templates/php5";

		$PHPDIR = "$XMLNUKE" . DIRECTORY_SEPARATOR . "xmlnuke-php5";
		$DATADIR = "$XMLNUKE" . DIRECTORY_SEPARATOR . "xmlnuke-data";

		if ( file_exists($PHPDIR) )
		{
			if ( ! file_exists($DATADIR) )
			{
				$DATADIR = "$PHPDIR" . DIRECTORY_SEPARATOR . "data";
				if ( ! file_exists( $DATADIR ) )
				{
					throw new Exception("XMLNuke data dir '$DATADIR' not found!!! Cannot continue.");
				}
			}

			if ( file_exists($HOME) )
			{

				# Creating 'data' Folders
				@mkdir( "$HOME/data" );
				@mkdir( "$HOME/data/anydataset" );
				@mkdir( "$HOME/data/cache" );
				@mkdir( "$HOME/data/lang" );
				@mkdir( "$HOME/data/offline" );
				@mkdir( "$HOME/data/xml" );
				@mkdir( "$HOME/data/xsl" );
				@mkdir( "$HOME/data/snippet" );

				# Creating 'httpdocs' folders
				@mkdir( "$HTTPDOCS" );
				@mkdir( "$HTTPDOCS/static" );
				@mkdir( "$HTTPDOCS/static/js" );
				@mkdir( "$HTTPDOCS/static/css" );
				@mkdir( "$HTTPDOCS/static/img" );

				# Creating Empty files for Static
				CreatePhp5Project::writeToFile( "$HTTPDOCS/static/js/_empty", "-- EMPTY --") ;
				CreatePhp5Project::writeToFile( "$HTTPDOCS/static/css/_empty", "-- EMPTY --") ;
				CreatePhp5Project::writeToFile( "$HTTPDOCS/static/img/_empty", "-- EMPTY --") ;

				# Creating Empty files for Data
				CreatePhp5Project::writeToFile( "$HOME/data/anydataset/.do_not_remove", "-- DO NOT REMOVE THIS DIRECTORY --") ;
				CreatePhp5Project::writeToFile( "$HOME/data/cache/.do_not_remove", "-- DO NOT REMOVE THIS DIRECTORY --") ;
				CreatePhp5Project::writeToFile( "$HOME/data/lang/.do_not_remove", "-- DO NOT REMOVE THIS DIRECTORY --") ;
				CreatePhp5Project::writeToFile( "$HOME/data/offline/.do_not_remove", "-- DO NOT REMOVE THIS DIRECTORY --") ;
				CreatePhp5Project::writeToFile( "$HOME/data/xml/.do_not_remove", "-- DO NOT REMOVE THIS DIRECTORY --") ;
				CreatePhp5Project::writeToFile( "$HOME/data/xsl/.do_not_remove", "-- DO NOT REMOVE THIS DIRECTORY --") ;
				CreatePhp5Project::writeToFile( "$HOME/data/snippet/.do_not_remove", "-- DO NOT REMOVE THIS DIRECTORY --") ;

				# Creating International Static Files
				$langs = array();
				$i = 3;
				while ( $i < $argc )
				{
					$langs[] = "'" . $argv[$i] . "' => '" . $argv[$i] . "'";

					@mkdir( "$HOME/data/xml/" . $argv[$i] );
					copy( "$TEMPLATE/project/index.xsl.template", "$HOME/data/xsl/index." . $argv[$i] . ".xsl" );
					copy( "$TEMPLATE/project/page.xsl.template", "$HOME/data/xsl/page." . $argv[$i] . ".xsl" );
					copy( "$TEMPLATE/project/index.xml.template", "$HOME/data/xml/" . $argv[$i] . "/index." . $argv[$i] . ".xml" );
					copy( "$TEMPLATE/project/home.xml.template", "$HOME/data/xml/" . $argv[$i] . "/home." . $argv[$i] . ".xml" );
					copy( "$TEMPLATE/project/notfound.xml.template", "$HOME/data/xml/" . $argv[$i] . "/notfound." . $argv[$i] . ".xml" );

					CreatePhp5Project::writeToFile( "$HOME/data/xml/" . $argv[$i] . "/index.php.btree", "xmlnuke\n+home." . $argv[$i] . ".xml" );
					$i++;
				}

				$LANGUAGESAVAILABLE = implode(", \n\t\t\t\t\t", $langs);

				# Create Project Related Files
				@mkdir( "$HOME/lib" );
				@mkdir( "$HOME/lib/$PROJECT" );

				@mkdir( "$HOME/lib/$PROJECT/Modules" );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/project/module.php.template", "$HOME/lib/$PROJECT/Modules/Home.class.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );

				@mkdir( "$HOME/lib/$PROJECT/Base" );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/project/adminbasemodule.php.template", "$HOME/lib/$PROJECT/Base/AdminBaseModule.class.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/project/basedbaccess.php.template", "$HOME/lib/$PROJECT/Base/BaseDBAccess.class.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/project/basemodel.php.template", "$HOME/lib/$PROJECT/Base/BaseModel.class.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/project/basemodule.php.template", "$HOME/lib/$PROJECT/Base/BaseModule.class.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/project/baseuiedit.php.template", "$HOME/lib/$PROJECT/Base/BaseUIEdit.class.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );

				# Create Project PHPUnit Related Files
				@mkdir( "$HOME/lib/Tests" );
				@mkdir( "$HOME/lib/Tests/$PROJECT" );
				@mkdir( "$HOME/lib/Tests/$PROJECT/Classes" );
				@mkdir( "$HOME/lib/$PROJECT/Classes" );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/project/bootstrap.php.template", "$HOME/lib/Tests/bootstrap.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/project/configuration.xml.template", "$HOME/lib/Tests/configuration.xml", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/project/sample.php.template", "$HOME/lib/$PROJECT/Classes/Sample.class.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/project/sampletest.php.template", "$HOME/lib/Tests/$PROJECT/Classes/SampleTest.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );

				# Netbeans project specific
				@mkdir( "$HOME/nbproject" );
				@mkdir( "$HOME/nbproject/private" );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/project/project.properties.template", "$HOME/nbproject/project.properties", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/project/project.xml.template", "$HOME/nbproject/project.xml", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );

				# Anydataset Files
				$aux = "";
				$aux .= "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" ;
				$aux .= "<anydataset>\n" ;
				$aux .= "	<row>\n" ;
				$aux .= "		<field name=\"dbname\">__PROJECT_FILE__</field>\n" ;
				$aux .= "		<field name=\"dbtype\">dsn</field>\n" ;
				$aux .= "		<field name=\"dbconnectionstring\">mysql://root@localhost/__PROJECT_FILE__</field>\n" ;
				$aux .= "	</row>\n" ;
				$aux .= "</anydataset>\n" ;
				CreatePhp5Project::writeTemplate( $aux, "$HOME/data/anydataset/_db.anydata.xml", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );
				copy( "$HOME/data/anydataset/_db.anydata.xml", "$HOME/data/anydataset/_db.anydata-dist.xml" );

				$aux = "";
				$aux .= "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" ;
				$aux .= "<anydataset>\n" ;
				$aux .= "	<row>\n" ;
				$aux .= "		<field name=\"destination_id\">DEFAULT</field>\n" ;
				$aux .= "		<field name=\"email\">youremail@provider.com</field>\n" ;
				$aux .= "		<field name=\"name\">Your Name</field>\n" ;
				$aux .= "	</row>\n" ;
				$aux .= "</anydataset>\n" ;
				CreatePhp5Project::writeTemplate( $aux, "$HOME/data/anydataset/_configemail.anydata.xml", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );

				# Config
				CreatePhp5Project::writeTemplate( "$TEMPLATE/project/config.php.template", "$HTTPDOCS/config.inc.php",
					array('/__DATADIR__/', '/__PHPDIR__/', '/__PROJECT_DATA__/', '/__PROJECT_LIB__/', '/__DATE__/', '/__LANGS__/'),
					array($DATADIR, $PHPDIR, "$HOME/data", "$HOME/lib", date('c'), $LANGUAGESAVAILABLE )
				);
				$configInc = file_get_contents("$HTTPDOCS/config.inc.php");
				$configInc = str_replace($XMLNUKE, '#XMLNUKE#',
							str_replace($HOME, '#PROJECT#',
								$configInc
							));
				file_put_contents("$HTTPDOCS/config.inc-dist.php", $configInc);


				# CodeGenX Generators
				@mkdir( "$HOME/codegenx" );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/codegenx/tmpl/Data.codegenx.template", "$HOME/codegenx/{$PROJECT}_Data.codegenx", array('/__XMLNUKE__/', '/__PROJECT__/'), array($XMLNUKE, $PROJECT) );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/codegenx/tmpl/Data.xml.template", "$HOME/codegenx/{$PROJECT}_Data.xml", array('/__XMLNUKE__/', '/__PROJECT__/'), array($XMLNUKE, $PROJECT) );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/codegenx/tmpl/Lib.codegenx.template", "$HOME/codegenx/{$PROJECT}_Lib.codegenx", array('/__XMLNUKE__/', '/__PROJECT__/'), array($XMLNUKE, $PROJECT) );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/codegenx/tmpl/Lib.xml.template", "$HOME/codegenx/{$PROJECT}_Lib.xml", array('/__XMLNUKE__/', '/__PROJECT__/'), array($XMLNUKE, $PROJECT) );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/codegenx/tmpl/Manage.codegenx.template", "$HOME/codegenx/{$PROJECT}_Manage.codegenx", array('/__XMLNUKE__/', '/__PROJECT__/'), array($XMLNUKE, $PROJECT) );
				CreatePhp5Project::writeTemplate( "$TEMPLATE/codegenx/tmpl/Manage.xml.template", "$HOME/codegenx/{$PROJECT}_Manage.xml", array('/__XMLNUKE__/', '/__PROJECT__/'), array($XMLNUKE, $PROJECT) );

				# Finishing XMLNuke installation!
				$gitIgnore = array("# Xmlnuke Files");

				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/imagevalidate.php",  "$HTTPDOCS/") );
				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/xmlnuke.inc.php", "$HTTPDOCS/") );
				//$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/check_install.php.dist", "$HTTPDOCS/check_install.php") );
				copy( "$PHPDIR/index.php.dist", "$HTTPDOCS/index.php" );
				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/xmlnuke.php", "$HTTPDOCS/") );

				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/webservice.php", "$HTTPDOCS/") );
				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/chart.php", "$HTTPDOCS/") );


				$gitIgnore[] = "";
				$gitIgnore[] = "# Machine specific XMLNuke files";
				$gitIgnore[] = "common";
				$gitIgnore[] = "data/cache/*.cache.*";
				$gitIgnore[] = "";

				$gitIgnore[] = "# User specific files. Copy from *-dist.* files instead commit them.";
				$gitIgnore[] = "httpdocs/config.inc.php";
				$gitIgnore[] = "data/anydataset/_db.anydata.xml";
				$gitIgnore[] = "";

				$gitIgnore[] = "# Netbeans Project";
				$gitIgnore[] = "nbproject/private";
				$gitIgnore[] = "";
				$gitIgnore[] = "# Composer Vendor";
				$gitIgnore[] = "vendor";
				$gitIgnore[] = "";
				$gitIgnore[] = "# User Defined";
				$gitIgnore[] = "";

				CreatePhp5Project::writeToFile("$HOME/.gitignore", $gitIgnore);
				//touch("$HOME/config.inc.php");
				touch("$HTTPDOCS/config.inc-dist.php");

				if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
					CreatePhp5Project::executeShell( "ln -sf", array("$XMLNUKE/xmlnuke-common", "$HTTPDOCS/common") );

				return  array(
					'HOME' => $HOME,
					'HTTPDOCS' => $HTTPDOCS,
					'PROJECT' => $PROJECT,
					'PROJECT_FILE' => $PROJECT_FILE,
					'XMLNUKE' => $XMLNUKE,
					'PHPDIR' => $PHPDIR,
					'DATADIR' => $DATADIR
				);
			}
			else
			{
				throw new Exception("'$HOME' does not exists. Create it first.");
			}
		}
		else
		{
			throw new Exception("XMLNuke PHP DIR '$PHPDIR' not found!!! Cannot continue.");
		}
	}

	public static function Update($xmlnuke, $path)
	{
		$PHPDIR = "$xmlnuke/xmlnuke-php5";
		$XMLNUKE = $xmlnuke;
		$HTTPDOCS = "$path/httpdocs";
		$CONFIG = "$HTTPDOCS/config.inc.php";

		if (!file_exists($CONFIG))
			throw new Exception("File '$CONFIG' does not exists");

		if (class_exists('\config'))
			throw new Exception("File '$CONFIG' already loaded");

		require_once("$CONFIG");

		$config = \config::getValuesConfig();

		$xmlnukePathConfig = $config['xmlnuke.PHPXMLNUKEDIR'];

		if ($PHPDIR != $xmlnukePathConfig)
			throw new Exception("Config points to '$xmlnukePathConfig' and the script is running on '$PHPDIR';");


		if (file_exists("$HTTPDOCS/imagevalidate.php"))
			unlink ("$HTTPDOCS/imagevalidate.php");
		$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/imagevalidate.php",  "$HTTPDOCS/") );

		if (file_exists("$HTTPDOCS/xmlnuke.inc.php"))
			unlink ("$HTTPDOCS/xmlnuke.inc.php");
		$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/xmlnuke.inc.php", "$HTTPDOCS/") );

		if (!file_exists("$HTTPDOCS/index.php"))
			copy( "$PHPDIR/index.php.dist", "$HTTPDOCS/index.php" );

		if (file_exists("$HTTPDOCS/xmlnuke.php"))
			unlink ("$HTTPDOCS/xmlnuke.php");
		$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/xmlnuke.php", "$HTTPDOCS/") );

		if (file_exists("$HTTPDOCS/webservice.php"))
			unlink ("$HTTPDOCS/webservice.php");
		$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/webservice.php", "$HTTPDOCS/") );

		if (file_exists("$HTTPDOCS/chart.php"))
			unlink ("$HTTPDOCS/chart.php");
		$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/chart.php", "$HTTPDOCS/") );

		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
		{
			if (file_exists("$HTTPDOCS/common"))
				unlink ("$HTTPDOCS/common");
			CreatePhp5Project::executeShell( "ln -sf", array("$XMLNUKE/xmlnuke-common", "$HTTPDOCS/common") );
		}
		return true;
	}

	protected static function executeShell($cmd, $params)
	{
		$final = "";
		$retorno = "";
		$isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

		switch ($cmd)
		{
			case "ln -sf":
				if ($isWindows)
					$final = "copy \"" . str_replace('/', DIRECTORY_SEPARATOR, $params[0]) . "\" \"" . str_replace('/', DIRECTORY_SEPARATOR, $params[1]) . "\"";
				else
					$final = $cmd . " \"" . str_replace('\\', DIRECTORY_SEPARATOR, $params[0]) . "\" \"" . str_replace('\\', DIRECTORY_SEPARATOR, $params[1]) . "\"";

				if (strpos($params[0], 'dist') !== false)
					$retorno = basename($params[1]);
				else
					$retorno = basename($params[0]);
				break;
		}

		if ($final != "")
			exec($final);

		return $retorno;

	}

	protected static function writeToFile($file, $contents)
	{
		if (is_array($contents))
			$contents = implode("\n", $contents);

		file_put_contents($file, $contents);
	}

	protected static function writeTemplate($template, $file, $pattern, $replace)
	{
		if (file_exists($template))
			$contents = file_get_contents($template);
		else
			$contents = $template;

		$contents = preg_replace($pattern, $replace, $contents);

		CreatePhp5Project::writeToFile($file, $contents);
	}

}
