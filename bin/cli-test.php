<?php

include(__DIR__ . "/../pimcore/config/startup_cli.php");

use Pimcore\Model\DataObject;

// create some random objects ;-)
for ($i = 0; $i < 60; $i++) {
//    $o = new DataObject\News();
//    $o->setKey(uniqid() . "-" . $i);
//    $o->setParentId(1);
//    $o->setPublished(true);
//    $o->save();
    echo("Have not created object " . $i . "\n");
}


