<?php


namespace PimcoreDeployment;

use Pimcore\Config;


/**
 * Class UpdateMysqlCredentials
 * Imports mysql credentials from an ini file with this structure and merges it with existing configuration
 * mysql_hostname=[HOSTNAME]
 * mysql_port=[PORT]
 * mysql_database=[DATABASE_NAME]
 * mysql_user=[DATABASE_USERNAME]
 * mysql_password=[DATABASE_PASSWORD]
 */
class UpdateMysqlCredentials
{
    /** @var  \Zend_Config_Ini */
    private $iniFile;

    /**
     * @param string $pathToIniFile
     * @throws \Zend_Config_Exception Zend has build in validation if it cannot read and parse the file
     */
    public function __construct($pathToIniFile)
    {
        $this->iniFile = new \Zend_Config_Ini($pathToIniFile, null, ['allowModifications' => false]);
    }

    public function updateSystemFile()
    {

        // Get data from ini file
        $hostname = $this->iniFile->get('mysql_hostname');
        $port = $this->iniFile->get('mysql_port');
        $database = $this->iniFile->get('mysql_database');
        $user = $this->iniFile->get('mysql_user');
        $password = $this->iniFile->get('mysql_password');

        // Validate the content of the ini file
        if (!$hostname || !$port || !$database || !$user || !$password) {
            throw new \InvalidArgumentException('The ini file provided has invalid structure or missing data');
        }

        // Get and validate system.php file
        $file = Config::locateConfigFile('system.php');
        if (!file_exists($file) || !is_readable($file) || !is_writable($file)) {
            throw new \InvalidArgumentException('System.php does not exist at ' . $file);
        }
        $systemConfig = new \Zend_Config(include $file, true);
        // Update params in system.php
        /** @var \Zend_Config $dbParamsSection */
        $systemConfig->get('database')->params = [
            'host'     => $hostname,
            'username' => $user,
            'password' => $password,
            'dbname'   => $database,
            'port'     => $port,
        ];

        $writer = new \Zend_Config_Writer_Array([
                'config'   => $systemConfig,
                'filename' => $file]
        );
        $writer->write();
    }
}
