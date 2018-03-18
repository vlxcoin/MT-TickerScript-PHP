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

class ExchangeModel extends BaseModel
{
    protected $fields   = [
        'name'          => [self::FIELD_TYPE_TEXT, self::FIELD_ALLOW_REQUIRED],
        'website'       => [self::FIELD_TYPE_TEXT, self::FIELD_ALLOW_REQUIRED],
        'contact'       => [self::FIELD_TYPE_TEXT, self::FIELD_ALLOW_REQUIRED],
        'description'   => [self::FIELD_TYPE_TEXT, self::FIELD_ALLOW_OPTIONAL],
        'logo_url'      => [self::FIELD_TYPE_TEXT, self::FIELD_ALLOW_OPTIONAL],
    ];

    /**
     * @param $data
     * Array
     * [
            name*       string
            exchange name(交易所名称)

            website*	string
            official website(官网地址)

            contact*	string
            contact information(联系方式)

            description	string
            exchange description(交易所简介)

            logo_url    string
            logo url(交易所logo)
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
            'url'   => '/markets',
            'data'  => $filterData,
            'method'=> 'post',
        ];

        $response               = Http::request($request, $this->exchangeSubject);

        return $response;
    }
}
