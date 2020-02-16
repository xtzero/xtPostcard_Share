<?php
namespace controller\index;

use core\coreController;
use lib\coreModel;
use lib\db;

class postcard extends coreController {

    private $m;
    const postcard_content = 'postcard_content';
    const postcard_code = 'postcard_verifycode';
    const sdkappid = '1400310523';
    const sdkappkey = '5fdbf07575749d4cba77e91d999a833d';

    public function __construct()
    {
        crossDomain();
        $this->m = new coreModel();
    }

    public function index() {
        ajax(200, '接口访问成功了！', []);
    }

    public function sendVeryfyCode() {
        $this->param('mobile');
        $time = time();
        $ifSended = $this->m->table(self::postcard_code)->mode('select')->where("mobile='{$this->mobile}' AND valid=1")->order('create_time DESC')->query();
        if (
            !empty($ifSended) && 
            (time() - (int)strtotime($ifSended[0]['create_time']) < 60)
        ) {
            ajax(400, '1分钟内只能获取一次', []);
        }

        // 开始发送短信
        $smsCode = rand(100000, 999999);
        $random = rand(100000, 999999);
        $url = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid='.self::sdkappid.'&random='.$random;
        $sig = hash('sha256', "appkey=".self::sdkappkey."&random={$random}&time={$time}&mobile={$this->mobile}");
        $smsData = [
            'params' => [
                $smsCode
            ],
            'sig' => $sig,
            'sign' => 'Zero的个人主页',
            'tel' => [
                'mobile' => $this->mobile,
                'nationcode' => '86'
            ],
            'time' => $time,
            'tpl_id' => 529607
        ];
        $send = json_decode(post($url, $smsData), true);
        if ($send['result'] !== 0) {
            ajax(400, 'smserr:'.$send['errmsg'], []);
        } else {
            $insert = db::init()->query("insert into postcard_verifycode(mobile, verifycode, create_time, valid) values('{$this->mobile}','{$smsCode}','".date('Y-m-d H:i:s')."', 1);");
            if ($insert) {
                ajax(200, 'suc', [
                    'send' => json_decode($send, true),
                    'smsdata' => $smsData
                ]);
            } else {
                ajax(400, 'dberr:'.db::init()->error(), []);
            }
        }
    }

    public function sendPostcard() {
        $this->param('mobile,verifycode,from_name,content,sendto');
        $time = time();
        // 验证验证码
        $verifyCode = $this->m->table(self::postcard_code)->mode('select')->where("mobile='{$this->mobile}' AND verifycode='{$this->verifycode}' AND valid=1")->order('create_time DESC')->query();
        if (empty($verifyCode)) {
            ajax(400, '验证码不正确', []);
        } else {
            // 将验证码失效
            db::init()->query("update ".self::postcard_code." set valid=0 where mobile='{$this->mobile}';");
            // 取件码
            $verifycode = rand(100000, 999999);
            // 发短信
            $random = rand(100000, 999999);
            $url = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid='.self::sdkappid.'&random='.$random;
            $sig = hash('sha256', "appkey=".self::sdkappkey."&random={$random}&time={$time}&mobile={$this->sendto}");
            $smsData = [
                'params' => [
                    $verifycode
                ],
                'sig' => $sig,
                'sign' => 'Zero的个人主页',
                'tel' => [
                    'mobile' => $this->sendto,
                    'nationcode' => '86'
                ],
                'time' => $time,
                'tpl_id' => 529609
            ];
            $send = json_decode(post($url, $smsData), true);
            if ($send['result'] !== 0) {
                ajax(400, 'smserr:'.$send['errmsg'], []);
            } else {
                try {
                    // 保存
                    $insert = db::init()->query("
                        insert into postcard_content(
                            content,
                            sendto,
                            verifycode,
                            `from`,
                            from_name,
                            is_del,
                            create_time
                        ) values(
                            '{$this->content}',
                            '{$this->sendto}',
                            '{$verifycode}',
                            '{$this->mobile}',
                            '{$this->from_name}',
                            0,
                            '".date('Y-m-d H:i:s')."'
                        );
                    ");
                    if ($insert) {
                        ajax(200, 'suc', [
                            'send' => json_decode($send, true),
                            'smsdata' => $smsData
                        ]);
                    } else {
                        ajax(400, 'dberr:'.db::init()->error(), []);
                    }
                } catch (\Exception $e) {
                    ajax(500, '发送验证码时出现问题,'.$e->getMessage().','.db::init()->error());
                }
            }
        }
    }

    public function readPostcard()
    {
        $this->param('verifycode');
        $postcard = $this->m->table(self::postcard_content)->mode('select')->where("verifycode='{$this->verifycode}'")->order('create_time DESC')->query();
        if (empty($postcard)) {
            ajax(500, '取件码错误');
        }
        db::init()->query('update '.self::postcard_content." set is_del=1 where verifycode='{$this->verifycode}';");

        ajax(200, '成功', $postcard);
    }
}
