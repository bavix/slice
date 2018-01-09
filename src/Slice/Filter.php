<?php

namespace Bavix\Slice;

use Bavix\Exceptions\Invalid;

/**
 * Class Filter
 *
 * @package Bavix\Slice
 *
 * @method static int getInt(mixed $data, $offset)
 * @method static float getFloat(mixed $data, $offset)
 * @method static bool getBool(mixed $data, $offset)
 * @method static string getEmail(mixed $data, $offset)
 * @method static string getIP(mixed $data, $offset)
 * @method static string getURL(mixed $data, $offset)
 */
class Filter
{

    /**
     * @var array
     */
    protected static $filters = [
        'getInt'   => FILTER_VALIDATE_INT,
        'getFloat' => FILTER_VALIDATE_FLOAT,
        'getBool'  => FILTER_VALIDATE_BOOLEAN,
        'getEmail' => FILTER_VALIDATE_EMAIL,
        'getIP'    => FILTER_VALIDATE_IP,
        'getURL'   => FILTER_VALIDATE_URL
    ];

    /**
     * @var array
     */
    protected static $defaults = [
        'getInt'   => 0,
        'getFloat' => .0,
        'getBool'  => false,
        'getEmail' => '',
        'getIP'    => '',
        'getURL'   => '',
    ];

    /**
     * @param mixed $data
     * @param mixed $type
     * @param mixed $default
     *
     * @return mixed
     */
    protected static function filterVariable($data, $type, $default)
    {
        return \filter_var($data, $type, [
            'options' => ['default' => $default]
        ]);
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     *
     * @throws \BadFunctionCallException
     */
    public static function __callStatic($name, $arguments)
    {

        if (empty(static::$filters[$name])) {
            throw new Invalid('Filter `' . $name . '` not found');
        }

        return static::filterVariable(
            $arguments[0],
            static::$filters[$name],
            static::$defaults[$name]
        );
    }
}
