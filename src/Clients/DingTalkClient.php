<?php


namespace RobotWebHook\Clients;


use RobotWebHook\Exceptions\RobotWebHookException;

class DingTalkClient
{
    private $config = array();

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $data
     * @return array
     */
    function textExceptionFormat(array $data)
    {

        $send_data = array(
            "content" => "异常警告:\n" . print_r(array(
                    'app_name' => isset($data['app_name']) ?$data['app_name']: '',
                    'env'      => isset($data['env']) ?$data['env']: '',
                    'code'     => isset($data['code']) ?$data['code']: '',
                    'message'  => isset($data['message']) ?$data['message']: '',
                    'file'     => isset($data['file']) ?$data['file']: '',
                    'line'     => isset($data['line']) ?$data['line']: '',
                ), true)
        );
        return $send_data;
    }

    /**
     * @param array $data
     * @return array
     */
    function markdownExceptionFormat(array $data)
    {
        $send_data = [
            'text' => <<<MARKDOWN
# app_name:{$data['app_name']}
- env:{$data['env']}
- file:{$this->getFormatString($data['file'])}
- line:{$data['line']}
- code:{$data['code']}
- message:{$this->getFormatString($data['message'])}
MARKDOWN
        ];

        $send_data['title'] = isset($data['title']) ? $data["title"] : '测试';
        return $send_data;
    }

    /**
     * @param array $data
     * @return array
     * @throws RobotWebHookException
     */
    function textSend(array $data)
    {
        return $this->send(array(
            "msgtype" => "text",
            "text"    => $data
        ));
    }

    /**
     * @param array $data
     * @param array $atMobiles
     * @param bool $is_at_all
     * @return array
     * @throws RobotWebHookException
     */
    function markdownSend(array $data, array $atMobiles = [], $is_at_all = false)
    {
        $send_data = array(
            'msgtype'  => 'markdown',
            'markdown' => $data
        );
        $atMobiles && $send_data['at']["atMobiles"] = $atMobiles;
        $is_at_all && $send_data['at']["isAtAll"] = $is_at_all;
        return $this->send($send_data);
    }

    /**
     * @param array $data
     * @return array
     * @throws RobotWebHookException
     */
    public function send(array $data)
    {
        if (!$this->config['web_hook_url']) {
            throw new RobotWebHookException('your web hook url', 100001);
        }
        $res = $this->httpPostJson($this->config['web_hook_url'], json_encode($data));
        if ($res['httpCode'] != 200) {
            throw new RobotWebHookException($res['body'], $res['httpCode']);
        }
        $body = json_decode($res['body'],true);
        if($body['errcode']){
            throw new RobotWebHookException($res['body'], $res['httpCode']);
        }
        return $res;
    }

    function httpPostJson($url, $jsonStr)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//绕过ssl验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return array('url' => $url, 'jsonStr' => $jsonStr, 'httpCode' => $httpCode, 'body' => $response);
    }

    /**
     * 获取字符过滤 用反斜线转义字符串
     *
     * @param string $str
     * @return string
     * @author carlo<284474102@qq.com>
     */
    function getFormatString($str)
    {
        if (empty($str)) {
            return null;
        }

        if (is_numeric($str)) {
            $str = trim($str);

            return $str;
        }

        if (is_string($str)) {
            if (!is_null(json_decode($str))) {
                return $str;
            }

            return addslashes(strip_tags(trim($str)));
        }

        return $str;
    }
}