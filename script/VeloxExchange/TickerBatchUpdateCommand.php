<?php
/**
 * User: david
 * Date: 2018/4/23
 */
require_once __DIR__ . '/../../autoload.php';

use \ThirdParty\VeloxExchange\API as VELOXEXCHANGE_API;
use \MT\API\TickerModel;

class TickerBatchUpdateCommand
{
    // Velox Exchange API call limit => 60 times per 1 minute
    const BATCH_CALL_LIMIT_SIZE  = 50;

    public function update()
    {
        $uniqueId           = uniqid();
        echo sprintf("Start batch updating veloxexchange ticker[%s]! Time: %s\n", $uniqueId, time());

        $veStatsList   = VELOXEXCHANGE_API::getStats();
        if (isset($veStatsList['code']) && isset($veStatsList['message'])) {
            echo sprintf("get veStatsList failed. Code: %s, Message: %s, File: %s, Line: %s\n",
                $veStatsList['code'], $veStatsList['message'], __FILE__, __LINE__);
            return ;
        }

        $tokenNames    = getTokenNames();
        $tickerPairHash             = $veStatsList['stats'];
        $tickerPairNameList         = array_keys($veStatsList['stats']);
        $tickerPairNameChunkList    = array_chunk($tickerPairNameList, self::BATCH_CALL_LIMIT_SIZE);
        // exchange name[the section key] from conf/app.ini
        $tickerModel                = new TickerModel('veloxexchange');

        foreach ($tickerPairNameChunkList as $index => $tickerPairNameList) {
            if ($index > 0) sleep(60);
            $tickerPairList         = VELOXEXCHANGE_API::getBatchTransactions($tickerPairNameList);
            if (isset($tickerPairList['code']) && isset($tickerPairList['message'])) {
                echo sprintf("get veTradingPairList failed. Code: %s, Message: %s, File: %s, Line: %s\n",
                    $tickerPairList['code'], $tickerPairList['message'], __FILE__, __LINE__);
                continue;
            }
            $data                   = [];
            foreach ($tickerPairList as $tickerPairName => $transactionsInfo) {
                $tickerPairInfo     = $tickerPairHash[$tickerPairName];
                $data[]             = [
                    'symbol_key'        => $tickerPairInfo['market'],
                    'symbol_name'       => $tokenNames[$tickerPairInfo['market']],
                    'anchor_key'        => $tickerPairInfo['currency'],
                    'anchor_name'       => $tokenNames[$tickerPairInfo['currency']],
                    'price'             => $tickerPriceInfo['last_price'],
                    'price_updated_at'  => gmdate('Y-m-d H:i:s O', $transactionsInfo['transactions'][0]['timestamp']),
                    'volume_24h'        => $tickerPairInfo['pair_volume_self'],
                    'volume_anchor_24h' => $tickerPairInfo['pair_volume'],
                ];
            }
            $res                    = $tickerModel->batchCreate($data);
            if (isset($res['code']) && isset($res['message']) && $res['code'] != 201) {
                echo sprintf("batch update ticker failed. Data: %s, Code: %s, Message: %s\n", json_encode($data), $res['code'], $res['message']);
            }
        }

        echo sprintf("Finish batch updating veloxexchange ticker[%s]! Time: %s\n", $uniqueId, time());
    }
}

$tickerBatchUpdateCmd = new TickerBatchUpdateCommand();
$tickerBatchUpdateCmd->update();
