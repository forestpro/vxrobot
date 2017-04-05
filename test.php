<?php
/**
 * Created by PhpStorm.
 * User: boren
 * Date: 17/4/5
 * Time: 下午2:22
 */
//require_once __DIR__ . '/mongodb.php';

//$client = mongodb::getInstance();
//$client->insert('test',array());
$m = new MongoClient('mongodb://192.168.1.17:30000/user?slaveOk=false');

$db = $m->user;

$coll = $db->xx;

$document = array( "title" => "Calvin and Hobbes", "author" => "Bill Watterson" );

$coll->insert($document);