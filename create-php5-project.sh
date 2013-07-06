#!/bin/sh

echo XMLNuke PHP5 Project Creator
echo By JG @ 2012
echo

if [ -z "$1" -o -z "$2" -o -z "$3" ]
then
	echo Use this script to create a XMLNuke PHP5 project ready to use on PDT Eclipse or another editor.
	echo
	echo Usage:
	echo "   create-php5-project.sh PATHTOYOURPROJECT sitename project language1 language2... "
	echo
	echo Where:
	echo "   PATHTOYOURPROJECT is the full path for your project "
	echo "   sitename is your site, for example: mysite "
	echo "   project is the name of the project, for example: MyProject "
	echo "   language is the main language for your project. e.g.: pt-br or en-us or de-de "
	echo
else
	HOME="$1"
	SITE="$2"
	PROJECT="$3"
	PROJECT_FILE="`echo $PROJECT | tr '[:upper:]' '[:lower:]'`"
	XMLNUKE=$(readlink -f ${0%/*})

	PHPDIR="$XMLNUKE/xmlnuke-php5"
	DATADIR="$XMLNUKE/xmlnuke-data"

	if [ -d "$PHPDIR" ]
	then
		if [ ! -d "$DATADIR" ]
		then
			DATADIR="$PHPDIR/data"
			if [ ! -d "$DATADIR" ]
			then
				echo XMLNuke release not found!!! Cannot continue.
				exit
			fi
		fi

		if [ -d "$HOME" ]
		then

			ln -sf "$PHPDIR/imagevalidate.php" "$HOME/"
			ln -sf "$PHPDIR/xmlnukeadmin.php" "$HOME/"
			ln -sf "$PHPDIR/xmlnuke.inc.php" "$HOME/"
			ln -sf "$PHPDIR/check_install.php.dist" "$HOME/check_install.php"
			ln -sf "$PHPDIR/index.php.dist" "$HOME/index.php"
			ln -sf "$PHPDIR/xmlnuke.php" "$HOME/"

			ln -sf "$PHPDIR/writepage.inc.php.dist" "$HOME/writepage.inc.php"
			ln -sf "$PHPDIR/unittest.php" "$HOME/"
			ln -sf "$PHPDIR/webservice.php" "$HOME/"
			ln -sf "$PHPDIR/chart.php" "$HOME/"
			
			
			touch "$HOME/config.inc.php"
			chmod 777 "$HOME/config.inc.php"
			mkdir -p "$HOME/static"
			mkdir -p "$HOME/data/anydataset"
			mkdir -p "$HOME/data/cache"
			mkdir -p "$HOME/data/lang"
			mkdir -p "$HOME/data/offline"
			mkdir -p "$HOME/data/xml"
			mkdir -p "$HOME/data/xsl"
			mkdir -p "$HOME/data/snippet"
			
			while [ ! -z "$4" ]
			do
				mkdir -p "$HOME/data/xml/$4"
				cp "$DATADIR/sites/index.xsl.template" "$HOME/data/xsl/index.$4.xsl"
				cp "$DATADIR/sites/page.xsl.template" "$HOME/data/xsl/page.$4.xsl"
				cp "$DATADIR/sites/index.xml.template" "$HOME/data/xml/$4/index.$4.xml"
				cp "$DATADIR/sites/home.xml.template" "$HOME/data/xml/$4/home.$4.xml"
				cp "$DATADIR/sites/notfound.xml.template" "$HOME/data/xml/$4/notfound.$4.xml"
				echo "xmlnuke\n+home.$4.xml" > "$HOME/data/xml/$4/index.php.btree"
				shift
			done

			chmod 777 -R "$HOME/data"

			mkdir -p "$HOME/lib"
			cat "$DATADIR/sites/_includelist.php.template" | sed -e "s/__PROJECT__/$PROJECT/gi" | sed -e "s/__PROJECT_FILE__/$PROJECT_FILE/gi" > "$HOME/lib/_includelist.php"

			mkdir -p "$HOME/lib/modules"
			cat "$DATADIR/sites/module.php.template" | sed -e "s/__PROJECT__/$PROJECT/gi" | sed -e "s/__PROJECT_FILE__/$PROJECT_FILE/gi" > "$HOME/lib/modules/home.class.php"

			mkdir -p "$HOME/lib/base"
			cat "$DATADIR/sites/adminbasemodule.php.template" | sed -e "s/__PROJECT__/$PROJECT/gi" | sed -e "s/__PROJECT_FILE__/$PROJECT_FILE/gi"  > "$HOME/lib/base/${PROJECT_FILE}adminbasemodule.class.php"
			cat "$DATADIR/sites/basedbaccess.php.template" | sed -e "s/__PROJECT__/$PROJECT/gi" | sed -e "s/__PROJECT_FILE__/$PROJECT_FILE/gi"  > "$HOME/lib/base/${PROJECT_FILE}basedbaccess.class.php"
			cat "$DATADIR/sites/basemodel.php.template" | sed -e "s/__PROJECT__/$PROJECT/gi" | sed -e "s/__PROJECT_FILE__/$PROJECT_FILE/gi"  > "$HOME/lib/base/${PROJECT_FILE}basemodel.class.php"
			cat "$DATADIR/sites/basemodule.php.template" | sed -e "s/__PROJECT__/$PROJECT/gi" | sed -e "s/__PROJECT_FILE__/$PROJECT_FILE/gi"  > "$HOME/lib/base/${PROJECT_FILE}basemodule.class.php"
			cat "$DATADIR/sites/baseuiedit.php.template" | sed -e "s/__PROJECT__/$PROJECT/gi" | sed -e "s/__PROJECT_FILE__/$PROJECT_FILE/gi"  > "$HOME/lib/base/${PROJECT_FILE}baseuiedit.class.php"
			
			echo '<?xml version="1.0" encoding="utf-8"?>' > "$HOME/data/anydataset/_db.anydata.xml"
			echo '<anydataset>' >> "$HOME/data/anydataset/_db.anydata.xml"
			echo '	<row>' >> "$HOME/data/anydataset/_db.anydata.xml"
			echo "		<field name=\"dbname\">$PROJECT_FILE</field>" >> "$HOME/data/anydataset/_db.anydata.xml"
			echo '		<field name="dbtype">dsn</field>' >> "$HOME/data/anydataset/_db.anydata.xml"
			echo "		<field name=\"dbconnectionstring\">mysql://root@localhost/$PROJECT_FILE</field>" >> "$HOME/data/anydataset/_db.anydata.xml"
			echo '	</row>' >> "$HOME/data/anydataset/_db.anydata.xml"
			echo '</anydataset>' >> "$HOME/data/anydataset/_db.anydata.xml"

			echo '<?xml version="1.0" encoding="utf-8"?>' > "$HOME/data/anydataset/_configemail.anydata.xml"
			echo '<anydataset>' >> "$HOME/data/anydataset/_configemail.anydata.xml"
			echo '	<row>' >> "$HOME/data/anydataset/_configemail.anydata.xml"
			echo '		<field name="destination_id">DEFAULT</field>' >> "$HOME/data/anydataset/_configemail.anydata.xml"
			echo '		<field name="email">youremail@provider.com</field>' >> "$HOME/data/anydataset/_configemail.anydata.xml"
			echo '		<field name="name">Your Name</field>' >> "$HOME/data/anydataset/_configemail.anydata.xml"
			echo '	</row>' >> "$HOME/data/anydataset/_configemail.anydata.xml"
			echo '</adnydataset>' >> "$HOME/data/anydataset/_configemail.anydata.xml"

			echo "<?php" > "$HOME/config.default.php"
			echo "# This file was generated by create-php5-project.sh. " >> "$HOME/config.default.php"
			echo "# You can safely remove this file after you XMLNuke installation is running." >> "$HOME/config.default.php"
			echo "\$configValues[\"xmlnuke.ROOTDIR\"]='$DATADIR'; " >> "$HOME/config.default.php"
			echo "\$configValues[\"xmlnuke.USEABSOLUTEPATHSROOTDIR\"] = true; " >> "$HOME/config.default.php"
			echo "\$configValues[\"xmlnuke.DEFAULTSITE\"]='$SITE'; " >> "$HOME/config.default.php"
			echo "\$configValues[\"xmlnuke.EXTERNALSITEDIR\"] = '$SITE=$HOME/data'; " >> "$HOME/config.default.php"
			echo "\$configValues[\"xmlnuke.PHPLIBDIR\"] = '${PROJECT_FILE}=$HOME/lib'; " >> "$HOME/config.default.php"
			echo "\$configValues[\"xmlnuke.PHPXMLNUKEDIR\"] = '$PHPDIR'; " >> "$HOME/config.default.php"
			echo "?>" >> "$HOME/config.default.php"
		

			echo Done.
			echo
			echo You must do some configurations manualy:
			echo "  - Create an alias \"/common\" pointing to \"$XMLNUKE/xmlnuke-common\" "
			echo "  - Point the document root on your Web Server to \"$HOME\" "
			echo
			echo After this you can play with these URLs:
			echo http://localhost/xmlnuke.php?xml=home
			echo http://localhost/xmlnuke.php?module=${PROJECT_FILE}.home
			echo

		else
			echo "'$HOME' does not exists. Create it first."
		fi
	else
		echo XMLNuke release not found!!! Cannot continue.
	fi
fi
