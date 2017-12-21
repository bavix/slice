<?php

namespace Bavix\Slice;

use Bavix\Exceptions;
use Bavix\Helpers\Arr;
use Bavix\Helpers\Str;
use Bavix\Iterator\Iterator;

/**
 * Class Slice
 *
 * @package Bavix\Slice
 *
 * @method int getInt($offset)
 * @method float getFloat($offset)
 * @method bool getBool($offset)
 * @method string getEmail($offset)
 * @method string getIP($offset)
 * @method string getURL($offset)
 *
 * @method int getRequiredInt($offset)
 * @method float getRequiredFloat($offset)
 * @method bool getRequiredBool($offset)
 * @method string getRequiredEmail($offset)
 * @method string getRequiredIP($offset)
 * @method string getRequiredURL($offset)
 */
class Slice extends Iterator
{

    /**
     * Slice constructor.
     *
     * @param array       $data
     * @param array|Slice $parameters
     */
    public function __construct(array $data, $parameters = null)
    {
        parent::__construct($data);

        if (null !== $parameters)
        {
            $this->walk($parameters);
        }
    }

    /**
     * @param array|\Traversable $data
     *
     * @return self
     */
    public static function from($data): self
    {
        if ($data instanceof self)
        {
            return $data;
        }

        return new static(
            Arr::iterator($data)
        );
    }

    /**
     * @param int|bool $depth
     *
     * @return array
     */
    public function asArray($depth = INF)
    {
        if (!$depth || $depth <= 0)
        {
            return $this->data;
        }

        $results = [];

        foreach (parent::asArray() as $key => $data)
        {
            $results[$key] =
                $data instanceof self ?
                    $data->asArray(\is_bool($depth) ? INF : --$depth) :
                    $data;
        }

        return $results;
    }

    /**
     * @param Slice|array $slice
     */
    protected function walk($slice)
    {
        if (\is_array($slice))
        {
            $slice = $this->make($slice);
        }

        Arr::walkRecursive($this->data, function (&$value) use ($slice) {

            if (\is_object($value) && $value instanceof Raw)
            {
                $value = $value->getData();

                return;
            }

            if (empty($value) || !\is_string($value))
            {
                return;
            }

            if (Str::first($value) === '%' &&
                Str::last($value) === '%' &&
                \substr_count($value, '%') === 2)
            {
                $path  = Str::sub($value, 1, -1);
                $value = $slice->getRequired($path);
            }

        });
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        list($offset) = $arguments;

        if (\strpos($name, 'Required') !== false)
        {
            $name = \str_replace('Required', '', $name);
            return Filter::$name($this->getRequired($offset));
        }

        return Filter::$name($this->getData($offset));
    }

    /**
     * @return \Generator|Slice[]
     */
    public function asGenerator()
    {
        foreach ($this->data as $key => $object)
        {
            yield $key => $this->make($object);
        }
    }

    /**
     * @return array
     */
    public function keys()
    {
        return Arr::getKeys($this->data);
    }

    /**
     * @return Slice[]
     */
    public function asObject()
    {
        return Arr::iterator($this->asGenerator());
    }

    /**
     * @param array $data
     *
     * @return static
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param string $offset
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getData($offset, $default = null)
    {
        return Arr::get($this->data, $offset, $default);
    }

    /**
     * @param string $offset
     *
     * @return Slice
     */
    public function getSlice($offset)
    {
        return $this->make($this->getRequired($offset));
    }

    /**
     * @param array $data
     *
     * @return Slice
     */
    public function make(array $data)
    {
        return (clone $this)->setData($data);
    }

    /**
     * @param string $offset
     *
     * @return array|mixed
     */
    public function getRequired($offset)
    {
        return Arr::getRequired($this->data, $offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return Arr::get($this->data, $offset) !== null;
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->getRequired($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if (null === $offset)
        {
            throw new Exceptions\Invalid('Slice does not support NULL');
        }

        Arr::set($this->data, $offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        Arr::remove($this->data, $offset);
    }

}
