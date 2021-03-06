<?php

namespace Pimcore5\DeploymentBundle\PimcoreDeployment;

use Exception;
use Pimcore\File;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition\CustomLayout as PimcoreCustomLayout;

class CustomLayout
{
    /** @var string */
    public $path;
    private $db;

    public function __construct()
    {
        $this->db = \Pimcore\Db::get();
        $this->path = PIMCORE_PRIVATE_VAR . '/bundles/PimcoreDeployment/migration/custom_layouts/';
    }

    /**
     * Exports custom layouts to json file
     */
    public function export()
    {
        File::setDefaultMode(0755);

        if (!is_dir($this->path)) {
            File::mkdir($this->path);
        }

        $list = new PimcoreCustomLayout\Listing();
        /** @var PimcoreCustomLayout $customLayout */
        foreach ($list->load() as $customLayout) {

            $json = $this->generateCustomLayoutJson($customLayout);
            $filename = $this->path . 'custom_layout_' . $customLayout->getClassId() . '_' . $customLayout->getName() . '.json';
            echo 'Exporting: ' . str_replace(PIMCORE_PRIVATE_VAR, '', $filename) . "\n";
            File::put($filename, $json);
        }
    }

    /**
     * @param PimcoreCustomLayout $customLayout
     * @return string
     */
    public function generateCustomLayoutJson($customLayout)
    {
        unset(
            $customLayout->creationDate,
            $customLayout->modificationDate,
            $customLayout->userModification,
            $customLayout->fieldDefinitions
        );
        $json = json_encode($customLayout, JSON_PRETTY_PRINT);

        return $json;
    }


    /**
     * Imports custom layouts from json files
     * @throws \Exception
     */
    public function import()
    {
        $this->db->query('TRUNCATE custom_layouts')->execute();
        foreach (glob($this->path . '*.json') as $filename) {
            echo 'Importing: ' . str_replace(PIMCORE_PRIVATE_VAR, '', $filename) . "\n";
            $this->save($filename);
        }
    }

    /**
     * @param string $filename
     * @throws Exception
     * @throws Zend_Json_Exception
     */
    public function save($filename)
    {
        $json = file_get_contents($filename);
        $importData = json_decode($json, true);

        // Safe to do an insert with id as long as custom_layouts is truncated at import()
        $this->db->insert('custom_layouts', [
            'id'          => $importData['id'],
            'name'        => $importData['name'],
            'description' => $importData['description'],
            'userOwner'   => $importData['userOwner'],
            'classId'     => $importData['classId'],
            'default'     => $importData['default'],
        ]);

        $customLayout = PimcoreCustomLayout::getById($importData['id']);
        $layout = DataObject\ClassDefinition\Service::generateLayoutTreeFromArray($importData['layoutDefinitions'], true);
        $customLayout->setLayoutDefinitions($layout);
        $customLayout->save();
    }


}