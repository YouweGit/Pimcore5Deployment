<?php

namespace PimcoreDeployment;

use Pimcore\Model\Object\DeploymentDataMigration;

class DeploymentDataMigrationManager {

//    public $path;
//    private $db;

    function __construct() {
        $this->db = \Pimcore\Db::get();
//        $this->path = PIMCORE_WEBSITE_PATH . '/var/deployment/migration/classes/';
    }

    public static function DDMtoArray($mig)
    {
        return array(
            'CName' => $mig->getCName(),
            'CId' => $mig->getCId(),
            'CId2' => $mig->getCId2(),
            'CId3' => $mig->getCId3(),
            'Timestamp' => $mig->getTimestamp()->getTimestamp(),
            'MigrationKey' => $mig->getMigrationKey(),
            'Mode' => $mig->getMode()
        );
    }

    public static function getModeByCnameAndId($cname, $cid, $cid2 = null, $cid3 = null) {
        $mode = 'default'; // when nothing is found

        $deployment_data_object = self::retrieveObjectByCnameAndId($cname, $cid, $cid2, $cid3);

        if($deployment_data_object)
        {
            $mode = $deployment_data_object->getMode();
        }

        return $mode;
    }

    public static function setModeByCnameAndId($cname, $cid, $cid2 = null, $cid3 = null, $mode)
    {
        $deployment_data_object = self::retrieveObjectByCnameAndId($cname, $cid, $cid2, $cid3);

        if(!$deployment_data_object) {

            // Create a new object (all related object migration keys will be created by the CLI)
            $new_object = \Pimcore\Model\Object\Service::createFolderByPath('/deployment/datamigration');
            $parent_id_of_new_object = $new_object->getId();

            $deployment_data_object = new DeploymentDataMigration();
            $deployment_data_object->setCName($cname);
            $deployment_data_object->setMode($mode);
            $deployment_data_object->setCId($cid);
            $deployment_data_object->setTimestamp(new \Pimcore\Date());
            $deployment_data_object->setMigrationKey(self::generateUniqueMigrationKey());
            $deployment_data_object->setParentId($parent_id_of_new_object);
            if($cid2) $deployment_data_object->setCId2($cid2);
            if($cid3) $deployment_data_object->setCId3($cid3);
            $deployment_data_object->setKey(self::makeKeyValid($cname . '-' . $cid . '-' . $cid2 . '-' . $cid3));
            $deployment_data_object->save();
        } else {
            $deployment_data_object->setMode($mode);
            $deployment_data_object->setTimestamp(new \Pimcore\Date());
            $deployment_data_object->save();
        }
    }

    public static function createKeyByCnameAndId($cname, $cid, $cid2 = null, $cid3 = null)
    {
        $mode = 'default';
        $deployment_data_object = self::retrieveObjectByCnameAndId($cname, $cid, $cid2, $cid3);
//        var_dump($deployment_data_object);
//        die();

        if(!$deployment_data_object) {

            // Create a new object (all related object migration keys will be created by the CLI)
            $new_object = \Pimcore\Model\Object\Service::createFolderByPath('/deployment/datamigration');
            $parent_id_of_new_object = $new_object->getId();

            $deployment_data_object = new DeploymentDataMigration();
            $deployment_data_object->setCName($cname);
            $deployment_data_object->setMode($mode);
            $deployment_data_object->setCId($cid);
            $deployment_data_object->setTimestamp(new \Pimcore\Date());
            $deployment_data_object->setMigrationKey(self::generateUniqueMigrationKey());
            $deployment_data_object->setParentId($parent_id_of_new_object);
            if($cid2) $deployment_data_object->setCId2($cid2);
            if($cid3) $deployment_data_object->setCId3($cid3);
            $deployment_data_object->setKey(self::makeKeyValid($cname . '-' . $cid . '-' . $cid2 . '-' . $cid3));
            $deployment_data_object->save();
        } else {
            // dont touch if it already exists
        }
        return $deployment_data_object;
    }

    public static function retrieveObjectByCnameAndId($cname, $cid, $cid2 = null, $cid3 = null)
    {
        $list = new DeploymentDataMigration\Listing();
        $list->setUnpublished(true);
        $list->addConditionParam('CName = ?', $cname);
        $list->addConditionParam('CId = ?', $cid);
        if ($cid2) $list->addConditionParam('CId2 = ?', $cid2);
        if ($cid3) $list->addConditionParam('CId3 = ?', $cid3);
        $deployment_data_object = $list->current();
        return $deployment_data_object;
    }

    public static function generateUniqueMigrationKey()
    {
        while(!self::idIsUnique($uid = uniqid(rand(), true)))
        {
            // loop until we get a unique id
        }
        return $uid;
    }

    public static function idIsUnique($uid)
    {
        if(!$uid) return false;
        $deployment_data_object = DeploymentDataMigration::getByMigrationKey($uid);
        if(!$deployment_data_object->count()) return true;
        return false;
    }

    public static function makeKeyValid($keybase)
    {
        $k = \Pimcore\File::getValidFilename($keybase);
        return $k;
    }

    public function getDataByDDM($mig)
    {
        $data = false;
        $sql = false;

        if($mig->getCName() == 'documents')
        {
            $sql = "SELECT * FROM documents WHERE id = " . $mig->getCid();
        }
        elseif($mig->getCName() == 'documents_link')
        {
            $sql = "SELECT * FROM documents_link WHERE id = " . $mig->getCid();
        }
        elseif($mig->getCName() == 'documents_page')
        {
            $sql = "SELECT * FROM documents_page WHERE id = " . $mig->getCid();
        }
        elseif($mig->getCName() == 'documents_elements')
        {
            $sql = "SELECT * FROM documents_elements WHERE documentId = " . $mig->getCid() .
                    " AND name = '" . $mig->getCid2() . "'";
        }
        elseif($mig->getCName() == 'properties')
        {
            $sql = "SELECT * FROM properties WHERE cid = " . $mig->getCid() .
                    " AND ctype = '" . $mig->getCid2() . "'" .
                    " AND name = '" . $mig->getCid3() . "'";
        }

        if ($sql) {
//            echo "\n" . $sql . "\n";
            $data = $this->db->fetchRow($sql);
        }
        return $data;
    }


}