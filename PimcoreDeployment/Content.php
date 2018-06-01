<?php

namespace PimcoreDeployment;

use Pimcore\Model\Object\DeploymentDataMigration;

class Content extends \PimcoreDeployment\DAbstract
{
    /**
     * @var string
     */
    private $backupPath = PIMCORE_PRIVATE_VAR . '/bundles/PimcoreDeployment/migration/content/';
    private $backupTmpPath = PIMCORE_PRIVATE_VAR . '/bundles/PimcoreDeployment/migration/content/tmp/';
    /**
     * @var string
     */
    private $dumpFileName = 'contentdata.zip';
    /**
     * @var Zend_Config
     */
    public $config;

    private $db;
    private $ddmm;

    private $cts = array(
        'documents'         => array(
            'idfield'       => 'id',
            'idfield2'      => null,
            'idfield3'      => null
        ),
        'documents_link'    => array(
            'idfield'       => 'id',
            'idfield2'      => null,
            'idfield3'      => null
        ),
        'documents_page'    => array(
            'idfield'       => 'id',
            'idfield2'      => null,
            'idfield3'      => null
        ),
        'documents_elements' => array(
            'idfield'       => 'documentId',
            'idfield2'      => 'name',
            'idfield3'      => null
        ),
        'properties'        => array(
            'idfield'       => 'cid',
            'idfield2'      => 'ctype',
            'idfield3'      => 'name'
        ),
    );

    private $ifm;

    function __construct()
    {
        parent::__construct();
        $this->db = \Pimcore\Db::get();
        $this->ddmm = new DeploymentDataMigrationManager();
        \Pimcore\File::mkdir($this->backupPath);
        \Pimcore\File::mkdir($this->backupTmpPath);
    }

    // EXPORT ----------------------------------------------------------------------------------------------

    /**
     * Creates migration
     */
    public function exportContent()
    {
        $this->addMigrationKeys();
        $this->clearTmpDir();
        $this->dumpContent();

        // dump some data to jsons
//        file_put_contents($this->backupTmpPath . 'test.json', 'test-data');
//        file_put_contents($this->backupTmpPath . 'test2.json', 'test-data2');
//        file_put_contents($this->backupTmpPath . 'test3.json', 'test-data3');

        $this->finishExport();
    }

    /**
     * Zip all migration files
     */
    private function finishExport()
    {
        $zipFile = $this->backupPath . $this->dumpFileName;
        $zip = new \ZipArchive();
        $zip->open($zipFile, \ZIPARCHIVE::OVERWRITE);
        $zip->addGlob($this->backupTmpPath . '*', 0, array('add_path' => './', 'remove_all_path' => true));
        $zip->close();

        $this->clearTmpDir();
    }

    public function addMigrationKeys()
    {
        echo "\nCreating migration keys for all known content tables.\n";

        foreach($this->cts as $tn => $ct) {

            echo "table: $tn\n";

            $sql = "SELECT * FROM " . $tn;
            $docs = $this->db->fetchAssoc($sql);
            foreach ($docs as &$doc) {
                $id1 = $doc[$ct['idfield']];
                $id2 = $ct['idfield2'] ? $doc[$ct['idfield2']] : null;
                $id3 = $ct['idfield3'] ? $doc[$ct['idfield3']] : null;

                echo $tn . ' ' .
                    ' [' . $ct['idfield'] . '=' . $id1 . '] ' .
                    ' [' . $ct['idfield2'] . '=' . $id2 . '] ' .
                    ' [' . $ct['idfield3'] . '=' . $id3 . '] ';

//                echo " ($id1-$id2-$id3) ";

                // look up current migration setting by doc id
                $migration_object = \PimcoreDeployment\DeploymentDataMigrationManager::createKeyByCnameAndId($tn, $id1, $id2, $id3);

                echo $migration_object->getMigrationKey() . "\n";
            }
        }
    }

    public function dumpContent()
    {
        echo "\nDumping content structure to json:\n";

        $migslist = new DeploymentDataMigration\Listing();
        $migslist->setUnpublished(true);
        $migs = $migslist->getObjects();
        $startpoints = array();
        foreach($migs as $mig)
        {
            if($mig->getMode() != 'default') {
                $this->dumpContentPart($mig);
                $startpoints[] = DeploymentDataMigrationManager::DDMtoArray($mig);
            }
        }
        file_put_contents($this->backupTmpPath . 'startpoints.json', json_encode($startpoints));
    }


    public function dumpContentPart($mig)
    {
        echo $mig->getCName() . " " . $mig->getCId() . " " . $mig->getCId2() . " " . $mig->getCId3() . " " . $mig->getMode() . "\n";
        if($mig->getCName() == 'documents')
        {
            $this->dumpContentPartDocuments($mig);
        }
        elseif($mig->getCName() == 'documents_page')
        {
            $this->dumpContentPartDocumentsPage($mig);
        }
        elseif($mig->getCName() == 'documents_elements')
        {
            $this->dumpContentPartDocumentsElements($mig);
        }
        elseif($mig->getCName() == 'documents_link')
        {
            $this->dumpContentPartDocumentsLink($mig);
        }
        elseif($mig->getCName() == 'properties')
        {
            $this->dumpContentPartProperties($mig);
        }
    }

    public function dumpContentPartDocuments($mig)
    {
        $docdata = $this->ddmm->getDataByDDM($mig);
        $this->dumpDataToFile($mig, $docdata);

        // RELATED:
        // parent documents (parentId)
        if($docdata['parentId'] > 0)
        {
            $mig2 = DeploymentDataMigrationManager::retrieveObjectByCnameAndId('documents', $docdata['parentId']);
            $this->dumpContentPart($mig2);
        }

        // documents_page (type == page? --> use id)
        if($docdata['type'] == 'page')
        {
            $mig2 = DeploymentDataMigrationManager::retrieveObjectByCnameAndId('documents_page', $docdata['id']);
            $this->dumpContentPart($mig2);
        }

        // documents_elements (documentid)
        $sql = "SELECT * FROM documents_elements WHERE documentid = " . $docdata['id'];
        $doces = $this->db->fetchAssoc($sql);
        foreach ($doces as &$doce) {
            $mig2 = DeploymentDataMigrationManager::retrieveObjectByCnameAndId('documents_elements', $docdata['id'], $doce['name']);
            $this->dumpContentPart($mig2);
        }

        // documents_links (documentid)
        $sql = "SELECT * FROM documents_link WHERE id = " . $docdata['id'];
        $doces = $this->db->fetchAssoc($sql);
        foreach ($doces as &$doce) {
            $mig2 = DeploymentDataMigrationManager::retrieveObjectByCnameAndId('documents_link', $docdata['id'], $doce['name']);
            $this->dumpContentPart($mig2);
        }

        // properties
        $sql = "SELECT * FROM properties WHERE cid = " . $docdata['id'] . " AND ctype = 'document'";
        $props = $this->db->fetchAssoc($sql);
        foreach ($props as &$prop) {
            $mig2 = DeploymentDataMigrationManager::retrieveObjectByCnameAndId('properties', $docdata['id'], 'document', $prop['name']);
            $this->dumpContentPart($mig2);
        }

        // -----------------------------------------------
        // @TODO: complete the list of relations to export

    }

    public function dumpContentPartDocumentsPage($mig)
    {
        $docdata = $this->ddmm->getDataByDDM($mig);
        $this->dumpDataToFile($mig, $docdata);

        // contentMasterDocumentId relation:
        if($docdata['contentMasterDocumentId'])
        {
            $mig2 = DeploymentDataMigrationManager::retrieveObjectByCnameAndId('documents', $docdata['contentMasterDocumentId']);
            $this->dumpContentPart($mig2);
        }
    }

    public function dumpContentPartDocumentsElements($mig)
    {
        $docdata = $this->ddmm->getDataByDDM($mig);
        $this->dumpDataToFile($mig, $docdata);
    }

    public function dumpContentPartDocumentsLink($mig)
    {
        $docdata = $this->ddmm->getDataByDDM($mig);
        $this->dumpDataToFile($mig, $docdata);
    }

    public function dumpContentPartProperties($mig)
    {
        $docdata = $this->ddmm->getDataByDDM($mig);
        $this->dumpDataToFile($mig, $docdata);
    }


    // -=-=-=-=-=-=-=-=-=-=-=-

    public function dumpDataToFile($mig, $data)
    {
        /* @var $mig DeploymentDataMigration */

        $data = array(
            'migration' => DeploymentDataMigrationManager::DDMtoArray($mig),
            'data' => $data
        );
//        var_dump($data);
        $dumpfile = $this->backupTmpPath . $mig->getCName() . '-' . $mig->getMigrationKey() . '.mig.json';
        file_put_contents($dumpfile, json_encode($data));
    }

    // IMPORT --------------------------------------------------------------------------------------------------------

    public function importContent()
    {
        $this->clearTmpDir();

        $zipFile = $this->backupPath . $this->dumpFileName;

        $command = "unzip $zipFile -d " . $this->backupTmpPath;
        print "EXEC: $command \n";
        exec($command, $output, $return_var);

        $files = glob($this->backupTmpPath . '*.mig.json');
        echo "\nProcessing files:\n";
//        var_dump($files);

        $this->createImportFileMap($files);
        $this->importStartpoints();

        $this->clearTmpDir();
    }

    private function createImportFileMap($files)
    {
        $this->ifm = array();
        foreach($files as $file) {
//            var_dump($file);
            $fc = file_get_contents($file);
            $fi = json_decode($fc, true);
//            echo "\n-------------------------------------------------------\n";
//            var_dump($fi);
            echo $file . "\n";
            $fi['migration']['file'] = $file;
            $this->ifm[] = $fi['migration'];
        }
    }

    private function importStartPoints()
    {
        $spf = file_get_contents($this->backupTmpPath . 'startpoints.json');
        $sps = json_decode($spf, true);
        foreach($sps as $startpoint)
        {
//            var_dump($startpoint);
            $this->importContentPart($startpoint, $startpoint['Mode']);
        }
    }

    private function importContentPart($mig, $parentmode = false)
    {
        echo $mig['CName'] . " " . $mig['CId'] . " " . $mig['CId2'] . " " . $mig['CId3'] . " " . $mig['Mode'] . "\n";
        // locate the file
        // get the data
        $filedata = $this->getFileDataByMig($mig);

        var_dump($filedata);
//        die();

//array(2) {
//  'migration' =>
//  array(7) {
//    'CName' =>
//    string(9) "documents"
//    'CId' =>
//    string(2) "25"
//    'CId2' =>
//    NULL
//    'CId3' =>
//    NULL
//    'Timestamp' =>
//    string(10) "1448626217"
//    'MigrationKey' =>
//    string(32) "774244708565847585d2fe1.37041140"
//    'Mode' =>
//    string(10) "softinsert"
//  }
//  'data' =>
//  array(11) {
//            'id' =>
//    string(2) "25"
//    'parentId' =>
//    string(2) "21"
//    'type' =>
//    string(4) "page"
//    'key' =>
//    string(19) "request-information"
//    'path' =>
//    string(12) "/en/contact/"
//    'index' =>
//    string(1) "1"
//    'published' =>
//    string(1) "1"
//    'creationDate' =>
//    string(10) "1443776396"
//    'modificationDate' =>
//    string(10) "1444637005"
//    'userOwner' =>
//    string(1) "2"
//    'userModification' =>
//    string(1) "2"
//  }
//}


//        if($filedata['migration']['Mode'] == 'softinsert')
        if($parentmode == 'softinsert') // stop if it exists
        {
            // look if key already exists in database
            $ddmlist = new DeploymentDataMigration\Listing();
            $ddmlist->addConditionParam('MigrationKey = ?', $filedata['migration']['MigrationKey']);
            $ddm = $ddmlist->current();
            // insert if not exists (depending on mode)
            if(!$ddm) {
                echo "inserting\n";
                if($filedata['migration']['CName'] == 'documents')
                {
                    $this->importContentPartDocuments($filedata);
                }
            }
            else {
                echo "exists. skipping.\n";
            }
        }
    }

    private function importContentPartDocuments($filedata)
    {
        // insert into database
        

        // insert the migration key for this thing
//        DeploymentDataMigrationManager::setKeyByCnameAndId('documents', $new_id, null, null, $filedata['migration']['MigrationKey']);
    }




    private function getFileDataByMig($mig)
    {
        // look up file in $this->ifm
        foreach($this->ifm as &$i)
        {
//            var_dump($i);
//            var_dump($mig);
//            die('123');

            if($i['MigrationKey'] == $mig['MigrationKey'])
            {
                break;
            }
        }

        return $this->getFileDataByIFM($i);
    }

    private function getFileDataByIFM($ifm)
    {
        return json_decode(file_get_contents($ifm['file']), true);
    }





    // -------------- GENERAL ----------------------------------------------

    /**
     * Executes query
     * @param string $query
     */
    private function executeQuery($query)
    {
        print $query . "\n";
        try {
            $this->adapter->query($query);
        } catch (Exception $e) {
            print $e->getMessage() . "\n";
        }
    }

    private function clearTmpDir()
    {
        array_map('unlink', glob($this->backupTmpPath . '*'));
    }




}
