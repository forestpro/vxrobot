<?php
/**
 * Created by PhpStorm.
 * User: boren
 * Date: 17/6/12
 * Time: 下午5:57
 */

class redisClient {

    static $instance;

    public function __construct()
    {

    }

    /**
     * 获取实例
     * @return Redis
     */
    public static function getInstance()
    {
        if(!static::$instance){
            static::$instance = new Redis();
            static::$instance->connect('127.0.0.1', '22100');
        }

        return static::$instance;
    }



}