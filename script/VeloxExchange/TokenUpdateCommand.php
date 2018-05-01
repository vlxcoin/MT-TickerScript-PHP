<?php
/**
 * User: david
 * Date: 2018/4/23
 */
require_once __DIR__ . '/../../autoload.php';

use \ThirdParty\VeloxExchange\API as VELOXEXCHANGE_API;
use \MT\API\TokenModel;

class TokenUpdateCommand
{
    // Velox Exchange API call limit => 60 times per 1 minute

    public function update()
    {
        $uniqueId           = uniqid();
        echo sprintf("Start updating veloxexchange token[%s]! Time: %s\n", $uniqueId, time());

        $veTokenList        = VELOXEXCHANGE_API::getTokens(true);
        if (isset($veTokenList['code']) && isset($veTokenList['message'])) {
            echo sprintf("get veTokenList failed. File: %s Line: %s\n", __FILE__, __LINE__);
            return ;
        }

        // exchange name[the section key] from conf/app.ini
        $tokenModel         = new TokenModel('veloxexchange');
        foreach ($veTokenList['tokens'] as $key => $value) {
            $data           = [
                'symbol'    => $key,
                'name'      => $value['name_en'],
                'unique_key'=> $key,
            ];
            $res            = $tokenModel->create($data);
            if (isset($res['code']) && isset($res['message'])) {
                echo sprintf("create token failed. Data: %s, Code: %s, Message: %s\n", json_encode($data), $res['code'], $res['message']);
            }
        }

        echo sprintf("Finish updating veloxexchange token[%s]! Time: %s\n", $uniqueId, time());
    }
}

$tokenUpdateCmd = new TokenUpdateCommand();
$tokenUpdateCmd->update();
