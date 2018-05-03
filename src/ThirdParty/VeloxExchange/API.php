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

    protected static function getDiffData($data, $srcData)
    {
        return array_diff_key($data, $srcData);
    }

    protected static function putData($fileName, $data)
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
            $diffData = self::getDiffData($data, $srcData);
            $results    = [];
            foreach ($data as $key => $value) {
                if (isset($diffData[$key])) {
                    $results[$key]  = $value;
                }
            }
        } else {
            $results    = $data;
        }
        self::putData($fileName, $data);

        return $results;
    }
    
    public static function getTokens($returnNew = false)
    {
        $path       = '/tokens';
        $result     = self::call($path);

        if ($returnNew && $result) {
            return self::returnNewDataFromFile('tokens', $result);
        }

        return $result;
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
        $path       = '/stats?market=ALL&currency=ALL';
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
