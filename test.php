<?php
/**
 * Created by PhpStorm.
 * User: boren
 * Date: 17/4/5
 * Time: 下午2:22
 */
require_once __DIR__ . '/mongodb.php';

$client = mongodb::getInstance();
$client->insert('test',array());