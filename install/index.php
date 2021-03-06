<?php

IncludeModuleLangFile(__FILE__);

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class arrilot_systemcheck extends CModule
{
    var $MODULE_ID = 'arrilot.systemcheck';
    var $MODULE_DESCRIPTION = '';
    function __construct()
    {
        $this->MODULE_VERSION = '0.1.1';
        $this->MODULE_VERSION_DATE = '2019-01-01';

        $this->MODULE_NAME = 'Bitrix System Checks';
        $this->MODULE_DESCRIPTION = 'Производит мониторинг приложения';
        $this->MODULE_GROUP_RIGHTS = 'Y';
    }

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallFiles();
        $this->InstallDB();
    }

    public function DoUninstall()
    {
        $this->UnInstallFiles();
        $this->UnInstallDB();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function InstallFiles()
    {
        CopyDirFiles(__DIR__ ."/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
        return true;
    }

    public function UnInstallFiles()
    {
        DeleteDirFiles(__DIR__ . "/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        return true;
    }
    
    public function InstallDB()
    {
        $connection = $connection = Application::getConnection();

        $connection->query('DROP TABLE IF EXISTS arrilot_systemcheck_checks_data');

        $sql = "
        CREATE TABLE `arrilot_systemcheck_checks_data` (
          `ID` INT NOT NULL AUTO_INCREMENT,
          `MONITORING` VARCHAR(256) NOT NULL,
          `CHECK` VARCHAR(256) NOT NULL,
          `DATA` TEXT NOT NULL,
          `CREATED_AT` DATETIME NOT NULL,
          PRIMARY KEY (id),
          INDEX IX_CREATED_AT (`CREATED_AT`),
          INDEX IX_MONITORING (`MONITORING`),
          INDEX IX_CHECK (`CHECK`)
        );";
        $connection->query($sql);
    
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            '\Arrilot\BitrixSystemCheck\EventHandlers',
            'addMonitoringPageToAdminMenu'
        );
        
        return true;
    }
    
    public function UnInstallDB()
    {
        $connection = $connection = Application::getConnection();

        $connection->query('DROP TABLE IF EXISTS arrilot_systemcheck_checks_data');
    
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            '\Arrilot\BitrixSystemCheck\EventHandlers',
            'addMonitoringPageToAdminMenu'
        );

        return true;
    }
}