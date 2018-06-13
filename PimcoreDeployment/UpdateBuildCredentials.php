<?php

// not yet upgraded to pimcore 5

namespace Pimcore5\DeploymentBundle\PimcoreDeployment;

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
class UpdateBuildCredentials
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

    public function updateBuildFile()
    {

        // Get data from ini file
        $hostname = $this->iniFile->get('mysql_hostname');
        $port = $this->iniFile->get('mysql_port');
        $database = $this->iniFile->get('mysql_database');
        $username = $this->iniFile->get('mysql_user');
        $password = $this->iniFile->get('mysql_password');

        // Validate the content of the ini file
        if (!$hostname || !$port || !$database || !$username || !$password) {
            throw new \InvalidArgumentException('The ini file provided has invalid structure or missing data');
        }

        /*
         * /data/projects/PROJECT_NAME/tools/build/local.cfg
         *
         * # database 
         *
         * DBHost=127.0.0.1 
         * DBName=DATABASE_NAME 
         * DBUser=root 
         * DBPassword=root 
         * DBPort=3306
         *
         */

        // Get and validate system.php file
        $file = realpath(__DIR__) . '/../../../../tools/build/local.cfg';
        if (!file_exists($file) || !is_readable($file) || !is_writable($file)) {
            throw new \InvalidArgumentException('Config file does not exist at ' . $file);
        }

        $data =     "# database\n";
        $data .=    "DBHost=" . $hostname . "\n";
        $data .=    "DBName=" . $database . "\n";
        $data .=    "DBUser=" . $username . "\n";
        $data .=    "DBPassword=" . $password . "\n";
        $data .=    "DBPort=" . $port . "\n";
        $data .=    "\n";

        file_put_contents($file, $data);

    }
}
