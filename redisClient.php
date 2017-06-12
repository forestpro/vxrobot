<?php
/**
 * Created by PhpStorm.
 * User: boren
 * Date: 17/6/12
 * Time: 下午5:57
 */

class redisClient {

    public $instance;

    public function __construct()
    {

    }

    /**
     * 获取实例
     * @return Redis
     */
    public static function getInstance()
    {
        $instance = new Redis();
        $instance->connect('127.0.0.1', '22100');

        return $instance;
    }



}