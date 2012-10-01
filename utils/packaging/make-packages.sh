#!/bin/bash

if [[ $UID -ne 0 ]]
then
	echo "$0 must be run as root"
	exit 1
fi

# Variables
CURDIR="`dirname \"$0\"`"              # relative
CURDIR="`( cd \"$CURDIR\" && pwd )`"  # absolutized and normalized
XMLNUKE="`( cd \"$CURDIR/../..\" && pwd )`"
VERSION=`cat $XMLNUKE/VERSION`
TMPPATH="/tmp/xmlnuke-$VERSION"

if [ -z "$1" ]
then
	echo "Last build was `cat $CURDIR/packages/LASTBUILD`"
	echo
	exit
else
	RELEASE="$1"
fi



echo VARIABLES
echo $CURDIR
echo $XMLNUKE
echo $VERSION
echo $TMPPATH
echo

# EXPORT CURRENT WORKING COPY
svn export $XMLNUKE/. $TMPPATH

# PREPARE TMP FOLDER XMLNUKE
cd $TMPPATH
./rename-config-files.sh yes

# SETUP DOC FILES
FOLDERS="xmlnuke-common xmlnuke-php5 xmlnuke-data"
for FLD in `echo $FOLDERS`
do
    FOLDER=$FLD-$VERSION
	mkdir -p $TMPPATH/$FOLDER/doc-pak
	cp $TMPPATH/AUTHORS $TMPPATH/$FOLDER/doc-pak
	cp $TMPPATH/CONTRIBUTORS $TMPPATH/$FOLDER/doc-pak
	cp $TMPPATH/*README* $TMPPATH/$FOLDER/doc-pak
	cp $TMPPATH/LICENSE $TMPPATH/$FOLDER/doc-pak
	cp $TMPPATH/COPYING $TMPPATH/$FOLDER/doc-pak
	cp $TMPPATH/VERSION $TMPPATH/$FOLDER/doc-pak
	cp $CURDIR/description-pak $TMPPATH/$FOLDER
	cp $CURDIR/$FLD/postinstall-pak $TMPPATH/$FOLDER
done;


# CREATE COMMON PACKAGE
cd $TMPPATH/xmlnuke-common-$VERSION
checkinstall --type=debian --pkgname=xmlnuke-common --pkgversion=$VERSION --pkgrelease=$RELEASE --maintainer=joao@xmlnuke.com --pkgsource=http://www.xmlnuke.com/ --pkggroup=web $CURDIR/xmlnuke-common/makeit.sh $TMPPATH
cp $TMPPATH/xmlnuke-common-$VERSION/*.deb $CURDIR/packages

# CREATE DATA PACKAGE
cd $TMPPATH/xmlnuke-data-$VERSION
checkinstall --type=debian --pkgname=xmlnuke-data --pkgversion=$VERSION --pkgrelease=$RELEASE --maintainer=joao@xmlnuke.com --pkgsource=http://www.xmlnuke.com/ --requires=xmlnuke-common --pkggroup=web $CURDIR/xmlnuke-data/makeit.sh $TMPPATH
cp $TMPPATH/xmlnuke-data-$VERSION/*.deb $CURDIR/packages

# CREATE PHP5 PACKAGE
cp $CURDIR/xmlnuke-php5/create-php5-project $TMPPATH
cd $TMPPATH/xmlnuke-php5-$VERSION
checkinstall --type=debian --pkgname=xmlnuke-php5 --pkgversion=$VERSION --pkgrelease=$RELEASE --maintainer=joao@xmlnuke.com --pkgsource=http://www.xmlnuke.com/ --requires=php5-xsl,php5-gd,xmlnuke-common,xmlnuke-data --pkggroup=web --nodoc $CURDIR/xmlnuke-php5/makeit.sh $TMPPATH
cp $TMPPATH/xmlnuke-php5-$VERSION/*.deb $CURDIR/packages

echo "$VERSION-$RELEASE" > $CURDIR/packages/LASTBUILD

rm -rf $TMPPATH

echo
echo
echo "================================================================================"
echo
echo "All packages copied to $CURDIR/packages"
echo
echo "================================================================================"
echo