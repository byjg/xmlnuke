<?php

if (PHP_SAPI == 'cli')
{

	echo "\n";
	echo "============================\n";
	echo "XMLNuke PHP5 Project Creator\n";
	echo "By JG @ 2013\n";
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
			$result = call_user_func_array( array( 'CreatePhp5Project', 'Run' ), $argv );

			echo "Done.\n";
			echo "\n";
			echo "You must do some configurations manualy:\n";
			echo "  - Create an alias \"/common\" pointing to \"{$result['XMLNUKE']}/xmlnuke-common\" \n";
			echo "  - Point the document root on your Web Server to \"{$result['HOME']}\" \n";
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

class CreatePhp5Project
{
	public static function Run($arg = null)
	{

		if (func_num_args() < 4)
			throw new Exception('I expected at least 5 parameters: file, homepath, projectname and language ');

		$argc = func_num_args();
		$argv = func_get_args();

		$HOME = $argv[1];
		$HTTPDOCS = $argv[1] . "/httpdocs";
		$PROJECT = $argv[2];
		$PROJECT_FILE = strtolower($PROJECT);
		$XMLNUKE = dirname(realpath($_SERVER["SCRIPT_FILENAME"]));
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
					throw new Exception("XMLNuke release not found!!! Cannot continue.");
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
				CreatePhp5Project::writeTemplate( "$TEMPLATE/project/sampletest.php.template", "$HOME/lib/Tests/$PROJECT/Classes/SampleTest.class.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );

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
				CreatePhp5Project::writeTemplate( "$TEMPLATE/project/config.php.template", "$HTTPDOCS/config.inc-dist.php",
					array('/__DATADIR__/', '/__PHPDIR__/', '/__PROJECT_DATA__/', '/__PROJECT_LIB__/', '/__DATE__/', '/__LANGS__/'),
					array($DATADIR, $PHPDIR, "$HOME/data", "$HOME/lib", date('c'), $LANGUAGESAVAILABLE )
				);


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
				CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/index.php.dist", "$HTTPDOCS/index.php") );
				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/xmlnuke.php", "$HTTPDOCS/") );

				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/webservice.php", "$HTTPDOCS/") );
				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/chart.php", "$HTTPDOCS/") );

				$gitIgnore[] = "config.inc.php";
				$gitIgnore[] = "# ----------";
				$gitIgnore[] = "common";
				$gitIgnore[] = "data/cache/*.cache.*";
				$gitIgnore[] = "data/anydataset/_db.anydata.xml   # Create a _db.anydata-dist.xml instead to commit this file";
				$gitIgnore[] = "";
				$gitIgnore[] = "# Netbeans Project";
				$gitIgnore[] = "nbproject/private";
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
			throw new Exception("XMLNuke release not found!!! Cannot continue.");
		}
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
?>