<?php
/**
 * User: david
 * Date: 2018/4/23
 */
namespace ThirdParty\VeloxExchange;

use \MT\Config;
use \MT\Http;

class API
{
    const API_HOST = 'https://api.vlxexchange.com';

    protected static function call($path, $data = [], $method = 'get', $needAuth = false)
    {
        $request    = [
            'host'      => self::API_HOST,
            'header'    => [],
            'url'       => $path,
            'method'    => $method,
            'data'      => $data,
        ];

        $response               = Http::request($request);

        return $response;
    }

    protected static function getDiffMarkets($data, $srcData)
    {
        return array_diff_key($data, $srcData);
    }

    protected static function putMarkets($fileName, $data)
    {
        file_put_contents($fileName, json_encode($data));
    }

    // return new data from file cache
    protected static function returnNewDataFromFile($subject, $data)
    {
        $fileDir        = APP_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . str_replace("\\", "_", __NAMESPACE__);
        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0777, true);
        }
        
        if (!isset($data[$subject])) $data = [];
        $data = $data[$subject];

        $fileName       = $fileDir . DIRECTORY_SEPARATOR . $subject;
        if (file_exists($fileName)) {
            $srcContent = file_get_contents($fileName);
            $srcData    = json_decode($srcContent, true);
            $diffMarkets = self::getDiffMarkets($data, $srcData);
            $results    = [];
            foreach ($data as $key => $value) {
                if (isset($diffMarkets[$key])) {
                    $results[$key]  = $value;
                }
            }
        } else {
            $results    = $data;
        }
        self::putMarkets($fileName, $data);

        return $results;
    }

    public static function getTokenNames()
    {
        return array(
            'VLX' => 'Velox',
            'BTC' => 'Bitcoin',
            'LTC' => 'Litecoin',
            'DOGE' => 'Dogecoin',
            'DASH' => 'Dash'
        );
    }

    public static function getActiveTokens($markets)
    {
        $allTokenNames = self::getTokenNames();
        $activeTokenNames = array();
        foreach ($markets as $key => $data) {
            if (isset($allTokenNames[$data['market']]) && !isset($activeTokenNames[$data['market']])) $activeTokenNames[$data['market']] = $allTokenNames[$data['market']];
            if (isset($allTokenNames[$data['currency']]) && !isset($activeTokenNames[$data['currency']])) $activeTokenNames[$data['currency']] = $allTokenNames[$data['currency']];
        }
        return $activeTokenNames;
    }

    public static function getMarkets($returnNew = false)
    {
        $path       = '/markets';
        $result     = self::call($path);

        if ($returnNew && $result) {
            return self::returnNewDataFromFile('markets', $result);
        }

        return $result;
    }

    public static function getStats($returnNew = false)
    {
        $path       = '/stats';
        $result     = self::call($path);

        if ($returnNew && $result) {
            return self::returnNewDataFromFile('stats', $result);
        }

        return $result;
    }

    public static function getTransactions($market, $currency, $limit = 1)
    {
        $path       = sprintf("/transactions?market=%s&currency=%s&limit=%s", $market, $currency, $limit);
        return self::call($path);
    }

    public static function getBatchTransactions($tradingPairNameList, $limit = 1)
    {
        $requests       = [];

        foreach ($tradingPairNameList as $tradingPairName) {
            list($market, $currency) = explode('-', $tradingPairName);
            $path       = sprintf("/transactions?market=%s&currency=%s&limit=%s", $market, $currency, $limit);
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
