<?php
/**
 * 跨域访问开启
 */
function crossDomain(){
    header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Headers:x-requested-with,content-type');
    header("Content-type: text/html; charset=utf-8");
}

/**
 * 接口返回数据
 */
function ajax($code,$msg,$data = []){
    echo json_encode([
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ]);
    die();
}

/**
 * 对外开放的显示异常信息
 */
function error($info){
    throw new Exception('<h1>'.$info.'</h1><br/><p>'.$_SERVER['PHP_SELF'].' <p><hr/>');
    die();
}

/**
 * 显示异常信息
 */
function displayException($e){
    //异常处理
    echo $e->getMessage();
    $trace = $e->getTrace();
    foreach($trace as $k => $v){
        echo 'In file: '.$v['file'].',line '.$v['line'].'<br/>';
        echo 'Error function: '.$v['function'].',error info: <br/>';
        if($v['args']){
            foreach($v['args'] as $kk => $vv){
                echo $kk.'.'.$vv.'<br/>';
            }
        }else{
            echo '[none]';
        }
        
        echo '<br/><hr/>';
    }
}

/**
 * 二维数组下某个键变成数组索引
 */
function keyToIndex($array,$keyName){
    $array_ = false;
    foreach($array as $k => $v){
        $array_[$v[$keyName]] = $v;
    }

    return $array_;
}

/**
 * 生成userid
 * @param $password
 * @return string
 */
function appendUserid($password = '')
{
    return md5(md5(implode(',', [
        $password,
        rand(1111,9999),
        time()
    ])));
}

function post($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen(json_encode($data))
        )
    );
    // POST数据
    curl_setopt($ch, CURLOPT_POST, 1);
    // 把post的变量加上
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}