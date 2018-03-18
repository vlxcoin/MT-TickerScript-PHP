<?php
namespace MT;

final class Config
{
    const API_HOST_DEV  = 'http://matrix.beta.mytoken.io/api/v1';
    const API_HOST_PROD = 'http://matrix.api.mytoken.io/api/v1';

    public static function getConfigFile()
    {
        return APP_PATH . DIRECTORY_SEPARATOR  . 'conf' . DIRECTORY_SEPARATOR . 'app.ini';
    }

    public static function getConfig()
    {
        $iniArray = parse_ini_file(APP_PATH . DIRECTORY_SEPARATOR  . 'conf' . DIRECTORY_SEPARATOR . 'app.ini', true);
        return $iniArray;
    }
}
