<?php

namespace PimcoreDeployment;

use Pimcore5\DeploymentBundle\Config\Config;

abstract class DAbstract {
    /**
     * @var $localDBName string
     */
    protected $localDBName;
    /**
     * @var mixed|Zend_Db_Adapter_Abstract
     */
    protected $adapter;

    protected $config;
    /**
     *  Set up local database
     */
    public function __construct()
    {
//        $this->config = \PimcoreDeployment\Plugin::getConfig();
        $this->config = Config::get();

        $this->adapter = \Pimcore\Db::get();

//        \Kint::$max_depth = 2;
//        d($this->adapter);
//        die();

//        $this->dbName = $this->adapter->getConfig()['dbname'];
        $this->dbName = $this->adapter->getDatabase();
//        $this->dbName = \Pimcore::getContainer();

//        var_dump($this->dbName);

//        die();

    }

}