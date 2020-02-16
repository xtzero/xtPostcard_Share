<?php

error_reporting(E_ERROR);
/**
 * 分析路径，启动程序
 */

if (!isset($_SERVER['PATH_INFO'])) {
    $uri = 'index';
} else {
    $uri = substr($_SERVER['PATH_INFO'], 0, 1) == '/' ? substr($_SERVER['PATH_INFO'], 1) : $_SERVER['PATH_INFO'];
}

if ($router[$uri]) {
    $routerPath = $router[$uri];
} else {
    error('找不到路由');
}
$routerPath = substr($routerPath, 0, 1) == '/' ? substr($routerPath, 1) : $routerPath;
$pathInfo = explode('/', $routerPath);
if (count($pathInfo) > 3) {
    error('路径超过3个参数！');
}
$module = $pathInfo[0]?$pathInfo[0]:'index';
$controller = $pathInfo[1]?$pathInfo[1]:'index';
$function = $pathInfo[2]?$pathInfo[2]:'index';

//尝试启动
try{
    $r = require_once 'controller/'.$module.'/'.$controller.'.php';
    $className = '\\controller\\'.$module.'\\'.$controller;
    $class = new $className();
    call_user_func_array([$class, $function],[]);
}catch(Exception $e){
    displayException($e);
}
