<?php
/**
 * Created by PhpStorm.
 * User: monday41
 * Date: 2018/3/14
 * Time: 下午3:33
 */

namespace MT\API;

class BaseModel
{
    protected $fields       = [];

    const FIELD_TYPE_TEXT   = 'text';
    const FIELD_TYPE_INT    = 'int';
    const FIELD_TYPE_FLOAT  = 'float';

    const FIELD_ALLOW_REQUIRED  = 'required';
    const FIELD_ALLOW_OPTIONAL  = 'optional';

    protected $exchangeSubject  = "";

    /**
     * BaseModel constructor.
     * @param string $exchangeSubject
     * exchange name[the section key] from conf/app.ini
     */
    public function __construct($exchangeSubject = '')
    {
        $this->exchangeSubject  = $exchangeSubject;
    }

    private function getFields()
    {
        return $this->fields;
    }

    public function filterParams($params)
    {
        if (!$params) {
            return [];
        }

        $fields         = $this->getFields();

        $data           = [];
        foreach($fields as $field => $row) {
            list($fieldType, $fieldAllow) = $row;

            if (isset($params[$field])) {
                switch ($fieldType) {
                    case self::FIELD_TYPE_TEXT:
                        $value  = strval($params[$field]);
                        break;
                    case self::FIELD_TYPE_INT:
                        $value  = intval($params[$field]);
                        break;
                    case self::FIELD_TYPE_FLOAT:
                        $value  = floatval($params[$field]);
                        break;
                    default:
                        return false;
                }
                $data[$field]   = $value;
            } else if (self::FIELD_ALLOW_REQUIRED == $fieldAllow && !isset($params[$field])) {
                return false;
            }
        }

        return $data;
    }
}