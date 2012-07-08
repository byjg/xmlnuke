#!/bin/sh 

echo
echo BUILD-PHP5-PACKAGE-RELEASE.sh
echo Jun-2009
echo by Joao Gilberto Magalhaes
echo

if [ -z $1 ] ; then

   XMLNUKE_SVN="svn://svn.code.sf.net/p/xmlnuke/code/trunk"

else

   XMLNUKE_SVN="."   # USE THE CURRENT WORKAREA

fi

   svn export $XMLNUKE_SVN/VERSION /tmp/VERSION
   VERSION=`cat /tmp/VERSION`

   svn info $XMLNUKE_SVN/ --xml > /tmp/RELEASE
   RELEASE=`cat /tmp/RELEASE | grep revision -m 1 | sed -e 's/   revision="\(\w*\)">/\1/gi'`

   # DOWNLOAD THE REQUIRED FILES
   echo Downloading the required files
   XMLNUKEDIR=xmlnuke-php5-${VERSION}.${RELEASE}
   mkdir /tmp/$XMLNUKEDIR
   #mkdir /tmp/$XMLNUKEDIR/xmlnuke-php5
   #mkdir /tmp/$XMLNUKEDIR/xmlnuke-php5/data
   #mkdir /tmp/$XMLNUKEDIR/xmlnuke-php5/common
   svn export $XMLNUKE_SVN/xmlnuke-php5 /tmp/$XMLNUKEDIR/xmlnuke-php5
   svn export $XMLNUKE_SVN/xmlnuke-data /tmp/$XMLNUKEDIR/xmlnuke-php5/data
   svn export $XMLNUKE_SVN/xmlnuke-common /tmp/$XMLNUKEDIR/xmlnuke-php5/common
   svn export $XMLNUKE_SVN/COPYING /tmp/$XMLNUKEDIR/COPYING
   svn export $XMLNUKE_SVN/AUTHORS /tmp/$XMLNUKEDIR/AUTHORS
   svn export $XMLNUKE_SVN/CONTRIBUTORS /tmp/$XMLNUKEDIR/CONTRIBUTORS
   svn export $XMLNUKE_SVN/LICENSE /tmp/$XMLNUKEDIR/LICENSE
   svn export $XMLNUKE_SVN/PHP5.README /tmp/$XMLNUKEDIR/README
   svn export $XMLNUKE_SVN/PHP5.README.pt-br /tmp/$XMLNUKEDIR/README.pt-br
   svn export $XMLNUKE_SVN/PHP5.README.sv-se /tmp/$XMLNUKEDIR/README.sv-se
   svn export $XMLNUKE_SVN/create-php5-project.sh /tmp/$XMLNUKEDIR/create-php5-project.sh
   svn export $XMLNUKE_SVN/create-php5-project.vbs /tmp/$XMLNUKEDIR/create-php5-project.vbs
   svn export $XMLNUKE_SVN/utils /tmp/$XMLNUKEDIR/utils

   echo Generating ChangeLog
   #/tmp/$XMLNUKEDIR/utils/generatelog/changelog.sh $XMLNUKE_SVN/xmlnuke-php5 > /tmp/$XMLNUKEDIR/CHANGELOG
   svn export $XMLNUKE_SVN/CHANGELOG /tmp/$XMLNUKEDIR/CHANGELOG

   echo Preparing Package
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

