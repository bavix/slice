<?php

namespace Tests;

use Bavix\Slice\Slice;
use Bavix\Tests\Unit;

class SliceTest extends Unit
{

    /**
     * @var Slice
     */
    protected $slice;

    /**
     * @var array
     */
    protected $params = [
        'name'    => 'bavix/slice',
        'license' => 'MIT',
        'author'  => [
            'name'  => 'REZ1DENT3',
            'email' => 'info@babichev.net',
        ],
    ];

    /**
     * @var array
     */
    protected $data = [
        'name'    => '%name%',
        'license' => '%license%',
        'author'  => [
            'name'  => '%author.name%',
            'email' => '%author.email%',
        ],
    ];

    protected $matrix = [
        [1, 2, 3, 4],
        [2, 3, 4, 5],
        [3, 4, 5, 6],
        [4, 5, 6, 7],
    ];

    public function setUp()
    {
        parent::setUp();
        $this->slice = new Slice($this->data, $this->params);
    }

    public function testWithoutParams()
    {
        $this->slice = $this->slice->make($this->data);

        $this->assertArraySubset(
            $this->data,
            $this->slice->asArray()
        );
    }

    public function testAsArray()
    {
        $this->assertArraySubset(
            $this->params,
            $this->slice->asArray()
        );
    }

    public function testAsArraySlice()
    {
        $this->assertTrue(\is_array($this->slice->author));
        $this->slice->author = $this->slice->getSlice('author');
        $this->assertInstanceOf(Slice::class, $this->slice->author);

        /**
         * @var $author \Bavix\Slice\Slice
         */
        $author = $this->slice->author;

        $author->test = clone $author;

        $authors = [];

        foreach (range(1, 10) as $item)
        {
            $authors[] = $author;
        }

        $this->slice->setData($authors);

        foreach ($this->slice->asArray(0) as $slice)
        {
            $this->assertInstanceOf(Slice::class, $slice);
        }

        foreach ($this->slice->asArray(1) as $arr)
        {
            $this->assertTrue(\is_array($arr));
            $this->assertInstanceOf(Slice::class, $arr['test']);
        }

        foreach ($this->slice->asArray() as $arr)
        {
            $this->assertTrue(\is_array($arr));
            $this->assertTrue(\is_array($arr['test']));
        }
    }

    public function testKeys()
    {
        $this->assertArraySubset(
            \array_keys($this->data),
            $this->slice->keys()
        );
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->slice['name']));
        $this->assertFalse(isset($this->slice[__FUNCTION__]));
    }
    public function testOffsetSet()
    {
        $this->slice['name'] = __FUNCTION__;

        $this->assertSame(
            $this->slice->getData('name'),
            __FUNCTION__
        );
    }

    /**
     * @expectedException \Bavix\Exceptions\Invalid
     */
    public function testOffsetSetInvalid()
    {
        $this->slice[] = __FUNCTION__;
    }

    /**
     * @expectedException \Bavix\Exceptions\NotFound\Path
     */
    public function testMagicExceptionPath()
    {
        $this->slice->test;
    }

    /**
     * @expectedException \Bavix\Exceptions\NotFound\Path
     */
    public function testOffsetExceptionPath()
    {
        $this->slice['test'];
    }

    public function testOffsetUnset()
    {
        unset($this->slice['name']);
        $this->assertNull($this->slice->getData('name'));
    }

    public function testAsObject()
    {
        $slice = $this->slice->make($this->matrix);
        $rows  = $slice->asObject();

        foreach ($rows as $key => $row)
        {
            $this->assertInstanceOf(Slice::class, $row);
            $this->assertArraySubset(
                $this->matrix[$key],
                $row->asArray()
            );
        }
    }

    public function testFrom()
    {
        $example = Slice::from($result = [1, 2, 3]);

        $this->assertEquals($example, Slice::from($example));
        $this->assertInstanceOf(Slice::class, Slice::from($example));

        $this->assertEquals(
            $example->asArray(),
            $result
        );
    }

}
