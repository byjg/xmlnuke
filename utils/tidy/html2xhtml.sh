#!/bin/sh

if [ -z $1 ]
then
	echo "You must pass HTML filename"
else
	MYDIR=`dirname $0`
	tidy -config $MYDIR/html2xhtml.config $1
	echo
	echo "Saved on: `cat $MYDIR/html2xhtml.config | grep output-file | cut -b 13-`"
fi
