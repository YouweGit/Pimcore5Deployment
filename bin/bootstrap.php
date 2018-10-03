<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

$startfile = __DIR__ . '/../../../../vendor/pimcore/pimcore/lib/Bootstrap.php';

if(file_exists($startfile)) {   // pimcore 5.4.4
    require($startfile);
    $kernel = \Pimcore\Bootstrap::startupCli();

    return $kernel;

} else {   // pimcore 5.2 not even compatibru with 5.4

    require(__DIR__ . '/../../../../pimcore/config/startup_cli.php');

}

// /data/projects/kh-pimcore/vendor/pimcore/pimcore/lib/Bootstrap.php




