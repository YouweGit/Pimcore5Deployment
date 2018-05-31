<?php

namespace Pimcore5\DeploymentBundle\Config;


class Config
{

    private static $config;

    public static function get($var = null) {
        if($var === null) return self::$config;
        return self::$config[$var];
    }

    public static function set($config) {

//        echo "SETTING CONFIG";
//        var_dump($config);
//        die('CONFIG SETTTT');

        self::$config = $config;
    }


}