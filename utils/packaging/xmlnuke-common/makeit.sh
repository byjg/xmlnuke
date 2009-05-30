#!/bin/sh

BASE="/usr/local/xmlnuke"
XMLNUKE="/tmp/xmlnuke"
mkdir $BASE
mkdir $BASE/xmlnuke-sites

cp -R $XMLNUKE/xmlnuke-common $BASE/
cd $XMLNUKE
find . | grep xmlnuke-data | grep -v "sites\/sample" | grep -v "sites\/docs" | cpio -dump $BASE/.
cd -

cp -R $XMLNUKE/xmlnuke-data/sites/sample $BASE/xmlnuke-sites
cp -R $XMLNUKE/xmlnuke-data/sites/docs $BASE/xmlnuke-sites


