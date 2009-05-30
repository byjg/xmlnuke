#!/bin/sh 

echo
echo BUILD-CSHARP-PACKAGE-RELEASE.sh
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

   # DOWNLOAD OF REQUIRED FILES FOR COMPILE CSHARP EDITION
   XMLNUKEDIR=xmlnuke-csharp-v${VERSION}r${RELEASE}
   mkdir /tmp/$XMLNUKEDIR
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/xmlnuke-csharp /tmp/$XMLNUKEDIR/xmlnuke-csharp
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/xmlnuke-csharp-sources /tmp/$XMLNUKEDIR/xmlnuke-csharp-sources
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/utils /tmp/$XMLNUKEDIR/utils

   # COMPILING
   cd /tmp/$XMLNUKEDIR/xmlnuke-csharp-sources
   chmod +x build.sh
   chmod +x build.bat
   ./build.sh
   cd /tmp
   rm -rf /tmp/$XMLNUKEDIR/xmlnuke-csharp-sources

   # DOWNLOAD AND SETUP OF COMMON FILES
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/xmlnuke-data /tmp/$XMLNUKEDIR/xmlnuke-csharp/data
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/xmlnuke-common /tmp/$XMLNUKEDIR/xmlnuke-csharp/common

   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/COPYING /tmp/$XMLNUKEDIR/COPYING
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/AUTHORS /tmp/$XMLNUKEDIR/AUTHORS
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/CONTRIBUTORS /tmp/$XMLNUKEDIR/CONTRIBUTORS
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/LICENSE /tmp/$XMLNUKEDIR/LICENSE
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/CSharp.README /tmp/$XMLNUKEDIR/README
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/CSharp.README.pt-br /tmp/$XMLNUKEDIR/README.pt-br
   svn export https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/xmlnuke-csharp-sources/create-xmlnuke-project.vbs /tmp/$XMLNUKEDIR/create-xmlnuke-project.vbs
   /tmp/$XMLNUKEDIR/utils/generatelog/changelog.sh https://xmlnuke.svn.sourceforge.net/svnroot/xmlnuke/xmlnuke-csharp-sources > /tmp/$XMLNUKEDIR/CHANGELOG

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

