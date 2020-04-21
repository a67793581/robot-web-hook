<?php


namespace RobotWebHook\Clients;


use RobotWebHook\Exceptions\RobotWebHookException;
use RobotWebHook\Traits\common;

class EnterpriseWeChatClient
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
    function markdownExceptionFormat(array $data)
    {
        foreach ($data as $k => $v) {
            if (!is_string($v)) {
                throw new RobotWebHookException('Must be a 1D array', 200002);
            }
            $data[$k] = $this->getFormatString($v);
        }
        $app_name  = isset($data['app_name']) ? $data['app_name'] : '';
        $env       = isset($data['env']) ? $data['env'] : '';
        $file      = isset($data['file']) ? $data['file'] : '';
        $line      = isset($data['line']) ? $data['line'] : '';
        $code      = isset($data['code']) ? $data['code'] : '';
        $message   = isset($data['message']) ? $data['message'] : '';
        $send_data = array(
            'content' => sprintf("### app_name:<font color='warning'>%s</font>\n
>env:<font color='comment'>%s</font>
>file:<font color='comment'>%s</font>
>line:<font color='comment'>%s</font>
>code:<font color='comment'>%s</font>
>message:<font color='comment'>%s</font>", $app_name, $env, $file, $line, $code, $message)
        );
        return $send_data;
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
     * @param string $filename
     * @return array
     */
    function imageFormat($filename)
    {
        $file      = file_get_contents($filename);
        $send_data = array(
            'base64' => base64_encode($file),
            'md5'    => md5($file),
        );
        return $send_data;
    }

    /**
     * @param $data
     * @return array
     * @throws RobotWebHookException
     */
    function imageSend(array $data)
    {
        return $this->send(array(
            'msgtype' => 'image',
            'image'   => $data,
        ));
    }

    /**
     * @param array $data
     * @return array
     * @throws RobotWebHookException
     */
    function newsFormat(array $data)
    {
        $send_data = array();
        foreach ($data as $k => $v) {
            if (!is_array($v)) {
                throw new RobotWebHookException('Must be a 2D array', 200001);
            }
            if ($k > 7) {
                break;
            }
            $send_data[] = [
                'title'       => isset($v['title']) ? $v['title'] : '这是一个机器人消息推送',
                'description' => isset($v['description']) ? $v['description'] : '',
                'url'         => isset($v['url']) ? $v['url'] : 'https://github.com/a67793581/robot-web-hook',
                'picurl'      => isset($v['pic_url']) ? $v['pic_url'] : '',
            ];
        }
        return $send_data;
    }

    /**
     * @param $data
     * @return array
     * @throws RobotWebHookException
     */
    function newsSend(array $data)
    {
        return $this->send(array(
            'msgtype' => 'news',
            'news'    => array('articles' => $data),
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