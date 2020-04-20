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
    public function send(array $data)
    {
        if (!$this->config['web_hook_url']) {
            throw new RobotWebHookException('your web hook url', 100001);
        }
        $url     = $this->config['web_hook_url'];
        $jsonStr = json_encode($data);
        if ($this->config['secret']) {
            //    $t = time() * 1000;
            //    $ts = $t."\n".$webhook->ding_key;
            //    $sig = hash_hmac('sha256', $ts, $webhook->ding_key,true);
            //    $sig = base64_encode($sig);
            //    $sig = urlencode($sig);
            //    $webhook->url = $webhook->url."&timestamp=".$t."&sign=".$sig;
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
            throw new RobotWebHookException($res['body'], $res['httpCode']);
        }
        $body = json_decode($res['body'], true);
        if ($body['errcode']) {
            throw new RobotWebHookException($res['body'], $res['httpCode']);
        }
        return $res;
    }


}