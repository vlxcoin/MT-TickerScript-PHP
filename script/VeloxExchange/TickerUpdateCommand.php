<?php
/**
 * User: david
 * Date: 2018/4/23
 */
require_once __DIR__ . '/../../autoload.php';

use \ThirdParty\VeloxExchange\API as VELOXEXCHANGE_API;
use \MT\API\TickerModel;

class TickerUpdateCommand
{
    // Velox Exchange API call limit => 60 times per 1 minute

    public function update()
    {
        $uniqueId           = uniqid();
        echo sprintf("Start updating veloxexchange ticker[%s]! Time: %s\n", $uniqueId, time());

        $veStatsList   = VELOXEXCHANGE_API::getStats();
        if (isset($veStatsList['errors']) && $veStatsList['errors']) {
            echo sprintf("get veStatsList failed. Code: %s, Message: %s, File: %s, Line: %s\n",
                $veStatsList['code'], $veStatsList['message'], __FILE__, __LINE__);
            return ;
        }

        $tokenNames = getTokenNames();

        // exchange name[the section key] from conf/app.ini
        $tickerModel        = new TickerModel('veloxexchange');
        foreach ($veStatsList['stats'] as $tickerInfo) {
            $transactionsInfo = VELOXEXCHANGE_API::getTransactions($tickerInfo['market'], $tickerInfo['currency'], 1);
            $data           = [
                'symbol_key'        => $tickerInfo['market'],
                'symbol_name'       => $tokenNames[$tickerInfo['market']],
                'anchor_key'        => $tickerInfo['currency'],
                'anchor_name'       => $tokenNames[$tickerInfo['currency']],
                'price'             => $tickerInfo['last_price'],
                'price_updated_at'  => date('r', $transactionsInfo['transactions'][0]['timestamp']),
                'volume_24h'        => $tickerInfo['pair_volume_self'],
                'volume_anchor_24h' => $tickerInfo['pair_volume'],
            ];
            $res            = $tickerModel->create($data);
            if (isset($res['code']) && isset($res['message'])) {
                echo sprintf("update ticker failed. Data: %s, Code: %s, Message: %s\n", json_encode($data), $res['code'], $res['message']);
            }
        }

        echo sprintf("Finish updating veloxexchange ticker[%s]! Time: %s\n", $uniqueId, time());
    }
}

$tickerUpdateCmd = new TickerUpdateCommand();
$tickerUpdateCmd->update();
