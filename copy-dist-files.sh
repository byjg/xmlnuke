#!/bin/sh

echo
echo COPY DIST FILES.sh
echo July 6th 2013
echo by Joao Gilberto Magalhaes
echo

if [ -z $1 ] ; then

   echo This script will rename all config files to their proper names. 
   echo
   echo copy-dist-files [method] [replace]
   echo \ \ [method] - copy or link
   echo \ \ [replace] - yes or no
   echo

else

   echo Starting Process...
   echo

   CURDIR="`dirname \"$0\"`"              # relative
   CURDIR="`( cd \"$CURDIR\" && pwd )`"  # absolutized and normalized

   METHOD="$1"
   REPLACE="$2"

   if [ -z $REPLACE ] ; then 
      REPLACE="no"
   fi

   #Recursively rename some to other
   for FILE in `find $CURDIR/ -name '*.dist'`
   do
	NEW=`echo $FILE | sed -e 's/\.dist//'`
	
	if [ $REPLACE = "yes" -o ! -f $NEW ]
	then
		echo "$METHOD: from $FILE to $NEW."
		if [ $METHOD = "copy" ] ; then
		   cp "$FILE" "$NEW"
		else
		   if [ -f "$NEW" ] ; then rm "$NEW"; fi
		   ln -s "$FILE" "$NEW"
		fi
	else
		echo "Already exists file $NEW and replace mode is 'no'"
	fi
   done
   echo

fi

echo "Done."
