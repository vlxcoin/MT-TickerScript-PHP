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

class TickerBatchUpdateCommand
{
    // GOPAX API call limit => 20 times per 1 second
    const BATCH_CALL_LIMIT_SIZE  = 15;

    public function update()
    {
        $uniqueId           = uniqid();
        echo sprintf("Start batch updating gopax ticker[%s]! Time: %s\n", $uniqueId, time());

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

        $tokenHash                  = array_combine(array_column($gopaxTokenList, 'id'), $gopaxTokenList);
        $tickerPairNameList         = array_column($gopaxTradingList, 'name');
        $tickerPairHash             = array_combine($tickerPairNameList, $gopaxTradingList);
        $tickerPairNameChunkList    = array_chunk($tickerPairNameList, self::BATCH_CALL_LIMIT_SIZE);
        // exchange name[the section key] from conf/app.ini
        $tickerModel                = new TickerModel('gopax');

        foreach ($tickerPairNameChunkList as $tickerPairNameList) {
            sleep(1);
            $tickerPairList         = GOPAX_API::getBatchTickerPairs($tickerPairNameList);
            if (isset($tickerPairList['code']) && isset($tickerPairList['message'])) {
                echo sprintf("get gopaxTradingPairList failed. Code: %s, Message: %s, File: %s, Line: %s\n",
                    $tickerPairList['code'], $tickerPairList['message'], __FILE__, __LINE__);
                continue;
            }
            $data                   = [];
            foreach ($tickerPairList as $tickerPairName => $tickerPriceInfo) {
                $tickerPairInfo     = $tickerPairHash[$tickerPairName];
                $data[]             = [
                    'symbol_key'        => $tickerPairInfo['baseAsset'],
                    'symbol_name'       => $tokenHash[$tickerPairInfo['baseAsset']]['name'],
                    'anchor_key'        => $tickerPairInfo['quoteAsset'],
                    'anchor_name'       => $tokenHash[$tickerPairInfo['quoteAsset']]['name'],
                    'price'             => $tickerPriceInfo['price'],
                    'price_updated_at'  => $tickerPriceInfo['time'],
                    'volume_24h'        => $tickerPriceInfo['volume'],
                    'volume_anchor_24h' => $tickerPriceInfo['volume'] * $tickerPriceInfo['price'],
                ];
            }
            $res                    = $tickerModel->batchCreate($data);
            if (isset($res['code']) && isset($res['message']) && $res['code'] != 201) {
                echo sprintf("batch update ticker failed. Data: %s, Code: %s, Message: %s\n", json_encode($data), $res['code'], $res['message']);
            }
        }

        echo sprintf("Finish batch updating gopax ticker[%s]! Time: %s\n", $uniqueId, time());
    }
}

$tickerBatchUpdateCmd = new TickerBatchUpdateCommand();
$tickerBatchUpdateCmd->update();
