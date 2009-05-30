#!/bin/sh 

echo
echo BUILD-PHP5-PACKAGE-RELEASE.sh
echo May-2008
echo by Joao Gilberto Magalhaes
echo

VERSION=$1
if [ -z $1 ] ; then

   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/VERSION /tmp/VERSION
   VERSION=`cat /tmp/VERSION`

fi

   svn info https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/ --xml > /tmp/RELEASE
   RELEASE=`cat /tmp/RELEASE | grep revision -m 1 | sed -e 's/   revision="\(\w*\)">/\1/gi'`

   # DOWNLOAD OF REQUIRED FILES
   XMLNUKEDIR=xmlnuke-php5-v${VERSION}r${RELEASE}
   mkdir /tmp/$XMLNUKEDIR
   #mkdir /tmp/$XMLNUKEDIR/xmlnuke-php5
   #mkdir /tmp/$XMLNUKEDIR/xmlnuke-php5/data
   #mkdir /tmp/$XMLNUKEDIR/xmlnuke-php5/common
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/xmlnuke-php5 /tmp/$XMLNUKEDIR/xmlnuke-php5
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/xmlnuke-data /tmp/$XMLNUKEDIR/xmlnuke-php5/data
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/xmlnuke-common /tmp/$XMLNUKEDIR/xmlnuke-php5/common
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/COPYING /tmp/$XMLNUKEDIR/COPYING
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/AUTHORS /tmp/$XMLNUKEDIR/AUTHORS
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/CONTRIBUTORS /tmp/$XMLNUKEDIR/CONTRIBUTORS
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/LICENSE /tmp/$XMLNUKEDIR/LICENSE
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/PHP5.README /tmp/$XMLNUKEDIR/README
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/PHP5.README.pt-br /tmp/$XMLNUKEDIR/README.pt-br
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/PHP5.README.sv-se /tmp/$XMLNUKEDIR/README.sv-se
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/utils /tmp/$XMLNUKEDIR/utils
   /tmp/$XMLNUKEDIR/utils/generatelog/changelog.sh https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/xmlnuke-php5 > /tmp/$XMLNUKEDIR/CHANGELOG

   rm -rf /tmp/$XMLNUKEDIR/utils

   chmod -R g+rws /tmp/$XMLNUKEDIR/xmlnuke-php5/data
   chmod -R a+rw /tmp/$XMLNUKEDIR/xmlnuke-php5/data
   mv /tmp/$XMLNUKEDIR/xmlnuke-php5/check_install.php.rename_to_work /tmp/$XMLNUKEDIR/xmlnuke-php5/check_install.php
   mv /tmp/$XMLNUKEDIR/xmlnuke-php5/index.php.rename_to_work /tmp/$XMLNUKEDIR/xmlnuke-php5/index.php
   mv /tmp/$XMLNUKEDIR/xmlnuke-php5/data/shared/setup/users.anydata.xml.rename_to_work /tmp/$XMLNUKEDIR/xmlnuke-php5/data/shared/setup/users.anydata.xml
   mv /tmp/$XMLNUKEDIR/xmlnuke-php5/data/shared/setup/roles.anydata.xml.rename_to_work /tmp/$XMLNUKEDIR/xmlnuke-php5/data/shared/setup/roles.anydata.xml
   mv /tmp/$XMLNUKEDIR/xmlnuke-php5/data/shared/admin/admin_page.pt-br.xsl.rename_to_work /tmp/$XMLNUKEDIR/xmlnuke-php5/data/shared/admin/admin_page.pt-br.xsl
   mv /tmp/$XMLNUKEDIR/xmlnuke-php5/data/shared/admin/adminmodules.config.xml.rename_to_work /tmp/$XMLNUKEDIR/xmlnuke-php5/data/shared/admin/adminmodules.config.xml
   mv /tmp/$XMLNUKEDIR/xmlnuke-php5/data/shared/admin/adminmodules.lang.anydata.xml.rename_to_work /tmp/$XMLNUKEDIR/xmlnuke-php5/data/shared/admin/adminmodules.lang.anydata.xml
   echo "<?php # 'CONFIG.INC.PHP' MUST HAVE WRITE PERMISSION BEFORE RUN XMLNUKE FIRST TIME ?>" > /tmp/$XMLNUKEDIR/xmlnuke-php5/config.inc.php
   chmod 777 /tmp/$XMLNUKEDIR/xmlnuke-php5/config.inc.php

   cd /tmp
   tar czvf $XMLNUKEDIR.tar.gz $XMLNUKEDIR/
   zip -r $XMLNUKEDIR.zip $XMLNUKEDIR

   rm -rf /tmp/$XMLNUKEDIR/
   rm /tmp/VERSION
   rm /tmp/RELEASE

echo
echo END.
echo

