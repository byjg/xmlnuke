@echo off

copy ..\..\xmlnuke-csharp\bin\com.xmlnuke.db.dll .
if errorlevel 1 goto erro

csc /out:ImportXML.exe /target:exe ImportXML.cs /r:com.xmlnuke.db.dll
if errorlevel 1 goto erro

	echo ===========================================================
	echo Sucessfull Builded!
	echo ===========================================================
	goto fim 

:erro

	echo.
	echo ===========================================================
	echo You need have the com.xmlnuke.db.dll compiled!!
	echo ===========================================================
	echo. 
	echo Failed!
	echo.

:fim

      echo.
      pause
