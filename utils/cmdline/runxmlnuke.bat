@echo off

SET COMMAND_LINE="C:\dados\BYJG\xmlnuke\utils\cmdline\xmlnuke.cmd.php"
SET PHP_PATH="C:\wamp\bin\php\php5.3.10\php.exe"

:CHECK1
	IF EXIST %PHP_PATH% GOTO CHECK2

	echo runxmlnuke.sh: PHP location path is not setup properly
	echo.

	GOTO END

:CHECK2
	IF EXIST %COMMAND_LINE% GOTO CHECK3

	echo runxmlnuke.sh: The script xmlnuke.cmd.php is not setup correctly
	echo.

	GOTO END

:CHECK3
	IF EXIST config.inc.php GOTO START

	echo runxmlnuke.sh: You need run this script inside the XMLNuke Root Directory
	echo.

	GOTO END

:START

	IF %1.==. GOTO MESSAGE

	%PHP_PATH% -q %COMMAND_LINE% 
	GOTO END

:MESSAGE

	echo.
	echo ============================================================
	echo runscript.sh by JG (2012)
	echo ============================================================
	echo This script enable you run XMLNuke pages or modules directly 
	echo from the command line. 
	echo The default result is XML (rawxml=true) but you can get JSON 
	echo (rawjson=true)
	echo.
	echo USAGE:
	echo You have to pass key-value pair for each parameter you want 
	echo to use. 
	echo.
	echo For example: 
	echo ./runxmlnuke.sh site=sample xml=home lang=en-us
	echo.
	echo No arguments provided

:END

