@echo off

title Stunts Control TransEdition

rem ****** Insert PHP-Path *******

set INSTPHP=c:\wamp\bin\php\php5.4.3

rem *************************************

PATH=%PATH%;%INSTPHP%;%INSTPHP%\extensions
"%INSTPHP%\php.exe" control.php

pause 