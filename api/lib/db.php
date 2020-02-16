<?php
namespace lib;
class db{
    private $db_host;
    private $db_usr;
    private $db_pwd;

    private $dbConfFile = '/var/xtDbConf/mysql';
    private $db;
    private static $obj = null;

    private function  __construct(){
        if(file_exists($this->dbConfFile)) {
            $dbconf = json_decode(file_get_contents($this->dbConfFile), true);
            $this->db_host = $dbconf['dbHost'];
            $this->db_pwd = $dbconf['dbPwd'];
            $this->db_usr = $dbconf['dbUsr'];
        } else {
            error("数据库连接失败！请创建文件：{$this->dbConfFile}，并写入内容：".'{"dbUsr":"","dbHost":"","dbPwd":""}');
        }
        $db = mysqli_connect($this->db_host,$this->db_usr,$this->db_pwd);
        if($db){
            mysqli_select_db($db,'xt');
            mysqli_query($db,'set names \'utf8\'');
            $this->db = $db;

            return $this;
        }else{
            error('db connect error:'.mysqli_error($db));
        }
    }

    public static function init(){
        if(self::$obj === null){
            self::$obj = new self();
        }

        return self::$obj;
    }

    public function query($sql,$dbResultToArray = false){
        if($this->db){
            $res = mysqli_query($this->db,$sql);
            if($dbResultToArray){
                if($res){
                    $res = fetchDbResult($res);
                }else{
                    $res = [];
                }
            }

            return $res;
        }else{
            error('db instance not found!');
            die();
        }
    }

    public function error(){
        if($this->db){
            $res = mysqli_error($this->db);
            return $res;
        }else{
            error('db instance not found!');
            die();
        }
    }
}

function fetchDbResult($dbResult){
    $data = [];
    while($row = mysqli_fetch_assoc($dbResult)){
        $data[] = $row;
    }

    return $data;
}
