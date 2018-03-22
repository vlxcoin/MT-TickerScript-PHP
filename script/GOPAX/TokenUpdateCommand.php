<?php
/**
 * Created by PhpStorm.
 * User: monday41
 * Date: 2018/3/16
 * Time: 上午11:46
 */
require_once __DIR__ . '/../../autoload.php';

use \ThirdParty\GOPAX\API as GOPAX_API;
use \MT\API\TokenModel;

class TokenUpdateCommand
{
    public function update()
    {
        $uniqueId           = uniqid();
        echo sprintf("Start updating gopax token[%s]! Time: %s\n", $uniqueId, time());

        $gopaxTokenList    = GOPAX_API::getAssets(true);
        if (!is_array($gopaxTokenList)) {
            echo sprintf("get gopaxTokenList failed. File: %s Line: %s\n", __FILE__, __LINE__);
            return ;
        }

        // exchange name[the section key] from conf/app.ini
        $tokenModel         = new TokenModel('gopax');
        foreach ($gopaxTokenList as $tokenInfo) {
            $data           = [
                'symbol'    => $tokenInfo['id'],
                'name'      => $tokenInfo['name'],
                'unique_key'=> $tokenInfo['id'],
            ];
            $res            = $tokenModel->create($data);
            if (isset($res['code']) && isset($res['message'])) {
                echo sprintf("create token failed. Data: %s, Code: %s, Message: %s\n", json_encode($data), $res['code'], $res['message']);
            }
        }

        echo sprintf("Finish updating gopax token[%s]! Time: %s\n", $uniqueId, time());
    }
}

$tokenUpdateCmd = new TokenUpdateCommand();
$tokenUpdateCmd->update();
