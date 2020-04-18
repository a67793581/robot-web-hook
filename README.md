# RobotWebHook
- EN:This is a robot message push, supporting pin and enterprise wechat's robot web hook interface.
- ZH:这是一个机器人消息推送，支持企业微信的机器人 web hook 接口。
# Demo
```php
//引入类库
require_once 'vendor/autoload.php';

use RobotWebHook\Client;
use RobotWebHook\Exceptions\RobotWebHookException;
//设置配置参数
$config = [
    //机器人接受请求的url
    'web_hook_url'=> 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=1f292d89-da72-4821-a12b-efbbadba2750'
];
//创建客户端对象
$Client = new Client($config);

try {
    try {
        throw new \Exception('测试用异常');
    }catch (\Exception $e){
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
        $markdownExceptionFormat = $Client->markdownExceptionFormat($exception);
        $Client->markdownSend($markdownExceptionFormat);
    }
} catch (RobotWebHookException $e) {
    var_dump('捕获异常',$e->getMessage(),$e->getCode());
}

```