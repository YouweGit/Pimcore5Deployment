<?php
namespace PimcoreDeployment;
use Pimcore\Config;
/**
 * Class UpdateMysqlCredentials
 * Imports mysql credentials from an ini file with this structure and merges it with existing configuration
 * redis_hostname=[HOSTNAME]
 * redis_port=[PORT]
 * redis_database=[DATABASE]
 */
class UpdateRedisCredentials
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
    public function updateCacheFile()
    {
        // Get data from ini file
        $hostname = $this->iniFile->get('redis_hostname');
        $database = $this->iniFile->get('redis_database');
        $port = $this->iniFile->get('redis_port');
        // Validate the content of the ini file
        if (!$hostname || !$port || !$database) {
            throw new \InvalidArgumentException('The ini file provided has invalid structure or missing data');
        }
        // Get and validate config.php file
        $file = Config::locateConfigFile('cache.php');
        // Its quite common that a lot of projects do not have a cache.php file,
        // but if this class its called then probably we should create one
        if (!file_exists($file)) {
            $defaultPimcoreBackend = ['backend' => [
                'type'    => "\\Pimcore\\Cache\\Backend\\Redis2",
                'custom'  => 'true',
                'options' => [
                    'persistent' => '1',
                    'use_lua'    => '1'
                ]
            ]];
            $cacheConfig = new \Zend_Config($defaultPimcoreBackend, true);
        } elseif (!is_readable($file) || !is_writable($file)) {
            throw new \InvalidArgumentException('Config.php exists but its not readable or writable at ' . $file);
        } else {
            $cacheConfig = new \Zend_Config(include $file, true);
        }
        // Update params in cache.php
        $cacheConfig->get('backend')->options->merge(new \Zend_Config([
            'server'   => $hostname,
            'port'     => $port,
            'database' => $database,
        ]));
        $writer = new \Zend_Config_Writer_Array([
            'config'   => $cacheConfig,
            'filename' => $file
        ]);
        $writer->write();
        // Generate session file
        //
        $sessionConfigContent = <<<CONFIG
        <?php
        ini_set('session.save_handler','redis');
        ini_set('session.save_path', 'tcp://$hostname:$port?database=$database');

CONFIG;
        $sessionConfigPath = PIMCORE_CUSTOM_CONFIGURATION_DIRECTORY . DIRECTORY_SEPARATOR . 'redis_session.php';
        file_put_contents($sessionConfigPath, $sessionConfigContent);
    }
}