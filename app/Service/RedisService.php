<?php


namespace App\Service;


class RedisService
{
    protected static $redis = [];

    /**
     * @param int $db
     * @return \Redis
     * @throws \Exception
     * @author 旗舰猫
     */
    public static function getInstance($db = 1) {
        if(!isset(self::$redis[$db])) {
            self::$redis[$db] = new \Redis();
        }
        try{
            if (!self::$redis[$db]->connect(env('REDIS_HOST')))
                throw new \Exception("连接缓存服务器失败");

            if (env('REDIS_PASSWORD'))
            {
                if (!self::$redis[$db]->auth(env('REDIS_PASSWORD')))
                    throw new \Exception("连接缓存服务器失败-");
            }


            if (!self::$redis[$db]->select($db))
                throw new \Exception("选择缓存数据库失败,".self::$redis[$db]->getLastError());

        }catch (\Exception $e){
            unset(self::$redis[$db]);
            throw new \Exception('redis error',500);
        }
        return self::$redis[$db];
    }

    public function __clone()
    {
        die('do not clone me ');
    }

}