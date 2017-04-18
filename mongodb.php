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

    /**
     * 获取实例
     * @return MongoCollection
     */
    public static function getInstance()
    {
        if(!static::$instance){
            static::$instance = new mongodb();
        }

        return static::$instance;
    }

    /**
     * 插入数据
     * @param $connName 集合名称
     * @param $data 数据
     */
    public  function insert($connName,$data)
    {
        $bulk = new MongoDB\Driver\BulkWrite;

        $bulk->insert($data);

        $this->manager->executeBulkWrite('robot.'.$connName, $bulk);
    }

    /**
     * 查询数据
     * @param $connName 集合名称
     * @param $filter 查询条件
     * @param $options 操作
     */
    public function Query($connName,$filter,$options)
    {
        $query = new MongoDB\Driver\Query($filter,$options);

        $cursor = $this->manager->executeQuery('robot.'.$connName,$query);

        $result = array();

        foreach($cursor as $doc)
        {
            array_push($result,$doc);
        }

        return $result;

    }

}