<?php
//引入类库
require_once '../vendor/autoload.php';

use RobotWebHook\Exceptions\RobotWebHookException;
use RobotWebHook\Service;

//设置配置参数
$config = [
    'web_hook_url' => 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=1f292d89-da72-4821-a12b-efbbadba2750',
    'client_drive' => 'EnterpriseWeChatClient',

//    'web_hook_url' => 'https://oapi.dingtalk.com/robot/send?access_token=dd54da0effd485b4974ee4fd332c973c72b4bba574e8e0eb21a3ef221733d459',
//    'client_drive' => 'DingTalkClient',
//    'secret'=>'SECbb573eaffce59f940d14b23e0e8219c6c172a536dfa9e89075668bb1489be638'
];
try {

    //创建客户端对象
    $Client = Service::getInstance($config)->getClient();
    try {
        throw new \Exception('测试用异常', 123456);
    } catch (\Exception $e) {
        //将异常信息格式化为 markdown 数据格式
        $exception = [
            'app_name'              => '应用名称',
            'env'                   => '当前环境',
            'code'                  => $e->getCode(),
            'message'               => $e->getMessage(),
            'file'                  => $e->getFile(),
            'line'                  => $e->getLine(),
            'mentioned_list'        => '',//根据名字@需要提醒的人 默认不提醒
            'mentioned_mobile_list' => '',//根据手机号@需要提醒的人 默认不提醒
        ];
        $data      = $Client->markdownExceptionFormat($exception);
        $res       = $Client->markdownSend($data);
        print_r($res);
    }
} catch (RobotWebHookException $e) {
    var_dump('捕获异常', $e->getMessage(), $e->getCode());
}
