<?php

namespace Pimcore5\DeploymentBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class Pimcore5DeploymentBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/bundles/pimcore5deployment/js/pimcore/startup.js'
        ];
    }
}
