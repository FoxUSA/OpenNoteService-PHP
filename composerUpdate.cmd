@echo off
rem Created by Jake Liscom
rem Version 14.2.0

setlocal
SET PATH=%PATH%;C:\Users\jake\Desktop\Programs\xampp\xampp-win32-1.8.1-VC9\xampp\php\;C:\Program Files (x86)\Git\bin\

rem php ./composer.phar update
php ./composer.phar update -v

endlocal
pause