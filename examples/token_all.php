<?php
/**
 * Created by PhpStorm.
 * User: monday41
 * Date: 2018/3/14
 * Time: 下午8:27
 */

require_once __DIR__ . '/../autoload.php';

use \MT\API\TokenModel;

$tokenModel     = new TokenModel('gopax');

$res            = $tokenModel->allTokens();

print_r($res);
