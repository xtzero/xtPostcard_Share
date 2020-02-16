<?php
namespace lib;

class redis
{
    private $host;
    private $port = '6379';
    private $password;
    private $redis;
    private $dbConfFile = '/var/xtDbConf/redis';
    private static $obj = null;
    private function __construct()
    {
        if(file_exists($this->dbConfFile)) {
            $dbconf = json_decode(file_get_contents($this->dbConfFile), true);
            $this->host = $dbconf['dbHost'];
            $this->port = $dbconf['dbPort'];
            $this->password = $dbconf['dbPwd'];
        } else {
            error("数据库连接失败！请创建文件：{$this->dbConfFile}，并写入内容：".'{"dbUsr":"","dbHost":"","dbPwd":"",""}');
        }

        $redis = new \Redis();
        $redis->connect($this->host, $this->port);
        $redis->auth($this->password);

        if($redis){
            $this->redis = $redis;
            return $redis;
        }else{
            error('redis connect error');
        }
    }

    public static function init(){
        if(self::$obj === null){
            return new self();
        }

        return self::$obj;
    }

    public function set($key, $value)
    {
        if ($this->redis) {
            return $this->redis->set($key, $value);
        } else {
            error('没有redis连接');
        }
    }

    public function setex($key, $value, $exTime = 0)
    {
        if ($this->redis) {
            return $this->redis->setex($key, $exTime, $value);
        } else {
            error('没有redis连接');
        }
    }

    public function get($key)
    {
        if ($this->redis) {
            return $this->redis->get($key);
        } else {
            error('没有redis连接');
        }
    }

    public function exec($cmd, $params)
    {
        if ($this->redis) {
             call_user_func_array([$this, $cmd],$params);
        } else {
            error('没有redis连接');
        }
    }
}
