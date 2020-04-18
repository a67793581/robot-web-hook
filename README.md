# RobotWebHook
- EN:This is a robot message push, supporting pin and enterprise wechat's robot web hook interface.
- ZH:这是一个机器人消息推送，支持企业微信和钉钉的机器人 web hook 接口。
# Demo
```php
<?php
//引入类库
require_once '../vendor/autoload.php';

use RobotWebHook\Exceptions\RobotWebHookException;
use RobotWebHook\Service;

//设置配置参数
$config = [
    //机器人接受请求的url
    'web_hook_url' => '',//你的地址
    'client_drive' => 'DingTalkClient',//客户端驱动类型

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
        $data      = $Client->textExceptionFormat($exception);
        $res       = $Client->textSend($data);
        print_r($res);
    }
} catch (RobotWebHookException $e) {
    var_dump('捕获异常', $e->getMessage(), $e->getCode());
}

```