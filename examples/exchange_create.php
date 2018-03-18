<?php
/**
 * Created by PhpStorm.
 * User: monday41
 * Date: 2018/3/14
 * Time: 下午4:15
 */

require_once __DIR__ . '/../autoload.php';

use \MT\API\ExchangeModel;

$exchangeModel  = new ExchangeModel();

$data           = [
    'name'          => 'gopax4',
    'website'       => 'https://www.gopax.co.kr',
    'contact'       => '8615266666666',
    'description'   => 'this is description',
    'logo_url'      => 'http://p1nzzscwm.bkt.clouddn.com/bittrex_logo.png',
];

$res            = $exchangeModel->create($data);

print_r($res);
