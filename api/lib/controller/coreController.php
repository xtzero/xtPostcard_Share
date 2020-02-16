<?php
namespace core;
class coreController
{
    function __construct()
    {

    }

    /**
     * 参数验证方法
     * @param $paramStr
     * @param string $method
     * @return int
     * @throws \Exception
     */
    public function param($paramStr, $method = 'g')
    {
        #par1
        #par1
        #par1=123
        #par1>>int
        #par1=123>>int
        #int、string、float、array
        $paramArr = explode(',', $paramStr);
        $needButNone = array();
        $successCount = 0;
        foreach ($paramArr as $k => $v) {
            $_key = trim($v);
            if (strpos($_key, '*') !== false) {
                $valueKind = false;
                if (strpos($_key, '>>') !== false) {
                    $_keyAndValueAndKind = explode('>>', $_key);
                    $_keyAndValue = $_keyAndValueAndKind[0];
                    $valueKind = $_keyAndValueAndKind[1];
                } else {
                    $_keyAndValue = $_key;
                }
                $keyAndValue = explode('=', $_keyAndValue);
                $key = substr(trim($keyAndValue[0]), 1);
                $defaultValue = trim($keyAndValue[1]);

                if ($method == 'p') {
                    @$value = $_POST[$key];
                } else {
                    @$value = $_GET[$key];
                }

                if (isset($value)) {
                    switch ($valueKind) {
                        case 'int'      :
                            $value = (int)$value;
                            break;
                        case 'float'    :
                            $value = (float)$value;
                            break;
                        case 'string'   :
                            $value = (string)$value;
                            break;
                        case 'array'    :
                            $value = json_decode($value);
                            break;
                    }
                    $this->{$key} = trim($value);
                    $successCount++;
                } else if ($defaultValue) {
                    switch ($valueKind) {
                        case 'int'      :
                            $defaultValue = (int)$defaultValue;
                            break;
                        case 'float'    :
                            $defaultValue = (float)$defaultValue;
                            break;
                        case 'string'   :
                            $defaultValue = (string)$defaultValue;
                            break;
                        case 'array'    :
                            $defaultValue = json_decode($defaultValue);
                            break;
                    }

                    $this->{$key} = $defaultValue;
                    $successCount++;
                }
            } else {
                $valueKind = false;
                if (strpos($_key, '>>') !== false) {
                    $keyAndKind = explode('>>', $_key);
                    $key = $keyAndKind[0];
                    $valueKind = $keyAndKind[1];
                } else {
                    $key = $_key;
                }

                if ($method == 'p') {
                    @$value = $_POST[$key];
                } else {
                    @$value = $_GET[$key];
                }


                if (isset($value)) {
                    switch ($valueKind) {
                        case 'int'      :
                            $value = (int)$value;
                            break;
                        case 'float'    :
                            $value = (float)$value;
                            break;
                        case 'string'   :
                            $value = (string)$value;
                            break;
                        case 'array'    :
                            $value = json_decode($value);
                            break;
                    }
                    $this->{$key} = trim($value);
                    $successCount++;
                } else {
                    array_push($needButNone, $key);
                }
            }
        }

        if (!empty($needButNone)) {
            error('缺少参数：' . implode('、', $needButNone));
        } else {
            return $successCount;
        }
    }
}
