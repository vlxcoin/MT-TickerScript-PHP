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

class TickerModel extends BaseModel
{
    protected $fields   = [
        'symbol_key'        => [self::FIELD_TYPE_TEXT, self::FIELD_ALLOW_REQUIRED],
        'symbol_name'       => [self::FIELD_TYPE_TEXT, self::FIELD_ALLOW_OPTIONAL],
        'anchor_key'        => [self::FIELD_TYPE_TEXT, self::FIELD_ALLOW_REQUIRED],
        'anchor_name'       => [self::FIELD_TYPE_TEXT, self::FIELD_ALLOW_OPTIONAL],
        'price'             => [self::FIELD_TYPE_FLOAT, self::FIELD_ALLOW_REQUIRED],
        'price_updated_at'  => [self::FIELD_TYPE_TEXT, self::FIELD_ALLOW_OPTIONAL],
        'volume_24h'        => [self::FIELD_TYPE_FLOAT, self::FIELD_ALLOW_REQUIRED],
        'volume_anchor_24h' => [self::FIELD_TYPE_FLOAT, self::FIELD_ALLOW_REQUIRED],
    ];

    /**
     * @param $data
     * Array
     * [
            symbol_key* string
            currency symbol(货币唯一主键 BTC 或者平台唯一ID)

            symbol_name	string
            currency name(货币别名 BTC)

            anchor_key*	string
            anchor currency symbol(锚定货币唯一主键 BTC 或者平台唯一ID)

            anchor_name	string
            anchor currency name(锚定货币别名 BTC)

            price*	number($double)
            last transaction price(最新成交价格)

            price_updated_at string($datetime)
            last updated time(价格最后更新时间)

            volume_24h*	number($double)
            24h trading volume(24成交量)

            volume_anchor_24h*	number($double)
            24h trading volume based on anchor currency(24小时基于锚定货币的成交额)
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
            'url'   => '/tickers',
            'data'  => $filterData,
            'method'=> 'post',
        ];

        $response               = Http::request($request, $this->exchangeSubject);

        return $response;
    }

    /**
     * @return array
     */
    public function allTickers()
    {
        $request    = [
            'url'   => '/tickers',
            'data'  => [],
            'method'=> 'get',
        ];

        $response               = Http::request($request, $this->exchangeSubject);

        return $response;
    }

    /**
     * @param $dataList
     * Array
     * [
     *      [
                symbol_key* string
                currency symbol(货币唯一主键 BTC 或者平台唯一ID)

                symbol_name	string
                currency name(货币别名 BTC)

                anchor_key*	string
                anchor currency symbol(锚定货币唯一主键 BTC 或者平台唯一ID)

                anchor_name	string
                anchor currency name(锚定货币别名 BTC)

                price*	number($double)
                last transaction price(最新成交价格)

                price_updated_at string($datetime)
                last updated time(价格最后更新时间)

                volume_24h*	number($double)
                24h trading volume(24成交量)

                volume_anchor_24h*	number($double)
                24h trading volume based on anchor currency(24小时基于锚定货币的成交额)
     *      ], ...
     * ]
     * @return array
     */
    public function batchCreate($dataList)
    {
        $filterDataList     = [];
        foreach ($dataList as $data) {
            $filterData     = $this->filterParams($data);
            if (!$filterData) {
                return ErrorCode::returnError(ErrorCode::PARAMS_ERROR);
            }
            $filterDataList[]   = $filterData;
        }

        $request    = [
            'url'   => '/tickers/batch_create',
            'data'  => [
                'tickers'   => $filterDataList,
            ],
            'method'=> 'post',
        ];

        $response               = Http::request($request, $this->exchangeSubject);

        return $response;
    }
}
