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

    private static function getApiServer($env)
    {
        return $env == self::API_ENV_DEV ? Config::API_HOST_DEV : Config::API_HOST_PROD;
    }

    protected static function PackageGetRequest( &$ch, $request, $timeOut = 10 )
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

    protected static function PackagePostRequest( &$ch, $request ){
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

    public static function request($request, $exchangeSubject = "", $timeOut = 10){
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

    public static function multiRequest($requests) {

        $queue                   = curl_multi_init();
        $map                     = array();

        foreach ($requests as $reqId => $request) {

            if( false == isset( $request['data'] ) || false == is_array( $request['data'] ) ){
                $request['data'] = array();
            }
            $ch                  = curl_init();
            switch ( $request['method'] ) {
                case 'get':
                case 'GET':
                    self::PackageGetRequest( $ch, $request );
                    break;
                case 'post':
                case 'POST':
                    self::PackagePostRequest( $ch, $request );
                    break;
                default: break;
            }
            curl_multi_add_handle($queue, $ch);
            $map[(string) $ch] = $request['key'];
        }

        $responses        = array();

        do {
            while (($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM) ;

            if ($code != CURLM_OK) { break; }

            // a request was just completed -- find out which one
            while ($done  = curl_multi_info_read($queue)) {

                // get the info and content returned on the request
                $info     = curl_getinfo($done['handle']);
                $error    = curl_error($done['handle']);
                $results  = curl_multi_getcontent($done['handle']);

                if( empty( $error ) ){
                    $responses[$map[(string) $done['handle']]] = json_decode( $results, true );
                } else {
                    $responses[$map[(string) $done['handle']]] = compact('info', 'error', 'results');
                }
                // remove the curl handle that just completed
                curl_multi_remove_handle($queue, $done['handle']);
                curl_close($done['handle']);
            }

            // Block for data in / output; error handling is done by curl_multi_exec
            if ($active > 0) {
                curl_multi_select($queue, 0.5);
            }

        } while ($active);

        curl_multi_close($queue);

        return $responses;
    }
}
