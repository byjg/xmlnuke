#!/bin/sh

if [[ $UID -ne 0 ]]
then
	echo "$0 must be run as root"
	exit 1
fi

svn export ../../. /tmp/xmlnuke

VERSION=`cat /tmp/xmlnuke/VERSION`

cd xmlnuke-common

checkinstall --type=debian --pkgname=xmlnuke-common --pkgversion=$VERSION --maintainer=joao@xmlnuke.com --pkgsource=http://www.xmlnuke.com/ --requires=libapache2-mod-php5,php5-xsl --pkggroup=web ./makeit.sh

cd -
cd xmlnuke-php5

checkinstall --type=debian --pkgname=xmlnuke-php5 --pkgversion=$VERSION --maintainer=joao@xmlnuke.com --pkgsource=http://www.xmlnuke.com/ --requires=xmlnuke-common --pkggroup=web --nodoc ./makeit.sh

cd -

rm -rf /tmp/xmlnuke
