<?php

namespace Pimcore5\DeploymentBundle\PimcoreDeployment;

use Pimcore5\DeploymentBundle\Config\Config;

abstract class DAbstract {
    /**
     * @var $localDBName string
     */
    protected $localDBName;
    protected $adapter;

    protected $config;
    /**
     *  Set up local database
     */
    public function __construct()
    {
        $this->config = \Pimcore::getContainer()->getParameter('pimcore5_deployment');
        $this->adapter = \Pimcore\Db::get();
        $this->dbName = $this->adapter->getDatabase();

    }

}