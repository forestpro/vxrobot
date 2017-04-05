<?php
/**
 * Created by PhpStorm.
 * User: boren
 * Date: 17/4/5
 * Time: 下午2:12
 */
class mongodb
{

    static $instance;

    protected $mongoURl = 'mongodb://zuihuiyou_user:zuihuiyou_2014@192.168.1.17:30000/user?slaveOk=false';

    protected $manager;

    public function __construct()
    {
        $this->manager = new MongoDB\Driver\Manager($this->mongoURl);
    }


    public static function getInstance()
    {
        if(!static::$instance){
            static::$instance = new mongodb();
        }

        return static::$instance;
    }

    public  function insert($connName,$data)
    {
        $bulk = new MongoDB\Driver\BulkWrite;

        $bulk->insert(['x' => 1, 'name'=>'mmmmxxx', 'url' => 'http://www.runoob.com']);

        $this->manager->executeBulkWrite('xx', $bulk);
    }

}