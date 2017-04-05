<?php
/**
 * Created by PhpStorm.
 * User: boren
 * Date: 17/4/5
 * Time: 下午2:12
 */
class mongodb{

    static $mongoURl = 'mongodb://192.168.1.17:30000/user?slaveOk=false';

    public static function insert($connName,$data)
    {
        $manager = new MongoDB\Driver\Manager(mongodb::mongoURl);

        $bulk = new MongoDB\Driver\BulkWrite;

        $bulk->insert(['x' => 1, 'name'=>'菜鸟教程', 'url' => 'http://www.runoob.com']);

        $manager->executeBulkWrite('test', $bulk);
    }

}