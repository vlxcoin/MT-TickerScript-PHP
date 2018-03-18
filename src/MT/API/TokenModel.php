<?php
/**
 * Created by PhpStorm.
 * User: monday41
 * Date: 2018/3/14
 * Time: 下午3:03
 */

namespace MT\API;

use MT\Http;
use MT\ErrorCode;

class TokenModel extends BaseModel
{
    protected $fields   = [
        'symbol'        => [self::FIELD_TYPE_TEXT, self::FIELD_ALLOW_REQUIRED],
        'name'          => [self::FIELD_TYPE_TEXT, self::FIELD_ALLOW_REQUIRED],
        'unique_key'    => [self::FIELD_TYPE_TEXT, self::FIELD_ALLOW_REQUIRED],
    ];

    /**
     * @param $data
     * Array
     * [
            symbol*	string
            currency symbol(货币)

            name*	string
            currency name(全称)

            unique_key*	string
            platform unique key, you can use currency symbol as unique key(唯一主键, 如果symbol不变,可以直接使用symbol, 建议使用平台的唯一标识)
     * ]
     * @return array
     */
    public function create($data)
    {
        $filterData     = $this->filterParams($data);
        if (!$filterData) {
            return ErrorCode::returnError(ErrorCode::PARAMS_ERROR);
        }

        $request    = [
            'url'   => '/tokens',
            'data'  => $filterData,
            'method'=> 'post',
        ];

        $response               = Http::request($request, $this->exchangeSubject);

        return $response;
    }

    public function allTokens()
    {
        $request    = [
            'url'   => '/tokens',
            'data'  => [],
            'method'=> 'get',
        ];

        $response               = Http::request($request, $this->exchangeSubject);

        return $response;
    }
}
