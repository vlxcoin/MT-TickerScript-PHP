<?php
/**
 * Created by PhpStorm.
 * User: monday41
 * Date: 2018/3/15
 * Time: 下午9:07
 */
namespace ThirdParty\GOPAX;

use \MT\Config;
use \MT\Http;

class API
{
    const API_HOST = 'https://api.gopax.co.kr';

    protected static function getSignature($nonce, $method, $requestPath, $secret)
    {
        $what       = $nonce . $method . $requestPath;
        $key        = base64_decode($secret);
        $hmac       = hash_hmac('sha512', $what, $key);
        return base64_encode($hmac);
    }

    protected static function call($path, $data = [], $method = 'get', $needAuth = false)
    {
        // No authorization required generally, it's just a try out.
        if ($needAuth) {
            $config     = Config::getConfig();
            $apiKey     = $config['gopax']['api_key'];
            $secret     = $config['gopax']['secret'];
            $nonce      = time();
            $signature  = self::getSignature($nonce, $method, $path, $secret);
            $header     = [
                'API-KEY'       => $apiKey,
                'SIGNATURE'     => $signature,
                'NONCE'         => $nonce,
            ];
        } else {
            $header     = [];
        }

        $request    = [
            'host'      => self::API_HOST,
            'header'    => $header,
            'url'       => $path,
            'method'    => $method,
            'data'      => $data,
        ];

        $response               = Http::request($request);

        return $response;
    }

    protected static function arrayDiffAssocRecursive($array1,$array2)
    {
        $diffArray      = array();
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key])) {
                    $diffArray[$key]    = $value;
                } else if (!is_array($array2[$key])) {
                    $diffArray[$key]    = $value;
                }else{
                    $diff               = self::arrayDiffAssocRecursive($value, $array2[$key]);
                    if ($diff != false) {
                        $diffArray[$key]= $diff;
                    }
                }
            } else if (!array_key_exists($key, $array2) || $value !== $array2[$key]) {
                $diffArray[$key]        = $value;
            }
        }
        return $diffArray;
    }

    protected static function returnNewDataFromFile($subject, $data)
    {
        $fileDir        = APP_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . str_replace("\\", "_", __NAMESPACE__);
        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0777, true);
        }

        $fileName       = $fileDir . DIRECTORY_SEPARATOR . $subject;
        if (file_exists($fileName)) {
            $srcContent = file_get_contents($fileName);
            $srcData    = json_decode($srcContent, true);
            $results    = self::arrayDiffAssocRecursive($data, $srcData);
        } else {
            $results    = $data;
        }
        file_put_contents($fileName, json_encode($data));

        return $results;
    }

    public static function getAssets($returnNew = false)
    {
        $path       = '/assets';
        $result     = self::call($path);

        if ($returnNew && $result) {
            return self::returnNewDataFromFile('assets', $result);
        }

        return $result;
    }

    public static function getTradingPairs($returnNew = false)
    {
        $path       = '/trading-pairs';
        $result     = self::call($path);

        if ($returnNew && $result) {
            return self::returnNewDataFromFile('trading-pairs', $result);
        }

        return $result;
    }

    public static function getTickerPairs($tradingPairName)
    {
        $path       = sprintf("/trading-pairs/%s/ticker", $tradingPairName);
        return self::call($path);
    }

    public static function getBatchTickerPairs($tradingPairNameList)
    {
        $requests       = [];

        foreach ($tradingPairNameList as $tradingPairName) {
            $path       = sprintf("/trading-pairs/%s/ticker", $tradingPairName);
            $requests[] = [
                'host'      => self::API_HOST,
                'url'       => $path,
                'method'    => 'get',
                'data'      => [],
                'key'       => $tradingPairName,
            ];
        }

        $response       = Http::multiRequest($requests);
        return $response;
    }
}
