<?php
/**
 * Created by PhpStorm.
 * User: monday41
 * Date: 2018/3/16
 * Time: 下午5:13
 */
require_once __DIR__ . '/../../autoload.php';

use \ThirdParty\GOPAX\API as GOPAX_API;
use \MT\API\TickerModel;

class TickerUpdateCommand
{
    public function update()
    {
        $uniqueId           = uniqid();
        echo sprintf("Start updating gopax ticker[%s]! Time: %s\n", $uniqueId, time());

        $gopaxTokenList     = GOPAX_API::getAssets();
        if (empty($gopaxTokenList) || !is_array($gopaxTokenList)) {
            echo sprintf("get gopaxTokenList failed. File: %s Line: %s\n", __FILE__, __LINE__);
            return ;
        }

        $gopaxTradingList   = GOPAX_API::getTradingPairs();
        if (empty($gopaxTradingList) || !is_array($gopaxTradingList)) {
            echo sprintf("get gopaxTradingList failed. File: %s Line: %s\n", __FILE__, __LINE__);
            return ;
        }

        $tokenHash          = array_combine(array_column($gopaxTokenList, 'id'), $gopaxTokenList);

        // exchange name[the section key] from conf/app.ini
        $tickerModel        = new TickerModel('gopax');
        foreach ($gopaxTradingList as $tickerInfo) {
            $tradingPairInfo = GOPAX_API::getTickerPairs($tickerInfo['name']);
            $data           = [
                'symbol_key'        => $tickerInfo['baseAsset'],
                'symbol_name'       => $tokenHash[$tickerInfo['baseAsset']]['name'],
                'anchor_key'        => $tickerInfo['quoteAsset'],
                'anchor_name'       => $tokenHash[$tickerInfo['quoteAsset']]['name'],
                'price'             => $tradingPairInfo['price'],
                'price_updated_at'  => $tradingPairInfo['time'],
                'volume_24h'        => $tradingPairInfo['volume'],
                'volume_anchor_24h' => $tradingPairInfo['volume'] * $tradingPairInfo['price'],
            ];
            $res            = $tickerModel->create($data);
            if (isset($res['code']) && isset($res['message'])) {
                echo sprintf("update ticker failed. Data: %s, Code: %s, Message: %s\n", json_encode($data), $res['code'], $res['message']);
            }
        }

        echo sprintf("Finish updating gopax ticker[%s]! Time: %s\n", $uniqueId, time());
    }
}

$tickerUpdateCmd = new TickerUpdateCommand();
$tickerUpdateCmd->update();
