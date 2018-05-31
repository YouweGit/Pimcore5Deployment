<?php
// Because we don't load pimcore we need to load Zend manually and load the pimcore deployment class
$autoLoader = require __DIR__ . '/../../../vendor/autoload.php';
$autoLoader->addPsr4("PimcoreDeployment\\", __DIR__ . '/../lib/PimcoreDeployment');

// We are using \Pimcore\Config ->getConfig("system.php"),
// this relies on the following constants which we also need to set up manually
define('PIMCORE_WEBSITE_PATH', __DIR__ . '/../../../website');
define('PIMCORE_PRIVATE_VAR', PIMCORE_WEBSITE_PATH . '/var');
define('PIMCORE_CONFIGURATION_DIRECTORY', PIMCORE_PRIVATE_VAR . '/config');
define('PIMCORE_CUSTOM_CONFIGURATION_DIRECTORY', PIMCORE_WEBSITE_PATH . '/config');

//this is optional, memory limit could be increased further (pimcore default is 1024M)
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '-1');

$time = microtime(true);
$memory = memory_get_usage();

$action = [
    'update-mysql-credentials',
    'update-redis-credentials',
    'update-build-credentials',
];

try {
    $opts = new Zend_Console_Getopt([
        'action|a=s'                   => '',
        'mysql-credentials-path|mcp-s' => '',
        'redis-credentials-path|rcp-s' => '',
        'build-credentials-path|bcp-s' => ''
    ]);
    $opts->parse();

    if (!isset($opts->action) || !in_array($opts->action, $action, true)) {
        throw new InvalidArgumentException(
            "\n" .
            'USAGE INSTRUCTIONS' .
            "\n" .
            'Action parameter should be one of the following:' . "\n" .
            'update-mysql-credentials : updates mysql credentials from an ini file' . "\n".
            'update-redis-credentials : updates redis credentials from an ini file' . "\n".
            'update-build-credentials : updates build credentials from an ini file' . "\n"
        );
    }

} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}

$mysqlCredentialsPath = $opts->getOption('mysql-credentials-path');
$redisCredentialsPath = $opts->getOption('redis-credentials-path');
$buildCredentialsPath = $opts->getOption('build-credentials-path');


switch ($opts->action) {
    case 'update-mysql-credentials':
        $umc = new \PimcoreDeployment\UpdateMysqlCredentials($mysqlCredentialsPath);
        $umc->updateSystemFile();
        break;
    case 'update-redis-credentials':
        $urc = new \PimcoreDeployment\UpdateRedisCredentials($redisCredentialsPath);
        $urc->updateCacheFile();
        break;
    case 'update-build-credentials':
        $urc = new \PimcoreDeployment\UpdateBuildCredentials($buildCredentialsPath);
        $urc->updateBuildFile();
        break;

}
