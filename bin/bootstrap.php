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

$fileNotFound = true;
if (file_exists(__DIR__ . '/../../../pimcore/pimcore/lib/Bootstrap.php')) {
    require __DIR__ . '/../../../pimcore/pimcore/lib/Bootstrap.php';
    $fileNotFound = false;
}
if (file_exists(__DIR__ . '/../../../../pimcore/lib/Pimcore/Bootstrap.php') && $fileNotFound) {
    require __DIR__ . '/../../../../pimcore/lib/Pimcore/Bootstrap.php';
    $fileNotFound = false;
}
if ($fileNotFound) {
    echo "Could not bootstrap the CLI startup.";
    return -1;
}
$kernel = \Pimcore\Bootstrap::startupCli();

return $kernel;
