#!/bin/bash

SCRIPT_PATH="$(readlink -f $0)";
BASEROOT=`dirname $SCRIPT_PATH`;
XMLNUKECMDLINE="$BASEROOT/$1.cmd.php";
PHPCMD=`which php`;

if [ ! -f $PHPCMD ]
then
	echo "runxmlnuke.sh: PHP is not setup properly or is not accessibile by path"
	exit 1;
fi

if [ -f "$2/config.inc.php" ]
then
	cd $2;
fi

if [ "$#" == "0" -o ! -f config.inc.php ]
then
	echo
	echo "runscript.sh by JG (2014)"
	echo
	echo "This script enables you run XMLNuke pages or modules directly from the command line"
	echo "The default result is XML (raw=xml) but you can get JSON (raw=json)"
	echo
	echo "USAGE:"
	echo "You have to pass key-value pair for each parameter you want to use. "
	echo 
	echo "runxmlnuke.sh script [path_to_your_site] [arguments]"
	echo
	echo "SCRIPTS AVAILABLE:"
	
	for SCR in `ls $BASEROOT/*.cmd.php`
	do
		echo " - `basename $SCR | cut -d. -f 1`"
	done
	echo
	echo "EXAMPLE:"
	echo "   runxmlnuke.sh xmlnuke /Projects/Sample/httpdocs site=sample xml=home lang=en-us"
	echo

	if [ ! -f config.inc.php ]
	then
		echo "**You need run this script inside the your path directory**";
	else
		echo "No arguments provided"
	fi

	echo

	exit 1
fi

if [ ! -f $XMLNUKECMDLINE ]
then
	echo "runxmlnuke.sh: The script '$1.cmd.php' does not exists ";
	echo;
	exit 1;
fi



$PHPCMD $XMLNUKECMDLINE "$@"

