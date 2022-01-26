<?php
class TestIterator2 implements Iterator {

    protected $data;

    public function __construct(array $array)
    {
        $this->data = $array;
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->data);
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        next($this->data);
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->data);
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return key($this->data) !== null;
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->data);
    }
}
