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
$manager = new MongoDB\Driver\Manager('mongodb://zuihuiyou_user:zuihuiyou_2014@192.168.1.17:30000/user');
//$manager->authenticate("zuihuiyou_user","zuihuiyou_2014");
$bulk = new MongoDB\Driver\BulkWrite;
$bulk->insert(['x' => 1, 'name'=>'菜鸟教程', 'url' => 'http://www.runoob.com']);
$manager->executeBulkWrite('user.xx', $bulk);