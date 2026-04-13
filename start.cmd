@echo off
TITLE Phantomarine Server
set PHP_BINARY=bin\php\php.exe

if exist %PHP_BINARY% (
    set POCKETMINE_FILE=start.php
) else (
    echo [ERROR] PHP binary not found. Please make sure bin\php\php.exe exists.
    pause
    exit 1
)

if exist %POCKETMINE_FILE% (
    %PHP_BINARY% %POCKETMINE_FILE% %*
) else (
    echo [ERROR] start.php not found.
    pause
    exit 1
)

pause
