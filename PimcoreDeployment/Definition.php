<?php

namespace PimcoreDeployment;

use Exception;
use Pimcore\Model\Object\ClassDefinition;
use Pimcore5\DeploymentBundle\Config\Config;

class Definition {

    public $path;
    private $db;

    public function __construct() {
        $this->db = \Pimcore\Db::get();
        $this->path = PIMCORE_PRIVATE_VAR . '/bundles/PimcoreDeployment/migration/classes/';
    }

    /**
     * Exports class definition to json file
     */
    public function export($classes = false) {

//        echo ' == Config test: == ' . Config::get('url') . ' == ';
//        die('asdfasdfasdfasdfasdf export function called');

        \Pimcore\File::setDefaultMode(0755);

        if (!is_dir($this->path)) {
            \Pimcore\File::mkdir($this->path);
        }


        $list = new ClassDefinition\Listing;
        foreach ($list->load() as $class) {
//            echo "class: " . var_export($class,1) . "\n";
            echo "class: " . $class->getName() . "\n";
//            die('asdf');
            // check if class needs to be skipped ($classes)
            if ($classes && !in_array($class->getName(), $classes)) continue;

            $json = $this->generateClassDefinitionJson($class);
            $filename = $this->path . 'class_' . $class->getName() . '.json';
            echo "Exporting: " . str_replace(PIMCORE_PRIVATE_VAR, '', $filename) . " (" . strlen($json) . " bytes)\n";
            \Pimcore\File::put($filename, $json);
        }
    }

    /**
     * @static
     * @param  ClassDefinition $class
     * @return string
     */
    public function generateClassDefinitionJson($class) {

        $data = \Pimcore\Model\Webservice\Data\Mapper::map($class, '\Pimcore\Model\Webservice\Data\ClassDefinition\Out', 'out');
        unset(
            $data->fieldDefinitions,
            $data->modificationDate,
            $data->modificationDate,
            $data->userOwner,
            $data->userModification
        );
        $data->propertyVisibility = $class->propertyVisibility;
//        $json = \Zend_Json::encode($data);
//        $json = \Zend_Json::prettyPrint($json);
//
        $json = json_encode($data, JSON_PRETTY_PRINT);
        return $json;
    }

    /**
     * Clear-classes
     * @param bool $classes
     */
    public function clearClasses($classes = false) {

        echo "Clearing classes table\n";
        $this->db->query('DELETE FROM classes');
    }

    /**
     * Dropviews
     * @param bool|array $classes
     * @param bool $class_ids
     */
    public function dropViews($classes = false, $class_ids = false) {

        // grab mappings from json files, if $classes (names) given
        if ($classes && is_array($classes)) {
            $classes = $this->_getJsonClassIds($classes);
        } elseif ($class_ids) {
            $classes = $class_ids;
        }

        $views = $this->db->fetchAll("SELECT
                      CONCAT(TABLE_SCHEMA,'.',TABLE_NAME) AS fulltablename,
                      TABLE_TYPE as ttype,
                      TABLE_NAME as tablename
                    FROM information_schema.TABLES
                    WHERE TABLE_SCHEMA = " . $this->db->quote($this->db->getConfig()['dbname']));

//        ---- object_##
//        ---- object_localized_##_XX_XX
//        ---- object_localized_##_XX

        foreach ($views as $view) {
            // check if class needs to be skipped ($classes)

            $should_be_view = false;
            $class_id = false;
            if (preg_match('/^object_([0-9]+)$/', $view['tablename'], $matches)
                ||
                preg_match('/^object_localized_([0-9]+)_[A-z]{2}$/', $view['tablename'], $matches)
                ||
                preg_match('/^object_localized_([0-9]+)_[A-z]{2}_[A-z]{2}$/', $view['tablename'], $matches)
            ) {
                $should_be_view = true;
                $class_id = $matches[1];
            }

            if ($should_be_view && $class_id) {
                echo 'Found: [' . $view['ttype'] . '] ' . $view['tablename'] . ": ";

                // if only specific classes are selected:
                if ($classes && !in_array($class_id, $classes)) {
                    echo "skipping\n";
                    continue;
                }

                if ($view['ttype'] === 'VIEW') {
                    echo "dropping view\n";
                    $this->db->query('DROP VIEW IF EXISTS ' . $this->db->quoteIdentifier($view['fulltablename']))->execute();
                } elseif ($view['ttype'] === 'BASE TABLE') {
                    echo "dropping table\n";
                    $this->db->query('DROP TABLE IF EXISTS ' . $this->db->quoteIdentifier($view['fulltablename']))->execute();
                }
            }
        }
    }

    /**
     * Imports classes from json files
     * @param bool $classes
     * @throws \Exception
     */
    public function import($classes = false) {
        foreach (glob($this->path . '*.json') as $filename) {
            // check if class needs to be skipped ($classes)
            $class = substr($filename, strpos($filename, 'class_'));
            $class = str_replace('class_', '', $class);
            $class = str_replace('.json', '', $class);
            if ($classes && !in_array($class, $classes, true)) continue;

            echo 'Importing: ' . str_replace(PIMCORE_PRIVATE_VAR, '', $filename) . ' (' . filesize($filename) . " bytes)\n";
            $this->save($filename);
        }
    }

    /**
     * Imports classes from json files
     * @param bool $classes
     */
    public function listExport($classes = false) {

        $id_array = [];

        foreach (glob($this->path . '*.json') as $filename) {
            // check if class needs to be skipped ($classes)
            $class = substr($filename, strpos($filename, 'class_'));
            $class = str_replace('class_', '', $class);
            $class = str_replace('.json', '', $class);
            if ($classes && !in_array($class, $classes)) continue;

            echo 'Found: ' . str_replace(PIMCORE_PRIVATE_VAR, '', $filename) . ' (' . filesize($filename) . " bytes)\n";
            $data = json_decode(file_get_contents($filename));
            //var_dump($data);
            echo 'id [' . $data->id . '] name [' . $data->name . "]\n";
            $id_array[$data->id][] = $data->name;
        }

        ksort($id_array);
//        print_r($id_array);
        foreach ($id_array as $classId => $idsArray) {
            if (count($idsArray) > 1) {
                echo 'WARNING! OVERLAPPING IDS: ';
            }
            echo $classId . ' : ' . implode('+', $idsArray) . "\n";
        }

    }

    /**
     * @param string $filename
     * @return bool
     * @throws Exception
     * @throws \Zend_Json_Exception
     */
    public function save($filename) {
        $json = file_get_contents($filename);
//        $importData = \Zend_Json::decode($json);
        $importData = json_decode($json, true);
        $id = $this->db->quote($importData['id']);
        $name = $this->db->quote($importData['name']);

        $classDefinition = new ClassDefinition();
        /** @var ClassDefinition\Dao $dao */
        $dao = $classDefinition->getDao();

        $className = $dao->getNameById($importData['id']);

        if (!$className) {
            $this->db->query("INSERT INTO classes(id,name) VALUES($id, $name)");
        } else {
            $this->db->query("UPDATE classes SET name = $name WHERE id=$id");
        }

        $this->db->query("UPDATE objects SET o_className = $name  WHERE o_classId=$id");

        $classDefinition->setName($importData['name']);
        $classDefinition->setId($importData['id']);
        return ClassDefinition\Service::importClassDefinitionFromJson($classDefinition, $json, true);
    }

    /**
     * @param array $classes Class-names
     * @return array or false
     */
    private function _getJsonClassIds($classes) {
        $class_ids = array();
        foreach (glob($this->path . '*.json') as $filename) {
            $classInfo = json_decode(file_get_contents($filename), true);
            $class_id = $classInfo['id'];
            $class_name = $classInfo['name'];
            if (in_array($class_name, $classes, true)) {
                $class_ids[$class_name] = $class_id;
            }
        }
        return count($class_ids) > 0 ? $class_ids : false;
    }
}
