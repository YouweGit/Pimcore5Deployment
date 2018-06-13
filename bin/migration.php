<?php

require_once __DIR__ . '/bootstrap.php';
use Pimcore\Cache;
use \Pimcore\Model\Version;

//this is optional, memory limit could be increased further (pimcore default is 1024M)
ini_set('memory_limit', '1024M');
ini_set("max_execution_time", "-1");

$time = microtime(true);
$memory = memory_get_usage();

//execute in admin mode
define("PIMCORE_ADMIN", true);

$actionen = [
    'import-definition',
    'export-definition',
    'import-customlayout',
    'export-customlayout',
    'import-staticdata',
    'export-staticdata',
    'import-customsql',
    'import-field-collection',
    'export-field-collection',
    'import-content',
    'export-content',
    'drop-views',
    'clear-classes',
    'list-definitionexport',
    'export-dump',
    'import-dump',
    'export-bricks',
    'import-bricks',
];

$argvcopy = $argv;
array_shift($argvcopy);
$key = false;
$p = [];
foreach($argvcopy  as $av) {
    if($key = !$key) {
        $keyName = $av;
    } else {
        $p[$keyName] = $av;
    }
}
$opts = new stdClass();
$opts->action = @$p['-a'];
$opts->classes = @$p['-c'];
$opts->classids = @$p['-i'];


try {

    if (!isset($opts->action) || !in_array($opts->action, $actionen)) {
        throw new Exception(
            "\n" .
            "USAGE INSTRUCTIONS" .
            "\n" .
            'Action parameter should be one of the following:' . "\n" .
            'import-definition        : re-create pimcore classes from json definitions' . "\n" .
            'export-definition        : re-create json definitions from pimcore classes'. "\n" .
            'import-customlayout      : re-create pimcore custom layouts from json definitions' . "\n" .
            'export-customlayout      : re-create json definitions from pimcore custom layouts'. "\n" .
            'import-staticdata        : re-create selected tables from static data dump' . "\n" .
            'export-field-collection  : re-create pimcore field collections from json definitions' . "\n" .
            'export-staticdata        : re-create static data dump from selected tables'. "\n" .
            'import-customsql         : re-create selected tables from static data dump' . "\n" .
            'import-field-collection  : Imports fields collections' . "\n" .
            'import-content           : N/A update content on server' . "\n" .
            'export-content           : N/A export selected content on local/dev'. "\n" .
            'drop-views               : drop views or tables that should be views' . "\n" .
            'clear-classes            : empty the classes table' . "\n" .
            'list-definitionexport    : list the current jsons' . "\n" .
            'export-dump              : export database to sql file' . "\n" .
            'import-dump              : import database from sql file' . "\n" .
            "\n" .
            'Optional classes parameter may list the class names comma seperated.' . "\n" .
            "\n" .
            'Example:' . "\n" .
            'php migration.php -a export-definition -c product,person' . "\n" .
            "\n" .
            'Note: for drop-views you can also use class ids:' . "\n" .
            'php migration.php -a drop-views -i 2,5,6' . "\n" .
            '');
    }

} catch (Exception $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}

$classes = ( ($opts->classes !== true && $opts->classes !== NULL) ? explode(',', $opts->classes) : false );
$classids = ( ($opts->classids !== true && $opts->classids !== NULL) ? explode(',', $opts->classids) : false );

Version::disable();
Cache::disable();

$plugin = "Pimcore5\\DeploymentBundle\\Pimcore5DeploymentBundle";

$state = [];
$bm = \Pimcore::getContainer()->get('pimcore.extension.bundle_manager');
$bundleClass = $plugin;

/* @var \Pimcore\Extension\Bundle\PimcoreBundleManager $bm */

if(!$bm->isEnabled($bundleClass)) {

    echo "\nEnabling plugin on the fly.\n";

    try {
        $bm->enable($bundleClass, $state);

        echo "\nPLUGIN ENABLED\n";

        $command = 'php ' . implode(' ', $argv);
        echo "\nRe-executing command: [ $command ] \n";
        echo shell_exec($command);
        die();

    } catch (\Exception $e) {
        echo "\nERROR: COULD NOT ENABLE PLUGIN\n";

        return;
    }

}

echo "\naction: " . $opts->action . "\n";

$def        = new \Pimcore5\DeploymentBundle\PimcoreDeployment\Definition();
$customsql  = new \Pimcore5\DeploymentBundle\PimcoreDeployment\Customsql();
$mig        = new \Pimcore5\DeploymentBundle\PimcoreDeployment\Migration();
//$con        = new \Pimcore5\DeploymentBundle\PimcoreDeployment\Content();
$cl         = new \Pimcore5\DeploymentBundle\PimcoreDeployment\CustomLayout();
$fc         = new \Pimcore5\DeploymentBundle\PimcoreDeployment\FieldCollection();
$ob         = new \Pimcore5\DeploymentBundle\PimcoreDeployment\ObjectBrick();

switch ($opts->action) {
    case 'clear-classes':
        $def->clearClasses($classes);
        break;
    case 'drop-views':
        $def->dropViews($classes, $classids);
        break;
    case 'import-definition':
        $def->import($classes);
        break;
    case 'list-definitionexport':
        $def->listExport($classes);
        break;
    case 'export-definition':
        $def->export($classes);
        break;
    case 'import-customlayout':
        $cl->import();
        break;
    case 'export-customlayout':
        $cl->export();
        break;
    case 'import-customsql':
        $customsql->migrate();
        break;
    case 'import-staticdata':
        $mig->migrate();
        break;
    case 'export-staticdata':
        $mig->create();
        break;
//    case 'import-content':
//        $con->importContent();
//        break;
//    case 'export-content':
//        $con->exportContent();
//        break;
    case 'import-field-collection':
        $fc->import();
        break;
    case 'export-field-collection':
        $fc->export();
        break;
    case 'import-dump':
        $mig->importDump();
        break;
    case 'export-dump':
        $mig->exportDump();
        break;
    case 'export-bricks':
        $ob->export();
        break;
    case 'import-bricks':
        $ob->import();
        break;
}
