#!/bin/sh

#VARIABLES
TMPPATH="$1"

BASE="/usr/local/xmlnuke"

cp -R $TMPPATH/xmlnuke-php5 $BASE
cp $TMPPATH/create-php5-project.php $BASE
cp $TMPPATH/create-php5-project /usr/bin
chmod 755 /usr/bin/create-php5-project

