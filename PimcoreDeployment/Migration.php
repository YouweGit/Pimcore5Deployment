<?php

namespace Pimcore5\DeploymentBundle\PimcoreDeployment;

class Migration extends DAbstract
{
    /**
     * @var string
     */
    private $backupPath = PIMCORE_PRIVATE_VAR . '/bundles/PimcoreDeployment/migration/';
    private $backupPathDumpComplete = PIMCORE_PRIVATE_VAR . '/bundles/PimcoreDeployment/dumps/';
    /**
     * @var string
     */
    private $dumpFileName = 'staticdata.zip';
    private $dumpCompleteFileName = 'database.sql';
    /**
     * @var Zend_Config
     */
    public $config;

    /**
     * Tables to copy data
     * @var array
     *
     * DEFAULT NOTHING - because data could get overwritten
     */
    private $staticDataTables = array(
    );

    /**
     * Contains migration sql queries
     * @var string
     */
    private $migrationSqlFile = 'migration.sql';

    public function __construct()
    {
        parent::__construct();
        $this->staticDataTables = array_unique($this->config['staticDataTables']);


        \Pimcore\File::mkdir($this->backupPath);
    }

    /**
     * Creates migration
     */
    public function create()
    {
        $this->dumpTables();
        $this->finish();
    }

    /**
     * Zip all migration files
     */
    private function finish()
    {
        $fullPathMigrationFile = $this->backupPath . $this->migrationSqlFile;
        if(is_file($fullPathMigrationFile)){
            $zipFile = $this->backupPath . $this->dumpFileName;
            $zip = new \ZipArchive();
            $zip->open($zipFile, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);
            $zip->addFile($fullPathMigrationFile, $this->migrationSqlFile);
            $zip->close();
            @unlink($fullPathMigrationFile);
        }
    }

    /**
     * Creates a (tables) dump file
     * @throws Zend_Exception
     */
    public function dumpTables()
    {
        $cnf = \Pimcore\Config::getSystemConfig();
        $return_var = NULL;
        $output = NULL;
        $u = $cnf->database->params->username;
        $p = $cnf->database->params->password;
        $db = $cnf->database->params->dbname;
        $h = $cnf->database->params->host;
        $file = $this->backupPath . $this->migrationSqlFile;

        $purged = '';
        if (true) {
            $purged = '--set-gtid-purged=OFF';
        }

        var_dump($this->staticDataTables);

        $tables = implode(' ', $this->staticDataTables);
        if(count($this->staticDataTables) > 0) {
            $command = "mysqldump $purged --add-drop-table -u$u -p$p -h$h $db $tables | sed -e '/DEFINER/d' > $file";
        }
        else {
            // REMOVE existing file!!!
            $command = "cp /dev/null $file";
        }

        exec($command, $output, $return_var);
    }

    /**
     * Migrate migration
     * @throws Exception
     * @throws Zend_Exception
     */
    public function migrate()
    {
        $cnf = \Pimcore\Config::getSystemConfig();

        $u = $cnf->database->params->username;
        $p = $cnf->database->params->password;
        $db = $cnf->database->params->dbname;
        $h = $cnf->database->params->host;

        $zipFile = $this->backupPath . $this->dumpFileName;

        $command = "unzip -p $zipFile | mysql -u$u -p$p -h$h $db";
        print "EXEC: $command \n";
        exec($command, $output, $return_var);
    }


    /**
     * Creates a (tables) dump file
     * @throws Zend_Exception
     */
    public function exportDump()
    {
        \Pimcore\File::mkdir($this->backupPathDumpComplete);

        $cnf = \Pimcore\Config::getSystemConfig();
        $return_var = NULL;
        $output = NULL;
        $u = $cnf->database->params->username;
        $p = $cnf->database->params->password;
        $db = $cnf->database->params->dbname;
        $h = $cnf->database->params->host;
        $file = $this->backupPathDumpComplete . $this->dumpCompleteFileName;
        $mysqlVersion = $this->adapter->getServerVersion();
        $purged = '';
        if (version_compare($mysqlVersion, '5.6.0', '>=')) {
            $purged = '--set-gtid-purged=OFF';
        }
        $tables = '';

        // ----- from the original script (might be useful) -------
        // export LC_CTYPE=C
        // export LANG=C
        // mysqldump -h${DB_LOCAL_HOST}  -u ${DB_LOCAL_USER} -p${DB_LOCAL_PASSWORD} ${DB_LOCAL_NAME} | sed '/\*\!50013 DEFINER/d' > ${CURRENT_PATH}/dump.sql
        // --------------------------------------------------------

        $command = "mysqldump $purged --add-drop-table -u$u -p$p -h$h $db $tables | sed -e '/DEFINER/d' > $file";

        $fullPathMigrationFile = $this->backupPathDumpComplete . $this->dumpCompleteFileName;

        exec($command, $output, $return_var);

        if(is_file($fullPathMigrationFile)) {
            $zipFile = $fullPathMigrationFile . '.zip';
            $zip = new \ZipArchive();
            $zip->open($zipFile, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);
            $zip->addFile($fullPathMigrationFile, $fullPathMigrationFile);
            $zip->close();
            @unlink($fullPathMigrationFile);
        }

        print "EXEC: $command \n";

    }

    /**
     * Migrate migration
     * @throws Exception
     * @throws Zend_Exception
     */
    public function importDump()
    {
        $cnf = \Pimcore\Config::getSystemConfig();

        $u = $cnf->database->params->username;
        $p = $cnf->database->params->password;
        $db = $cnf->database->params->dbname;
        $h = $cnf->database->params->host;

        $zipFile = $this->backupPathDumpComplete . $this->dumpCompleteFileName . '.zip';

        $command = "unzip -p $zipFile | mysql -u$u -p$p -h$h $db";
        print "EXEC: $command \n";
        exec($command, $output, $return_var);
    }


}




