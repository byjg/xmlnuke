#!/bin/bash

if [[ $UID -ne 0 ]]
then
	echo "$0 must be run as root"
	exit 1
fi

# SETUP DOC FILES
XMLNUKE="../.."
mkdir -p xmlnuke-common/doc-pak
cp $XMLNUKE/AUTHORS xmlnuke-common/doc-pak
cp $XMLNUKE/CONTRIBUTORS xmlnuke-common/doc-pak
cp $XMLNUKE/*README* xmlnuke-common/doc-pak
cp $XMLNUKE/LICENSE xmlnuke-common/doc-pak
cp $XMLNUKE/COPYING xmlnuke-common/doc-pak
cp $XMLNUKE/VERSION xmlnuke-common/doc-pak


# EXPORT CURRENT WORKING COPY
svn export ../../. /tmp/xmlnuke
VERSION=`cat /tmp/xmlnuke/VERSION`

# CREATE COMMON PACKAGE
cd xmlnuke-common

checkinstall --type=debian --pkgname=xmlnuke-common --pkgversion=$VERSION --maintainer=joao@xmlnuke.com --pkgsource=http://www.xmlnuke.com/ --requires=libapache2-mod-php5,php5-xsl --pkggroup=web ./makeit.sh

# CREATE PHP5 PACKAGE
cd -
cd xmlnuke-php5

checkinstall --type=debian --pkgname=xmlnuke-php5 --pkgversion=$VERSION --maintainer=joao@xmlnuke.com --pkgsource=http://www.xmlnuke.com/ --requires=xmlnuke-common --pkggroup=web --nodoc ./makeit.sh

cd -

rm -rf /tmp/xmlnuke
