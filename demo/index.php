<?php
require_once '../vendor/autoload.php';

use \RobotWebHook\Client;

$a = new Client();
print_r($a->getConfig());
