<?php
/**
 * Created by PhpStorm.
 * User: monday41
 * Date: 2018/3/14
 * Time: 下午8:27
 */

require_once __DIR__ . '/../autoload.php';

use \MT\API\TickerModel;

$tickerModel    = new TickerModel('gopax');

$data           = [
    'symbol_key'        => 'ELF',
    'symbol_name'       => 'aaaa',
    'anchor_key'        => 'KRW',
    'anchor_name'       => 'bbbb',
    'price'             => 10.23,
    'price_updated_at'  => '2017-01-01T16:03:08.123456z',
    'volume_24h'        => 2321333.1231,
    'volume_anchor_24h' => 1233.1233123,
];

$res            = $tickerModel->create($data);

print_r($res);
