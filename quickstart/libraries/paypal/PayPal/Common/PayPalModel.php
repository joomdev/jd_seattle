<?php

namespace PayPal\Common;
use PayPal\Validation\JsonValidator;
use PayPal\Validation\ModelAccessorValidator;

/**
 * Generic Model class that all API domain classes extend
 * Stores all member data in a Hashmap that enables easy
 * JSON encoding/decoding
 */
class PayPalModel
{

    private $_propMap = array();

    /**
     * OAuth Credentials to use for this call
     *
     * @var \PayPal\Auth\OAuthTokenCredential $credential
     */
    protected static $credential;

    /**
     * Sets Credential
     *
     * @deprecated Pass ApiContext to create/get methods instead
     * @param \PayPal\Auth\OAuthTokenCredential $credential
     */
    public static function setCredential($credential)
    {
        self::$credential = $credential;
    }

    /**
     * Default Constructor
     *
     * You can pass data as a json representation or array object. This argument eliminates the need
     * to do $obj->fromJson($data) later after creating the object.
     *
     * @param null $data
     * @throws \InvalidArgumentException
     */
    public function __construct($data = null)
    {
        switch (gettype($data)) {
            case "NULL":
                break;
            case "string":
                JsonValidator::validate($data);
                $this->fromJson($data);
                break;
            case "array":
                $this->fromArray($data);
                break;
            default:
        }
    }

    /**
     * Returns a list of Object from Array or Json String. It is generally used when you json
     * contains an array of this object
     *
     * @param mixed $data Array object or json string representation
     * @return array
     */
    public static function getList($data)
    {
        if (!is_array($data) && JsonValidator::validate($data)) {
            //Convert to Array if Json Data Sent
            $data = json_decode($data, true);
        }
        if (!ArrayUtil::isAssocArray($data)) {
            $list = array();
            //This means, root element is array
            foreach ($data as $k => $v) {
                $obj = new static;
                $obj->fromArray($v);
                $list[] = $obj;
            }
            return $list;
        }
    }

    /**
     * Magic Get Method
     *
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        if ($this->__isset($key)) {
            return $this->_propMap[$key];
        }
        return null;
    }

    /**
     * Magic Set Method
     *
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        ModelAccessorValidator::validate($this, $this->convertToCamelCase($key));
        if ($value == null) {
            $this->__unset($key);
        } else {
            $this->_propMap[$key] = $value;
        }
    }

    /**
     * Converts the input key into a valid Setter Method Name
     *
     * @param $key
     * @return mixed
     */
    private function convertToCamelCase($key)
    {
        return str_replace(' ', '', ucwords(str_replace(array('_', '-'), ' ', $key)));
    }

    /**
     * Magic isSet Method
     *
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->_propMap[$key]);
    }

    /**
     * Magic Unset Method
     *
     * @param $key
     */
    public function __unset($key)
    {
        unset($this->_propMap[$key]);
    }

    /**
     * Converts Params to Array
     *
     * @param $param
     * @return array
     */
    private function _convertToArray($param)
    {
        $ret = array();
        foreach ($param as $k => $v) {
            if ($v instanceof PayPalModel) {
                $ret[$k] = $v->toArray();
            } else if (is_array($v)) {
                $ret[$k] = $this->_convertToArray($v);
            } else {
                $ret[$k] = $v;
            }
        }
        // If the array is empty, which means an empty object,
        // we need to convert array to StdClass object to properly
        // represent JSON String
        if (sizeof($ret) <= 0) {
            $ret = new PayPalModel();
        }
        return $ret;
    }

    /**
     * Fills object value from Array list
     *
     * @param $arr
     * @return $this
     */
    public function fromArray($arr)
    {
        if (!empty($arr)) {
            foreach ($arr as $k => $v) {
                if (is_array($v)) {
                    $clazz = ReflectionUtil::getPropertyClass(get_class($this), $k);
                    if (ArrayUtil::isAssocArray($v)) {
                        /** @var self $o */
                        $o = new $clazz();
                        $o->fromArray($v);
                        $this->assignValue($k, $o);
                    } else {
                        $arr = array();
                        foreach ($v as $nk => $nv) {
                            if (is_array($nv)) {
                                $o = new $clazz();
                                $o->fromArray($nv);
                                $arr[$nk] = $o;
                            } else {
                                $arr[$nk] = $nv;
                            }
                        }
                        $this->assignValue($k, $arr);
                    }
                } else {
                    $this->assignValue($k, $v);
                }
            }
        }
        return $this;
    }

    private function assignValue($key, $value)
    {
        if (ModelAccessorValidator::validate($this, $this->convertToCamelCase($key))) {
            $setter = "set" . $this->convertToCamelCase($key);
            $this->$setter($value);
        } else {
            $this->__set($key, $value);
        }
    }

    /**
     * Fills object value from Json string
     *
     * @param $json
     * @return $this
     */
    public function fromJson($json)
    {
        return $this->fromArray(json_decode($json, true));
    }

    /**
     * Returns array representation of object
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_convertToArray($this->_propMap);
    }

    /**
     * Returns object JSON representation
     *
     * @param int $options http://php.net/manual/en/json.constants.php
     * @return string
     */
    public function toJSON($options = 0)
    {
        // Because of PHP Version 5.3, we cannot use JSON_UNESCAPED_SLASHES option
        // Instead we would use the str_replace command for now.
        // TODO: Replace this code with return json_encode($this->toArray(), $options | 64); once we support PHP >= 5.4
        if (version_compare(phpversion(), '5.4.0', '>=') === true) {
            return json_encode($this->toArray(), $options | 64);
        }
        return str_replace('\\/', '/', json_encode($this->toArray(), $options));
    }

    /**
     * Magic Method for toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJSON(128);
    }
}
