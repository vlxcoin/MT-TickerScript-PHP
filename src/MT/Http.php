<?php
namespace MT;

class Http
{
    private $responses  = array();

    const API_ENV_DEV  = 'dev';
    const API_ENV_PROD = 'prod';

    static $API_ENVS    = [self::API_ENV_DEV, self::API_ENV_PROD];

    public function __construct()
    {
    }

    static function getApiServer($env)
    {
        return $env == self::API_ENV_DEV ? Config::API_HOST_DEV : Config::API_HOST_PROD;
    }

    static function callback($data, $delay)
    {
        usleep($delay);
        return $data;
    }

    public static function PackageGetRequest( &$ch, $request, $timeOut = 10 )
    {
        $path            = http_build_query( $request['data'] );

        $url             = $request['host'];
        $request['url'] .= '?' . $path;

        $headers         = [];
        if (isset($request['header'])) {
            $headers     = array_merge($headers, $request['header']);
        }

        curl_setopt($ch, CURLOPT_URL, $url . $request['url']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    static function PackagePostRequest( &$ch, $request ){
        $url             = $request['host'];

        $headers         = [
            "Content-type: application/json;charset='utf-8'",
            "Accept: application/json",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
        ];
        if (isset($request['header'])) {
            $headers     = array_merge($headers, $request['header']);
        }

        curl_setopt($ch, CURLOPT_URL, $url . $request['url']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request['data']));
    }

    public function processMultiResponse( $instance )
    {
        $this->responses[$instance->id] = $instance->response;
    }

    public function getResponse()
    {
        ksort($this->responses);

        return $this->responses;
    }

    static function request($request, $exchangeSubject = "", $timeOut = 10){
        if ($exchangeSubject) {
            $config         = Config::getConfig();
            if (!isset($config[$exchangeSubject]['api_key'])) {
                return ErrorCode::returnError(ErrorCode::MISSING_CONFIGURATION_INFO);
            }
            $exchangeConfig = $config[$exchangeSubject];
            $env            = isset($exchangeConfig['env']) && in_array($exchangeConfig['env'], self::$API_ENVS) ? $exchangeConfig['env'] : self::API_ENV_DEV;
            $request['host']= isset($request['host']) ? $request['host'] : self::getApiServer($env);
            if (isset($exchangeConfig['mt_api_key'])) {
                $mtApiKeyHeader     = "X-API-key: " . $exchangeConfig['mt_api_key'];
                $request['header']  = isset($request['header']) ? array_merge($request['header'], $mtApiKeyHeader) : [$mtApiKeyHeader];
            }
        }
        if (!isset($request['host'])) {
            $request['host']        = self::getApiServer(self::API_ENV_DEV);
        }

        $request['method']  = isset($request['method']) ? $request['method'] : 'get';

        $ch       = curl_init();
        switch ( $request['method'] ) {
            case 'get':
            case 'GET':
                self::PackageGetRequest( $ch, $request, $timeOut );
                break;
            case 'post':
            case 'POST':
                self::PackagePostRequest( $ch, $request );
                break;
            default: break;
        }

        $response         = curl_exec($ch);

        $status           = curl_getinfo($ch);

        $result           = json_decode($response, true);
        $httpCode         = intval($status["http_code"]);
        if (!$result && !is_array($result) || isset($result['code']) && !isset($result['message'])) {
            curl_close( $ch );
            return ErrorCode::returnError($httpCode);
        }

        curl_close( $ch );

        return $result;
    }
}
