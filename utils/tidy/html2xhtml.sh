#!/bin/sh

if [ -z $1 ]
then
	echo "You must pass HTML filename"
else
	tidy -config html2xhtml.config $1
	echo
	echo "Saved on: `cat html2xhtml.config | grep output-file | cut -b 13-`"
fi
