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
	XMLNUKE="`dirname $0`"

	if [ -d "$XMLNUKE/xmlnuke-php5" ]
	then
		if [ -d "$HOME" ]
		then

			ln -sf "$XMLNUKE/xmlnuke-php5/imagevalidate.php" "$HOME/"
			ln -sf "$XMLNUKE/xmlnuke-php5/xmlnukeadmin.php" "$HOME/"
			ln -sf "$XMLNUKE/xmlnuke-php5/xmlnuke.inc.php" "$HOME/"
			ln -sf "$XMLNUKE/xmlnuke-php5/check_install.php.rename_to_work" "$HOME/check_install.php"
			ln -sf "$XMLNUKE/xmlnuke-php5/index.php.rename_to_work" "$HOME/index.php"
			ln -sf "$XMLNUKE/xmlnuke-php5/xmlnuke.php" "$HOME/"

			ln -sf "$XMLNUKE/xmlnuke-php5/writepage.inc.php.rename_to_work" "$HOME/writepage.inc.php"
			ln -sf "$XMLNUKE/xmlnuke-php5/unittest.php" "$HOME/"
			ln -sf "$XMLNUKE/xmlnuke-php5/webservice.php" "$HOME/"
			ln -sf "$XMLNUKE/xmlnuke-php5/chart.php" "$HOME/"
			
			
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
				cp "$XMLNUKE/xmlnuke-data/sites/index.xsl.template" "$HOME/data/xsl/index.$4.xsl"
				cp "$XMLNUKE/xmlnuke-data/sites/page.xsl.template" "$HOME/data/xsl/page.$4.xsl"
				cp "$XMLNUKE/xmlnuke-data/sites/index.xml.template" "$HOME/data/xml/$4/index.$4.xml"
				cp "$XMLNUKE/xmlnuke-data/sites/home.xml.template" "$HOME/data/xml/$4/home.$4.xml"
				cp "$XMLNUKE/xmlnuke-data/sites/notfound.xml.template" "$HOME/data/xml/$4/notfound.$4.xml"
				echo "xmlnuke\n+home.$4.xml" > "$HOME/data/xml/$4/index.php.btree"
				shift
			done

			chmod 777 -R "$HOME/data"

			mkdir -p "$HOME/lib"
			cat "$XMLNUKE/xmlnuke-data/sites/_includelist.php.template" | sed -e "s/__PROJECT__/$PROJECT/gi" | sed -e "s/__PROJECT_FILE__/$PROJECT_FILE/gi" > "$HOME/lib/_includelist.php"

			mkdir -p "$HOME/lib/modules"
			cat "$XMLNUKE/xmlnuke-data/sites/module.php.template" | sed -e "s/__PROJECT__/$PROJECT/gi" | sed -e "s/__PROJECT_FILE__/$PROJECT_FILE/gi" > "$HOME/lib/modules/home.class.php"

			mkdir -p "$HOME/lib/base"
			cat "$XMLNUKE/xmlnuke-data/sites/adminbasemodule.php.template" | sed -e "s/__PROJECT__/$PROJECT/gi" | sed -e "s/__PROJECT_FILE__/$PROJECT_FILE/gi"  > "$HOME/lib/base/${PROJECT_FILE}adminbasemodule.class.php"
			cat "$XMLNUKE/xmlnuke-data/sites/basedbaccess.php.template" | sed -e "s/__PROJECT__/$PROJECT/gi" | sed -e "s/__PROJECT_FILE__/$PROJECT_FILE/gi"  > "$HOME/lib/base/${PROJECT_FILE}basedbaccess.class.php"
			cat "$XMLNUKE/xmlnuke-data/sites/basemodel.php.template" | sed -e "s/__PROJECT__/$PROJECT/gi" | sed -e "s/__PROJECT_FILE__/$PROJECT_FILE/gi"  > "$HOME/lib/base/${PROJECT_FILE}basemodel.class.php"
			cat "$XMLNUKE/xmlnuke-data/sites/basemodule.php.template" | sed -e "s/__PROJECT__/$PROJECT/gi" | sed -e "s/__PROJECT_FILE__/$PROJECT_FILE/gi"  > "$HOME/lib/base/${PROJECT_FILE}basemodule.class.php"
			cat "$XMLNUKE/xmlnuke-data/sites/baseuiedit.php.template" | sed -e "s/__PROJECT__/$PROJECT/gi" | sed -e "s/__PROJECT_FILE__/$PROJECT_FILE/gi"  > "$HOME/lib/base/${PROJECT_FILE}baseuiedit.class.php"
			
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
			echo "\$configValues[\"xmlnuke.ROOTDIR\"]='$XMLNUKE/xmlnuke-data'; " >> "$HOME/config.default.php"
			echo "\$configValues[\"xmlnuke.USEABSOLUTEPATHSROOTDIR\"] = true; " >> "$HOME/config.default.php"
			echo "\$configValues[\"xmlnuke.DEFAULTSITE\"]='$SITE'; " >> "$HOME/config.default.php"
			echo "\$configValues[\"xmlnuke.EXTERNALSITEDIR\"] = '$SITE=$HOME/data'; " >> "$HOME/config.default.php"
			echo "\$configValues[\"xmlnuke.PHPLIBDIR\"] = '${PROJECT_FILE}=$HOME/lib'; " >> "$HOME/config.default.php"
			echo "\$configValues[\"xmlnuke.PHPXMLNUKEDIR\"] = '$XMLNUKE/xmlnuke-php5'; " >> "$HOME/config.default.php"
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
