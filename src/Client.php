<?php


namespace RobotWebHook;


use RobotWebHook\Exceptions\RobotWebHookException;

class Client
{
    private $config = array();

    public function __construct($config = array())
    {
        $default_config = include_once(__DIR__ . '/Config/config.php');
        $this->config   = array_merge($default_config, $config);
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
                    'app_name' => isset($data['app_name']) ?: '',
                    'env'      => isset($data['env']) ?: '',
                    'code'     => isset($data['code']) ?: '',
                    'message'  => isset($data['message']) ?: '',
                    'file'     => isset($data['file']) ?: '',
                    'line'     => isset($data['line']) ?: '',
                ), true)
        );
        isset($data['mentioned_list']) && $send_data["mentioned_list"] = $data["mentioned_list"];
        isset($data['mentioned_mobile_list']) && $send_data["mentioned_mobile_list"] = $data["mentioned_mobile_list"];
        return $send_data;
    }

    /**
     * @param array $data
     * @return array
     */
    function markdownExceptionFormat(array $data)
    {
        $send_data = [
            'content' => <<<MARKDOWN
### app_name:<font color='warning'>{$data['app_name']}</font>\n
>env:<font color='comment'>{$data['env']}</font>
>file:<font color='comment'>{$this->getFormatString($data['file'])}</font>
>line:<font color='comment'>{$data['line']}</font>
>code:<font color='comment'>{$data['code']}</font>
>message:<font color='comment'>{$this->getFormatString($data['message'])}</font>
MARKDOWN
        ];
        isset($data['mentioned_list']) && $send_data["mentioned_list"] = $data["mentioned_list"];
        isset($data['mentioned_mobile_list']) && $send_data["mentioned_mobile_list"] = $data["mentioned_mobile_list"];
        return $send_data;
    }

    /**
     * @param array $data
     * @throws RobotWebHookException
     */
    function textSend(array $data)
    {
        $this->send(array(
            "msgtype" => "text",
            "text"    => $data
        ));
    }

    /**
     * @param array $data
     * @throws RobotWebHookException
     */
    function markdownSend(array $data)
    {
        $this->send(array(
            'msgtype'  => 'markdown',
            'markdown' => $data
        ));
    }
    /**
     * @param array $data
     * @throws RobotWebHookException
     */
    public function send(array $data)
    {
        if(!$this->config['web_hook_url']){
            throw new RobotWebHookException('your web hook url', 100001);
        }
        $res = $this->httpPostJson($this->config['web_hook_url'], json_encode($data));
        if ($res['httpCode'] != 200) {
            throw new RobotWebHookException($res['body'], $res['httpCode']);
        }
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