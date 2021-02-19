<?php


namespace RobotWebHook\Clients;


use RobotWebHook\Exceptions\RobotWebHookException;
use RobotWebHook\Traits\common;

class DingTalkClient
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
        return $send_data;
    }

    /**
     * @param array $data
     * @param array $at_mobiles
     * @param bool $is_at_all
     * @return array
     * @throws RobotWebHookException
     */
    function textSend(array $data, array $at_mobiles = [], $is_at_all = false)
    {
        $send_data = array(
            'msgtype' => 'text',
            'text'    => $data
        );
        $at_mobiles && $send_data['at']["atMobiles"] = $at_mobiles;
        $is_at_all && $send_data['at']["isAtAll"] = $is_at_all;
        return $this->send($send_data);
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
     * @param array $at_mobiles
     * @param bool $is_at_all
     * @return array
     * @throws RobotWebHookException
     */
    function markdownSend(array $data, array $at_mobiles = [], $is_at_all = false)
    {
        $send_data = array(
            'msgtype'  => 'markdown',
            'markdown' => $data
        );
        $at_mobiles && $send_data['at']["atMobiles"] = $at_mobiles;
        $is_at_all && $send_data['at']["isAtAll"] = $is_at_all;
        return $this->send($send_data);
    }

    /**
     * @param array $data
     * @return array
     * @throws RobotWebHookException
     */
    function feedCardFormat(array $data)
    {
        $send_data = array();
        foreach ($data as $k => $v) {
            if (!is_array($v)) {
                throw new RobotWebHookException('Must be a 2D array', 200001);
            }
            $send_data[] = [
                'title'      => isset($v['title']) ? $v['title'] : '这是一个机器人消息推送',
                'messageURL' => isset($v['url']) ? $v['url'] : 'https://github.com/a67793581/robot-web-hook',
                'picURL'     => isset($v['pic_url']) ? $v['pic_url'] : '',
            ];
        }
        return $send_data;
    }

    /**
     * @param $data
     * @return array
     * @throws RobotWebHookException
     */
    function feedCardSend(array $data)
    {
        return $this->send(array(
            'msgtype'  => 'feedCard',
            'feedCard' => array('articles' => $data),
        ));
    }

    /**
     * @param array $data
     * @return array
     */
    function linkFormat(array $data)
    {
        $send_data = array(
            'title'      => isset($data['title']) ? $data['title'] : '这是一个机器人消息推送',
            'text'       => isset($data['description']) ? $data['description'] : '',
            'messageUrl' => isset($data['url']) ? $data['url'] : 'https://github.com/a67793581/robot-web-hook',
            'picurl'     => isset($data['pic_url']) ? $data['pic_url'] : '',
        );
        return $send_data;
    }

    /**
     * @param array $data
     * @return array
     * @throws RobotWebHookException
     */
    function linkSend(array $data)
    {
        $send_data = array(
            'msgtype' => 'link',
            'link'    => $data
        );
        return $this->send($send_data);
    }

    /**
     * @param array $data
     * @param array $buttons
     * @return array
     * @throws RobotWebHookException
     */
    function actionCardFormat(array $data, array $buttons)
    {
        $send_data = array(
            'title'          => isset($data['title']) ? $data['title'] : '这是一个机器人消息推送',
            'text'           => isset($data['description']) ? $data['description'] : '',
            'btnOrientation' => isset($data['orientation']) ? $data['orientation'] : '0',
        );
        $count     = count($buttons);
        foreach ($buttons as $k => $v) {
            if (!is_array($v)) {
                throw new RobotWebHookException('Must be a 2D array', 200001);
            }
            if ($count > 1) {
                $send_data['btns'][] = [
                    'title'     => isset($v['title']) ? $v['title'] : '这是一个机器人消息推送',
                    'actionURL' => isset($v['url']) ? $v['url'] : 'https://github.com/a67793581/robot-web-hook',
                ];
            } else {
                $send_data['singleTitle'] = isset($v['title']) ? $v['title'] : '这是一个机器人消息推送';
                $send_data['singleURL']   = isset($v['url']) ? $v['url'] : '这是一个机器人消息推送';
            }
        }
        return $send_data;
    }

    /**
     * @param array $data
     * @return array
     * @throws RobotWebHookException
     */
    function actionCardSend(array $data)
    {
        $send_data = array(
            'msgtype'    => 'actionCard',
            'actionCard' => $data
        );
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
        $url     = $this->config['web_hook_url'];
        $jsonStr = json_encode($data);
        if ($this->config['secret']) {
            $secret = $this->config['secret'];
            $secret = $this->transcoding_utf8($secret);
            //获取当前毫秒
            $timestamp = $this->get_millisecond();
            //组装数据
            $ts = $timestamp . "\n" . $secret;
            $ts = $this->transcoding_utf8($ts);
            //签名
            $sign = urlencode(base64_encode(hash_hmac('sha256', $ts, $secret, true)));
            //组装url
            $url .= "&timestamp={$timestamp}&sign={$sign}";
        }
        $res = $this->httpPostJson($url, $jsonStr);
        if ($res['httpCode'] != 200) {
            throw new RobotWebHookException(json_encode($res), 100002);
        }
        $body = json_decode($res['body'], true);
        if ($body['errcode']) {
            throw new RobotWebHookException(json_encode($res), 100003);
        }
        return $res;
    }


}
