#!/bin/sh

erro=1

cp ../../xmlnuke-csharp/bin/com.xmlnuke.db.dll .
if [ $? -eq 0 ]
then

	gmcs /out:importxml.exe /target:exe ImportXML.cs /r:com.xmlnuke.db.dll
	if [ $? -eq 0 ]
	then 
		echo ===========================================================
		echo Sucessfull Builded!
		echo ===========================================================
		erro=0
	fi
	 
fi

if [ $erro -eq 1 ]
then
	echo ===========================================================
	echo You need have the com.xmlnuke.db.dll compiled!!
	echo ===========================================================
	echo
	echo Failed!
	echo

fi
