<?php
/**
 * Created by PhpStorm.
 * User: monday41
 * Date: 2018/3/14
 * Time: 下午8:28
 */

require_once __DIR__ . '/../autoload.php';

use \MT\API\TickerModel;

$tickerModel    = new TickerModel('gopax');

$data           = [
    [
        'symbol_key'        => 'btc',
        'symbol_name'       => 'BTC',
        'anchor_key'        => 'eth',
        'anchor_name'       => 'Ethereum',
        'price'             => 10.23,
        'price_updated_at'  => '2017-01-01T16:03:08.123456z',
        'volume_24h'        => 2321333.1231,
        'volume_anchor_24h' => 1233.1233123,
    ],
    [
        'symbol_key'        => 'btc',
        'symbol_name'       => 'BTC',
        'anchor_key'        => 'usdt',
        'anchor_name'       => 'Usdt',
        'price'             => 10000,
        'price_updated_at'  => '2017-01-01T16:03:08.123456z',
        'volume_24h'        => 110000.1231,
        'volume_anchor_24h' => 670000.1233,
    ]
];

$res            = $tickerModel->batchCreate($data);

print_r($res);
