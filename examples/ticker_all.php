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

$res            = $tickerModel->allTickers();

print_r($res);
