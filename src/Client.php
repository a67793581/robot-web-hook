<?php


namespace RobotWebHook;

use RobotWebHook\Exceptions\RobotWebHookException;
use RobotWebHook\Traits\common;

/**
 * Class Client
 * @package RobotWebHook
 * @deprecated 兼容v1.*版本
 */
class Client
{
    use common;

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
                    'app_name' => isset($data['app_name']) ? $data['app_name'] : '',
                    'env'      => isset($data['env']) ? $data['env'] : '',
                    'code'     => isset($data['code']) ? $data['code'] : '',
                    'message'  => isset($data['message']) ? $data['message'] : '',
                    'file'     => isset($data['file']) ? $data['file'] : '',
                    'line'     => isset($data['line']) ? $data['line'] : '',
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
     * @return array
     * @throws RobotWebHookException
     */
    function markdownSend(array $data)
    {
        return $this->send(array(
            'msgtype'  => 'markdown',
            'markdown' => $data
        ));
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
        return $res;
    }
}