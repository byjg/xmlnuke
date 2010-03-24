#!/bin/sh

echo
echo MOUNT-RELEASE.sh
echo March-25-2006 
echo by Joao Gilberto Magalhaes
echo

if [ -z $1 ] ; then

   echo This script will rename all config files to their proper names. 
   echo
   echo rename-config-files [replace]
   echo \ \ [replace] - no or yes
   echo

else

   echo Stating Process...
   echo

   #Recursively rename some to other
   for FILE in `find . -name '*.rename_to_work'`
   do
	NEW=`echo $FILE | sed -e 's/\.rename_to_work//'`
	
	if [ $1 = "yes" -o ! -f $NEW ]
	then
		echo "Renamed from $FILE to $NEW."
		cp "$FILE" "$NEW"
	else
		echo "Already exists file $NEW and replace mode is 'no'"
	fi
   done
   echo

fi

echo "Done."
