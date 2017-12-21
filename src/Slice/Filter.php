<?php

namespace Bavix\Slice;

use Bavix\Exceptions\Invalid;

/**
 * Class Filter
 *
 * @package Bavix\Slice
 *
 * @method static int getInt(Slice $slice, $offset)
 * @method static float getFloat(Slice $slice, $offset)
 * @method static bool getBool(Slice $slice, $offset)
 * @method static string getEmail(Slice $slice, $offset)
 * @method static string getIP(Slice $slice, $offset)
 * @method static string getURL(Slice $slice, $offset)
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

        if (empty(static::$filters[$name]))
        {
            throw new Invalid('Filter `' . $name . '` not found');
        }

        /**
         * @var Slice  $slice
         * @var string $offset
         */
        list ($slice, $offset) = $arguments;

        return static::filterVariable(
            $slice->getData($offset),
            static::$filters[$name],
            static::$defaults[$name]
        );

    }

}
