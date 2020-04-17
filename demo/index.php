<?php
require_once '../vendor/autoload.php';

use RobotWebHook\Client;
use RobotWebHook\Exceptions\RobotWebHookException;

$a = new Client();

try {
    $a->markdown([]);
} catch (RobotWebHookException $e) {
    var_dump('捕获异常',$e->getMessage(),$e->getCode());
}
