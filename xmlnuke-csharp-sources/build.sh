#!/bin/sh

VERSION=`uname | cut -c1-6`

if [ "$VERSION" = "CYGWIN" ]
then
	echo =====================================
	echo SWITCHING for Win32 .NET compiler
	echo =====================================
	echo

	OLDPATH="$PATH"
	#PATH="$PATH:/cygdrive/c/WINDOWS/Microsoft.NET/Framework/v1.1.4322:/cygdrive/c/Program Files/Microsoft.NET/SDK/v1.1/Bin"
	PATH="$PATH:/cygdrive/c/WINDOWS/Microsoft.NET/Framework/v2.0.50727:/cygdrive/c/Program Files/Microsoft Visual Studio 8/SDK/v2.0/Bin"
	PATH="$PATH:/cygdrive/c/Program Files/Microsoft Visual Studio 9.0/VC/bin/"
	./build.bat
	PATH="$OLDPATH"

else

	SLN2MAKE_PATH="../utils/Sln2Make"

	gmcs $SLN2MAKE_PATH/sln2make.cs /out:$SLN2MAKE_PATH/sln2make.exe /target:exe

	if [ $? -eq 0 ]
	then

		$SLN2MAKE_PATH/sln2make.exe -u xmlnuke-csharp-sd.sln > makefile.linux
		sed -i -e 's/\.\/\.\.\///gi' makefile.linux  # FIX DLL directory

		if [ $? -eq 0 ]
		then
			make all TARGET=../xmlnuke-csharp/bin RELEASE=. -f makefile.linux
		fi;

	fi;

	if [ $? -eq 0 ]
	then 
		#clear
		echo Sucess Build in $SECONDS seconds; 
	else

		echo
		echo 
		echo Failed!
		echo

	fi;

fi;
