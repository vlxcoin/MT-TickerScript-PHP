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
    // GOPAX API call limit => 20 times per 1 second

    public function update()
    {
        $uniqueId           = uniqid();
        echo sprintf("Start updating gopax ticker[%s]! Time: %s\n", $uniqueId, time());

        $gopaxTokenList     = GOPAX_API::getAssets();
        if (isset($gopaxTokenList['code']) && isset($gopaxTokenList['message'])) {
            echo sprintf("get gopaxTokenList failed. Code: %s, Message: %s, File: %s, Line: %s\n",
                $gopaxTokenList['code'], $gopaxTokenList['message'], __FILE__, __LINE__);
            return ;
        }

        $gopaxTradingList   = GOPAX_API::getTradingPairs();
        if (isset($gopaxTradingList['code']) && isset($gopaxTradingList['message'])) {
            echo sprintf("get gopaxTradingList failed. Code: %s, Message: %s, File: %s, Line: %s\n",
                $gopaxTradingList['code'], $gopaxTradingList['message'], __FILE__, __LINE__);
            return ;
        }

        $tokenHash          = array_combine(array_column($gopaxTokenList, 'id'), $gopaxTokenList);

        // exchange name[the section key] from conf/app.ini
        $tickerModel        = new TickerModel('gopax');
        foreach ($gopaxTradingList as $tickerInfo) {
            sleep(1);
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
