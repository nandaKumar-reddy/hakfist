@echo off
title HakFist Magento application deployment
pause

cd C:\wamp64\www\hakfist

@echo:  
echo Removed Generation code
echo -----------------------
RMDIR "generated\code" /S /Q

@echo: 
echo Removed Generation metadata
echo ---------------------------
RMDIR "generated\metadata" /S /Q

@echo: 
echo Removed var cache
echo -----------------
RMDIR "var\cache" /S /Q

@echo: 
echo Removed var composer_home
echo -------------------------
RMDIR "var\composer_home" /S /Q

@echo: 
echo Removed var log
echo ---------------
RMDIR "var\log" /S /Q

@echo: 
echo Removed var page_cache
echo ----------------------
RMDIR "var\page_cache" /S /Q

@echo: 
echo Removed var tmp
echo ---------------
RMDIR "var\tmp" /S /Q

@echo: 
echo Removed var view_preprocessed
echo -----------------------------
RMDIR "var\view_preprocessed" /S /Q

@echo: 
echo Removed static frontend
echo -----------------------
RMDIR "pub\static\frontend" /S /Q

@echo: 
echo Removed static adminhtml
echo ------------------------
RMDIR "pub\static\adminhtml" /S /Q

REM @echo: 
REM echo All command exectuion completed!
REM pause

REM cd C:\wamp64\www\StarkCarpetRedesign2\

@echo:  
echo URLs Change
echo -----------------
REM php bin/magento setup:store-config:set --base-url="http://scalamandre.local/"
REM php bin/magento setup:store-config:set --base-url-secure="http://scalamandre.local/"
REM echo URLs Change Done
 echo URLs Change Skipped

@echo:  
echo Cleaning
echo -----------------
php -dmemory_limit=6G bin/magento cache:clean

@echo: 
echo Flush
echo --------------
php -dmemory_limit=6G bin/magento cache:flush

@echo: 
echo Upgrade
echo ----------------
php -dmemory_limit=6G bin/magento setup:upgrade

@echo: 
echo Reindex
echo ----------------
 php -dmemory_limit=6G bin/magento indexer:reindex
REM echo Skipped reindex
 echo Completed reindex

REM @echo: 
REM echo Di:Compile
REM echo ----------------
REM php -dmemory_limit=-1 bin/magento setup:di:compile
REM echo Skipped reindex
REM echo Completed reindex

@echo: 
echo Deployment all.
echo ----------------------
php -dmemory_limit=6G bin/magento setup:static-content:deploy -f

@echo: 
echo All command exectuion completed!
pause
