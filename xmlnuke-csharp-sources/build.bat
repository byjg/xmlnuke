@echo off

SET SLN2MAKE_PATH=..\utils\Sln2Make

csc /out:%SLN2MAKE_PATH%\sln2make.exe /target:exe %SLN2MAKE_PATH%\sln2make.cs 

if errorlevel 1 goto erro

	%SLN2MAKE_PATH%\sln2make.exe -w xmlnuke-csharp-sd.sln > makefile
	if errorlevel 1 goto erro2

	nmake all TARGET=..\xmlnuke-csharp\bin RELEASE=. 
	if errorlevel 1 goto erro2

	cls
	echo ===========================================================
	echo Sucess Build; 
	echo ===========================================================

	goto fim 

:erro

	echo.
	echo. 
	echo ===========================================================
	echo Failed!
	echo ===========================================================
	echo.
	goto fim

:erro2

	echo.
	echo. 
	echo ===========================================================
	echo Failed to compile Xmlnuke!
	echo ===========================================================
	echo.

:fim

      echo.
      pause
