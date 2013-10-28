<?php

if (PHP_SAPI == 'cli')
{

	echo "\n";
	echo "============================\n";
	echo "XMLNuke PHP5 Project Creator\n";
	echo "By JG @ 2013\n";
	echo "============================\n";
	echo "\n";

	if ($argc < 5)
	{
		echo "Use this script to create a XMLNuke PHP5 project ready run and edit in Netbeans, PDT Eclipse or another editor. \n";
		echo "\n";
		echo "Usage: \n";
		echo "   create-php5-project.php PATHTOYOURPROJECT sitename project language1 language2... \n";
		echo "\n";
		echo "Where: \n";
		echo "   PATHTOYOURPROJECT is the full path for your project  \n";
		echo "   sitename is your site, for example: mysite  \n";
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

		if (func_num_args() < 5)
			throw new Exception('I expected at least 5 parameters: file, homepath, sitename, projectname and language ');

		$argc = func_num_args();
		$argv = func_get_args();

		$HOME = $argv[1];
		$SITE = strtolower($argv[2]);
		$PROJECT = $argv[3];
		$PROJECT_FILE = strtolower($PROJECT);
		$XMLNUKE = dirname(__FILE__);

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

				# Creating the Folders
				@mkdir( "$HOME/static" );
				@mkdir( "$HOME/data" );
				@mkdir( "$HOME/data/anydataset" );
				@mkdir( "$HOME/data/cache" );
				@mkdir( "$HOME/data/lang" );
				@mkdir( "$HOME/data/offline" );
				@mkdir( "$HOME/data/xml" );
				@mkdir( "$HOME/data/xsl" );
				@mkdir( "$HOME/data/snippet" );

				CreatePhp5Project::writeToFile( "$HOME/data/anydataset/.do_not_remove", "-- DO NOT REMOVE THIS DIRECTORY --") ;
				CreatePhp5Project::writeToFile( "$HOME/data/cache/.do_not_remove", "-- DO NOT REMOVE THIS DIRECTORY --") ;
				CreatePhp5Project::writeToFile( "$HOME/data/lang/.do_not_remove", "-- DO NOT REMOVE THIS DIRECTORY --") ;
				CreatePhp5Project::writeToFile( "$HOME/data/offline/.do_not_remove", "-- DO NOT REMOVE THIS DIRECTORY --") ;
				CreatePhp5Project::writeToFile( "$HOME/data/xml/.do_not_remove", "-- DO NOT REMOVE THIS DIRECTORY --") ;
				CreatePhp5Project::writeToFile( "$HOME/data/xsl/.do_not_remove", "-- DO NOT REMOVE THIS DIRECTORY --") ;
				CreatePhp5Project::writeToFile( "$HOME/data/snippet/.do_not_remove", "-- DO NOT REMOVE THIS DIRECTORY --") ;

				$langs = array();
				$i = 4;
				while ( $i < $argc )
				{
					$langs[] = "'" . $argv[$i] . "' => '" . $argv[$i] . "'";

					@mkdir( "$HOME/data/xml/" . $argv[$i] );
					copy( "$DATADIR/sites/index.xsl.template", "$HOME/data/xsl/index." . $argv[$i] . ".xsl" );
					copy( "$DATADIR/sites/page.xsl.template", "$HOME/data/xsl/page." . $argv[$i] . ".xsl" );
					copy( "$DATADIR/sites/index.xml.template", "$HOME/data/xml/" . $argv[$i] . "/index." . $argv[$i] . ".xml" );
					copy( "$DATADIR/sites/home.xml.template", "$HOME/data/xml/" . $argv[$i] . "/home." . $argv[$i] . ".xml" );
					copy( "$DATADIR/sites/notfound.xml.template", "$HOME/data/xml/" . $argv[$i] . "/notfound." . $argv[$i] . ".xml" );

					CreatePhp5Project::writeToFile( "$HOME/data/xml/" . $argv[$i] . "/index.php.btree", "xmlnuke\n+home." . $argv[$i] . ".xml" );
					$i++;
				}

				$LANGUAGESAVAILABLE = implode(", \n\t\t\t\t\t", $langs);

				@mkdir( "$HOME/lib" );
				@mkdir( "$HOME/lib/$PROJECT" );

				@mkdir( "$HOME/lib/$PROJECT/Modules" );
				CreatePhp5Project::writeTemplate( "$DATADIR/sites/module.php.template", "$HOME/lib/$PROJECT/Modules/Home.class.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );

				@mkdir( "$HOME/lib/$PROJECT/Base" );
				CreatePhp5Project::writeTemplate( "$DATADIR/sites/adminbasemodule.php.template", "$HOME/lib/$PROJECT/Base/${PROJECT}AdminBaseModule.class.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );
				CreatePhp5Project::writeTemplate( "$DATADIR/sites/basedbaccess.php.template", "$HOME/lib/$PROJECT/Base/${PROJECT}BaseDBAccess.class.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );
				CreatePhp5Project::writeTemplate( "$DATADIR/sites/basemodel.php.template", "$HOME/lib/$PROJECT/Base/${PROJECT}BaseModel.class.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );
				CreatePhp5Project::writeTemplate( "$DATADIR/sites/basemodule.php.template", "$HOME/lib/$PROJECT/Base/${PROJECT}BaseModule.class.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );
				CreatePhp5Project::writeTemplate( "$DATADIR/sites/baseuiedit.php.template", "$HOME/lib/$PROJECT/Base/${PROJECT}BaseUIEdit.class.php", array('/__PROJECT__/', '/__PROJECT_FILE__/'), array($PROJECT, $PROJECT_FILE ) );


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
				CreatePhp5Project::writeTemplate( "$DATADIR/sites/config.php.template", "$HOME/config.inc-dist.php",
					array('/__DATADIR__/', '/__PHPDIR__/', '/__SITE__/', '/__PROJECT_DATA__/', '/__PROJECT_LIB__/', '/__DATE__/', '/__LANGS__/'),
					array($DATADIR, $PHPDIR, $SITE, $HOME . '/data', $HOME . '/lib', date('c'), $LANGUAGESAVAILABLE )
				);

				# Finishing XMLNuke installation!
				$gitIgnore = array("# Xmlnuke Files - Start");

				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/imagevalidate.php",  "$HOME/") );
				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/xmlnukeadmin.php", "$HOME/") );
				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/xmlnuke.inc.php", "$HOME/") );
				//$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/check_install.php.dist", "$HOME/check_install.php") );
				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/index.php.dist", "$HOME/index.php") );
				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/xmlnuke.php", "$HOME/") );

				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/writepage.inc.php.dist", "$HOME/writepage.inc.php") );
				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/unittest.php", "$HOME/") );
				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/webservice.php", "$HOME/") );
				$gitIgnore[] = CreatePhp5Project::executeShell( "ln -sf", array("$PHPDIR/chart.php", "$HOME/") );

				$gitIgnore[] = "config.inc.php";
				$gitIgnore[] = "# Xmlnuke Files - End";
				$gitIgnore[] = "";

				CreatePhp5Project::writeToFile("$HOME/.gitignore", $gitIgnore);
				//touch("$HOME/config.inc.php");
				touch("$HOME/config.inc-dist.php");


				return  array(
					'HOME' => $HOME,
					'SITE' => $SITE,
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