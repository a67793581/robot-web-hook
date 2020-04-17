<?php

namespace App\Jobs;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class BugJob extends Job
{
    protected $exception;

    protected $data = array();

    protected $company_wechat = array(
        'develop'    => 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=1f292d89-da72-4821-a12b-efbbadba2750',
        'production' => 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=7afa69ff-9dd5-4140-9624-c5260eb3e6f2',
    );

    public function __construct(array $exception, array $data = array())
    {
        $this->exception                     = array_to_object($exception);
        $this->exception->code               = $this->exception->code ?? '没有code';
        $this->exception->message            = $this->exception->message ?? '没有message';
        $this->exception->file               = $this->exception->file ?? '没有file';
        $this->exception->line               = $this->exception->line ?? '没有line';
        $this->data['env']                   = $data['env'] ?? 'develop';
        $this->data['msg_type']              = $data['msg_type'] ?? 'markdown';
        $this->data['app_name']              = '小程序';
        $this->data["mentioned_list"]        = $data["mentioned_list"] ?? [];
        $this->data["mentioned_mobile_list"] = $data["mentioned_mobile_list"] ?? [];

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {
            call_user_func_array(array(__CLASS__, $this->data['msg_type']), []);
        } catch (Exception $e) {
            Log::info('bug消息推送异常:', array(
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ));
        }
    }

    function text()
    {
        $data = array(
            "content" => "异常警告:\n" . print_r(array(
                    'app_name' => $this->data['app_name'],
                    'env'      => $this->data['env'],
                    'code'     => $this->exception->code,
                    'message'  => $this->exception->message,
                    'file'     => $this->exception->file,
                    'line'     => $this->exception->line,
                ), true)
        );
        $this->data["mentioned_list"] && $data["mentioned_list"] = $this->data["mentioned_list"];
        $this->data["mentioned_mobile_list"] && $data["mentioned_mobile_list"] = $this->data["mentioned_mobile_list"];
        $this->push(array(
            "msgtype" => "text",
            "text"    => $data
        ));
    }

    function markdown()
    {
        $this->push(array(
            'msgtype'  => 'markdown',
            'markdown' => array(
                'content' => "
### app_name:<font color='warning'>{$this->data['app_name']}</font>\n
>env:<font color='comment'>{$this->data['env']}</font>
>file:<font color='comment'>" . get_good_str($this->exception->file) . "</font>
>line:<font color='comment'>{$this->exception->line}</font>
>code:<font color='comment'>{$this->exception->code}</font>
>message:<font color='comment'>" . get_good_str($this->exception->message) . "</font>"
            )
        ));
    }

    function push($data)
    {

        if (isset($this->data['env']) && $this->data['env'] === 'production') {
            $uri = $this->company_wechat[$this->data['env']];
        } else {
            $uri = $this->company_wechat['develop'];
        }

        $client = new Client(array('timeout' => 30));
        $client->request(
            'POST',
            $uri,
            array(
                'json'    => $data,
                'headers' => array(
                    'Accept'       => '*/*',
                    'Content-Type' => 'application/json'
                ),
            )
        );
    }
}
