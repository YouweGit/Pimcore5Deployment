<?php

namespace PimcoreDeployment;

use Pimcore\Model\Object\Fieldcollection as FieldCollectionObject;
use Pimcore\File;
//use Zend_Json;
use Pimcore\Model\Object\ClassDefinition\Service;

class FieldCollection {

    /** @var string */
    public $path;

    /**
     * FieldCollection constructor.
     */
    public function __construct() {
        $this->path = PIMCORE_PRIVATE_VAR . '/bundles/PimcoreDeployment/migration/field-collections/';
    }

    /**
     * Imports field collections from json files
     */
    public function import() {
        foreach(glob($this->path . '*.json') as $filename) {
            echo 'Importing: ' . str_replace(PIMCORE_PRIVATE_VAR, '', $filename) . ' (' . filesize($filename) . " bytes)\n";
            $this->save($filename);
        }
    }

    /**
     * Exports field collections
     */
    public function export() {
        $objects = new FieldCollectionObject\Definition\Listing();

        /** @var FieldCollectionObject\Definition $obj */
        foreach($objects->load() as $obj) {
            $json = $this->generateFieldCollectionDefinitionJson($obj);
            $filename = $this->path . 'field_collection_' . $obj->getKey() . '.json';

            echo 'Exporting: ' . str_replace(PIMCORE_PRIVATE_VAR, '', $filename) . ' (' . strlen($json) . ' bytes)' . PHP_EOL;

            File::put($filename, $json);
        }
    }

    /**
     * @param FieldCollectionObject\Definition $class
     *
     * @return string
     */
    public function generateFieldCollectionDefinitionJson($class) {
//        $json = Zend_Json::encode($this->map($class));
//        $json = Zend_Json::prettyPrint($json);

        $json = json_encode($class, JSON_PRETTY_PRINT);

        return $json;
    }

    /**
     * Transforms from an FieldCollectionObject\Definition object to an a multidimensional array containing all the fields and their values.
     *
     * @param FieldCollectionObject\Definition $class
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
//        $importData = Zend_Json::decode($json);
        $importData = json_decode($json, true);
        $fieldCollection = new FieldCollectionObject\Definition();
        $fieldCollection->setKey($importData['key']);
        Service::importFieldCollectionFromJson($fieldCollection, $json);
    }
}
