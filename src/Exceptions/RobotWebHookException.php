<?php


namespace RobotWebHook\Exceptions;

use Exception;
use Throwable;

class RobotWebHookException extends Exception
{
    private $data;

    public function __construct($message = "", $code = 0, Throwable $previous = null, $data = null)
    {
        parent::__construct($message, $code, $previous);

        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
