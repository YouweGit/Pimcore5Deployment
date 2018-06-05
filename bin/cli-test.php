<?php

use Pimcore5\DeploymentBundle\Config\Config;

require_once __DIR__ . '/bootstrap.php';

//use Pimcore\Model\DataObject;

//// create some random objects ;-)
//for ($i = 0; $i < 60; $i++) {
////    $o = new DataObject\News();
////    $o->setKey(uniqid() . "-" . $i);
////    $o->setParentId(1);
////    $o->setPublished(true);
////    $o->save();
//    echo("Have not created object " . $i . "\n");
//}
//


echo ' == Config test: == ' . Config::get('url') . ' == ';


