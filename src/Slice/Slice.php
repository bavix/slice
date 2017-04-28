<?php

namespace Bavix\Slice;

use Bavix\Helpers\Arr;
use Bavix\Iterator\Iterator;
use Bavix\Exceptions;

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
        $this->setData($data);

        if (null !== $parameters)
        {
            $this->walk($parameters);
        }
    }

    /**
     * @param Slice|array $slice
     */
    protected function walk($slice)
    {
        if (is_array($slice))
        {
            $slice = $this->make($slice);
        }

        Arr::walkRecursive($this->data, function (&$value) use ($slice)
        {
            if (!is_object($value) && $value{0} === '%' && $value{\strlen($value) - 1} === '%')
            {
                $path  = \substr($value, 1, -1);
                $value = $slice->getData($path);
            }
        });
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
     * @return Slice[]
     */
    public function asObject()
    {
        return \iterator_to_array($this->asGenerator());
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
