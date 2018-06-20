<?php

namespace Pimcore5\DeploymentBundle\PimcoreDeployment;

use Exception;
use Pimcore\File;
use Pimcore\Model\DataObject\Objectbrick as ObjectBrickObject;
//use Zend_Json;
use Pimcore\Model\DataObject\ClassDefinition\Service;

/**
 * Class ObjectBrick
 * @package PimcoreDeployment
 */
class ObjectBrick {

    /** @var string */
    public $path;

    /**
     * ObjectBrick constructor.
     */
    public function __construct() {
        $this->path = PIMCORE_PRIVATE_VAR . '/bundles/PimcoreDeployment/migration/objectBricks/';
    }

    /**
     * Imports object bricks from json files
     */
    public function import() {
        foreach(glob($this->path . '*.json') as $filename) {
            echo 'Importing: ' . str_replace(PIMCORE_PRIVATE_VAR, '', $filename) . ' (' . filesize($filename) . " bytes)\n";
            $this->save($filename);
        }
    }

    /**
     * Exports object bricks
     */
    public function export() {
        $objects = new ObjectBrickObject\Definition\Listing();

        /** @var ObjectBrickObject\Definition $obj */
        foreach($objects->load() as $obj) {
            $json = $this->generateObjectBrickDefinitionJson($obj);
            $filename = $this->path . 'objectBrick_' . $obj->getKey() . '.json';

            echo "Exporting: " . str_replace(PIMCORE_PRIVATE_VAR, '', $filename) . " (" . strlen($json) . " bytes)" . PHP_EOL;

            File::put($filename, $json);
        }
    }

    /**
     * @param ObjectBrickObject\Definition $class
     *
     * @return string
     */
    public function generateObjectBrickDefinitionJson($class) {
        $json = json_encode($class, JSON_PRETTY_PRINT);

        return $json;
    }

    /**
     * Transforms from an ObjectBrickObject\Definition object to an a multidimensional array containing all the fields and their values.
     *
     * @param ObjectBrickObject\Definition $class
     *
     * @return object
     */
    private function map($class) {
        return json_decode(json_encode($class), true);
    }

    /**
     * @param string $filename
     */
    private function save($filename) {
        $json = file_get_contents($filename);
        $importData = json_decode($json, true);
        $object_brick = new ObjectBrickObject\Definition();
        $object_brick->setKey($importData['key']);
        Service::importObjectBrickFromJson($object_brick, $json);
    }
}