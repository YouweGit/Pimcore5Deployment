<?php

namespace PimcoreDeployment;

use Pimcore\API\Plugin as PluginLib;
use Pimcore\Model\Object\ClassDefinition;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface {

    /**
     * @var Zend_Translate
     */
    protected static $_translate;

    public function init() {

        parent::init();

        $cnf = self::getConfig();
        self::setConfig($cnf);
    }

    public static function getConfig()
    {
        $customconfig_file = PIMCORE_CONFIGURATION_DIRECTORY . '/deploymentconfig.php';
        $defaultconfig_file = PIMCORE_PLUGINS_PATH . '/PimcoreDeployment/deploymentconfig.default.php';

        if(file_exists($customconfig_file))
        {
            // weird patch necessary for specific servers that return
            // an integer instead of an array from the "require" function
            // every X json calls ?!?!
            $stuff = false;
            while (!is_array($stuff)) {
                $stuff = (require $customconfig_file);
            }
            return new \Zend_Config($stuff, true);
        }

        return new \Zend_Config((require $defaultconfig_file), true);
    }

    public static function setConfig($config)
    {
//        var_dump($config);
        $customconfig_file = PIMCORE_CONFIGURATION_DIRECTORY . '/deploymentconfig.php';

        $writer = new \Zend_Config_Writer_Array();
        $writer->write($customconfig_file, $config);
        $conf = self::getConfig();
//        var_dump($conf);
//        die('asdf');
    }

    /**
     * @return string $statusMessage
     */
    public static function install()
    {
//        // check if the plugins table structure is already exported by the plugin itself
//        $def = new \PimcoreDeployment\Definition();
//        $plugin_data_table_file_exported = $def->path . 'class_DeploymentDataMigration.json';
//        if(file_exists($plugin_data_table_file_exported))
//        {
//            return self::getTranslate()->_('deployment_install_definition_import');
//        }
//
//        try {
//            $install = new \PimcoreDeployment\Plugin\Install();
//            $install->createClass('DeploymentDataMigration');
//        } catch(\Exception $e) {
//            \logger::crit($e);
//            return self::getTranslate()->_('deployment_install_failed');
//        }
//
//        return self::getTranslate()->_('deployment_installed_successfully');
        return 'ok';
    }

    /**
     * @return string $statusMessage
     */
    public static function uninstall()
    {
//        try {
//            $install = new \PimcoreDeployment\Plugin\Install();
//            $install->removeClass('DeploymentDataMigration');
//
//            return self::getTranslate()->_('deployment_uninstalled_successfully');
//        } catch (\Exception $e) {
//            \Logger::crit($e);
//            return self::getTranslate()->_('deployment_uninstall_failed');
//        }
        return 'plugin is always installed';
    }

    /**
     * @return boolean $isInstalled
     */
    public static function isInstalled()
    {
//        $entry = Classdefinition::getByName('DeploymentDataMigration');
//        if ($entry) {
//            return true;
//        }
//
//        return false;
        return true;
    }

    /**
     * @return string
     */
    public static function getTranslationFileDirectory()
    {
        return PIMCORE_PLUGINS_PATH . '/PimcoreDeployment/static/texts';
    }

    /**
     * @param string $language
     * @return string path to the translation file relative to plugin direcory
     */
    public static function getTranslationFile($language)
    {
        if (is_file(self::getTranslationFileDirectory() . "/$language.csv")) {
            return "/PimcoreDeployment/static/texts/$language.csv";
        } else {
            return '/PimcoreDeployment/static/texts/en.csv';
        }
    }

    /**
     * @return Zend_Translate
     */
    public static function getTranslate()
    {
        if(self::$_translate instanceof \Zend_Translate) {
            return self::$_translate;
        }

        try {
            $lang = \Zend_Registry::get('Zend_Locale')->getLanguage();
        } catch (\Exception $e) {
            $lang = 'en';
        }

        self::$_translate = new \Zend_Translate(
            'csv',
            PIMCORE_PLUGINS_PATH . self::getTranslationFile($lang),
            $lang,
            array('delimiter' => ',')
        );
        return self::$_translate;
    }

}
