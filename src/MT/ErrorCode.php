<?php
namespace MT;

final class ErrorCode
{
    const HTTP_CODE_OK                          = 200;
    const HTTP_CODE_CREATED                     = 201;
    const HTTP_CODE_BAD_REQUEST                 = 400;
    const HTTP_CODE_UNAUTHORIZED                = 401;
    const HTTP_CODE_FORBIDDEN                   = 403;
    const HTTP_CODE_NOT_FOUND                   = 404;
    const HTTP_CODE_METHOD_NOT_ALLOW            = 405;
    const HTTP_CODE_TOO_MANY_REQUESTS           = 429;
    const HTTP_CODE_INTERNAL_SERVICE_ERROR      = 500;

    const UNKNOWN_ERROR                         = 9999;
    const PARAMS_ERROR                          = 9998;
    const MISSING_CONFIGURATION_INFO            = 9997;
    const MISSING_REQUEST_KEY_HOST              = 9996;

    static $ERROR_MSG                   = [
        self::HTTP_CODE_OK                  => 'OK',
        self::HTTP_CODE_CREATED             => 'Created',
        self::HTTP_CODE_BAD_REQUEST         => 'Bad Request',
        self::HTTP_CODE_UNAUTHORIZED        => 'Unauthorized',
        self::HTTP_CODE_FORBIDDEN           => 'Forbidden',
        self::HTTP_CODE_NOT_FOUND           => 'Not Found',
        self::HTTP_CODE_METHOD_NOT_ALLOW    => 'Method Not Allowed',
        self::HTTP_CODE_TOO_MANY_REQUESTS   => 'Too Many Requests',
        self::HTTP_CODE_INTERNAL_SERVICE_ERROR  => 'Internal Server Error',
        self::UNKNOWN_ERROR                 => 'unknown error',
        self::PARAMS_ERROR                  => 'params error',
        self::MISSING_CONFIGURATION_INFO    => 'missing configuration info',
        self::MISSING_REQUEST_KEY_HOST      => 'missing host of http request',
    ];

    public static function returnError($errorCode)
    {
        $errMsg     = isset(self::$ERROR_MSG[$errorCode]) ? self::$ERROR_MSG[$errorCode] : self::$ERROR_MSG[self::UNKNOWN_ERROR];

        return [
            'code'      => $errorCode,
            'message'   => $errMsg,
        ];
    }
}
