<?php


namespace RobotWebHook\Traits;


trait common
{
    /**
     * 返回当前的毫秒时间戳
     * @return float
     */
    public function get_millisecond()
    {
        list($millisecond, $second) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($millisecond) + floatval($second)) * 1000);
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

    /**
     * 将编码转为UTF-8
     * @param $str
     * @return string
     */
    function transcoding_utf8($str)
    {
        //转码
        $encoding = mb_detect_encoding($str, array("ASCII", "UTF-8", "GB2312", "GBK", "BIG5"));
        if ($encoding != 'UTF-8') {
            $str = mb_convert_encoding($str, 'utf-8', $encoding);
        }
        return $str;
    }

    /**
     * 发送post json请求
     * @param $url
     * @param $jsonStr
     * @return array
     */
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
}