<?php
/**
 * Created by PhpStorm.
 * User: monday41
 * Date: 2018/3/14
 * Time: 下午8:27
 */

require_once __DIR__ . '/../autoload.php';

use \MT\API\TokenModel;

$tokenModel    = new TokenModel('gopax');

$data           = [
    'symbol'        => 'usdt',
    'name'          => 'Usdt',
    'unique_key'    => 'usdt',
];

$res            = $tokenModel->create($data);

print_r($res);
