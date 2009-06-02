#!/bin/sh 

echo
echo BUILD-CSHARP-PACKAGE-RELEASE.sh
echo Jun-2009
echo by Joao Gilberto Magalhaes
echo

if [ -z $1 ] ; then

   XMLNUKE_SVN="https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/trunk"
   svn export $XMLNUKE_SVN/VERSION /tmp/VERSION
   VERSION=`cat /tmp/VERSION`

else

   XMLNUKE_SVN="${PWD}"   # USE THE CURRENT WORKAREA
   VERSION=$1

fi

   svn info $XMLNUKE_SVN/ --xml > /tmp/RELEASE
   RELEASE=`cat /tmp/RELEASE | grep revision -m 1 | sed -e 's/   revision="\(\w*\)">/\1/gi'`

   # DOWNLOAD OF REQUIRED FILES FOR COMPILE CSHARP EDITION
   XMLNUKEDIR=xmlnuke-csharp-v${VERSION}r${RELEASE}
   mkdir /tmp/$XMLNUKEDIR
   svn export $XMLNUKE_SVN/xmlnuke-csharp /tmp/$XMLNUKEDIR/xmlnuke-csharp
   svn export $XMLNUKE_SVN/xmlnuke-csharp-sources /tmp/$XMLNUKEDIR/xmlnuke-csharp-sources
   svn export $XMLNUKE_SVN/utils /tmp/$XMLNUKEDIR/utils

   # COMPILING
   cd /tmp/$XMLNUKEDIR/xmlnuke-csharp-sources
   chmod +x build.sh
   chmod +x build.bat
   ./build.sh
   cd /tmp
   rm -rf /tmp/$XMLNUKEDIR/xmlnuke-csharp-sources

   # DOWNLOAD AND SETUP OF COMMON FILES
   svn export $XMLNUKE_SVN/xmlnuke-data /tmp/$XMLNUKEDIR/xmlnuke-csharp/data
   svn export $XMLNUKE_SVN/xmlnuke-common /tmp/$XMLNUKEDIR/xmlnuke-csharp/common

   svn export $XMLNUKE_SVN/COPYING /tmp/$XMLNUKEDIR/COPYING
   svn export $XMLNUKE_SVN/AUTHORS /tmp/$XMLNUKEDIR/AUTHORS
   svn export $XMLNUKE_SVN/CONTRIBUTORS /tmp/$XMLNUKEDIR/CONTRIBUTORS
   svn export $XMLNUKE_SVN/LICENSE /tmp/$XMLNUKEDIR/LICENSE
   svn export $XMLNUKE_SVN/CSharp.README /tmp/$XMLNUKEDIR/README
   svn export $XMLNUKE_SVN/CSharp.README.pt-br /tmp/$XMLNUKEDIR/README.pt-br
   svn export $XMLNUKE_SVN/xmlnuke-csharp-sources/create-xmlnuke-project.vbs /tmp/$XMLNUKEDIR/create-xmlnuke-project.vbs
   #/tmp/$XMLNUKEDIR/utils/generatelog/changelog.sh $XMLNUKE_SVN/xmlnuke-csharp-sources > /tmp/$XMLNUKEDIR/CHANGELOG
   svn export $XMLNUKE_SVN/CHANGELOG /tmp/$XMLNUKEDIR/CHANGELOG

   rm -rf /tmp/$XMLNUKEDIR/utils

   chmod -R g+rws /tmp/$XMLNUKEDIR/xmlnuke-csharp/data
   chmod -R a+rw /tmp/$XMLNUKEDIR/xmlnuke-csharp/data

   mv /tmp/$XMLNUKEDIR/xmlnuke-csharp/check_install.aspx.rename_to_work /tmp/$XMLNUKEDIR/xmlnuke-csharp/check_install.aspx
   mv /tmp/$XMLNUKEDIR/xmlnuke-csharp/web.config.rename_to_work /tmp/$XMLNUKEDIR/xmlnuke-csharp/web.config
   mv /tmp/$XMLNUKEDIR/xmlnuke-csharp/default.aspx.rename_to_work /tmp/$XMLNUKEDIR/xmlnuke-csharp/default.aspx
   mv /tmp/$XMLNUKEDIR/xmlnuke-csharp/data/shared/setup/users.anydata.xml.rename_to_work /tmp/$XMLNUKEDIR/xmlnuke-csharp/data/shared/setup/users.anydata.xml
   mv /tmp/$XMLNUKEDIR/xmlnuke-csharp/data/shared/setup/roles.anydata.xml.rename_to_work /tmp/$XMLNUKEDIR/xmlnuke-csharp/data/shared/setup/roles.anydata.xml
   mv /tmp/$XMLNUKEDIR/xmlnuke-csharp/data/shared/admin/admin_page.pt-br.xsl.rename_to_work /tmp/$XMLNUKEDIR/xmlnuke-csharp/data/shared/admin/admin_page.pt-br.xsl
   mv /tmp/$XMLNUKEDIR/xmlnuke-csharp/data/shared/admin/adminmodules.config.xml.rename_to_work /tmp/$XMLNUKEDIR/xmlnuke-csharp/data/shared/admin/adminmodules.config.xml
   mv /tmp/$XMLNUKEDIR/xmlnuke-csharp/data/shared/admin/adminmodules.lang.anydata.xml.rename_to_work /tmp/$XMLNUKEDIR/xmlnuke-csharp/data/shared/admin/adminmodules.lang.anydata.xml
   
   chmod 666 /tmp/$XMLNUKEDIR/xmlnuke-csharp/web.config

   cd /tmp
   zip -r $XMLNUKEDIR.zip $XMLNUKEDIR

   rm -rf /tmp/$XMLNUKEDIR/
   rm /tmp/VERSION
   rm /tmp/RELEASE

echo
echo END.
echo

