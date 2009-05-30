#!/bin/sh

BASE="/usr/local/xmlnuke"
XMLNUKE="/tmp/xmlnuke"

mkdir $BASE/xmlnuke-php5

cp -R $XMLNUKE/xmlnuke-php5 $BASE/xmlnuke-php5
cp $XMLNUKE/create-php5-project.sh $BASE/
cp create-php5-project /usr/bin/
cp $XMLNUKE/apache-modules/xmlnuke-php5-virtualhost.conf /etc/apache2/sites-available

