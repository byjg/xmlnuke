#!/bin/sh

CMDLINE="/opt/xmlnuke/utils/cmdline/xmlnuke.cmd.php"
PHPCMD=`which php`

if [ ! -f config.inc.php ]
then
	echo "runxmlnuke.sh: You need run this script inside the XMLNuke Root Directory";
	echo;
	exit 1;
fi

if [ ! -f $CMDLINE ]
then
	echo "runxmlnuke.sh: The script xmlnuke.cmd.php is not setup correctly"
	exit 1;
fi

if [ ! -f $PHPCMD ]
then
	echo "runxmlnuke.sh: PHP is not setup properly or is not accessibile by path"
	exit 1;
fi

if [ "$#" == "0" ]; then
	echo "runscript.sh by JG (2012)"
	echo "This script enable you run XMLNuke pages or modules directly from the command line"
	echo "The default result is XML (rawxml=true) but you can get JSON (rawjson=true)"
	echo
	echo "USAGE:"
	echo "You have to pass key-value pair for each parameter you want to use. "
	echo "For example: "
	echo "./runxmlnuke.sh site=sample xml=home lang=en-us"
	echo
	echo "No arguments provided"
	exit 1
fi



$PHPCMD $CMDLINE "$@"

