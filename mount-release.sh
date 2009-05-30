#!/bin/sh 

echo
echo MOUNT-RELEASE.sh
echo March-25-2006 
echo by Joao Gilberto Magalhaes
echo

if [ -z $1 ] ; then

   echo This batch will mount a valid XMLNuke release. 
   echo
   echo mount-release [xmlnukerelease]
   echo \ \ [xmlnukerelease] - php5 or csharp or java
   echo

elif [ -d xmlnuke-$1 ]; then

   ln -sf $PWD/xmlnuke-data xmlnuke-$1/data
   ln -sf $PWD/xmlnuke-common xmlnuke-$1/common
   chmod -R a+w xmlnuke-data

else

   echo \"xmlnuke-$1\" is not a valid/existing XMLNuke release. 

fi

echo
echo END.
echo
