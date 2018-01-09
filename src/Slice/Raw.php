<?php

namespace Bavix\Slice;

class Raw
{

    /**
     * @var mixed
     */
    protected $data;

    /**
     * Raw constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
