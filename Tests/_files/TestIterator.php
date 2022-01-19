<?php
class TestIterator implements Iterator
{
    protected $array;
    protected $position = 0;

    public function __construct($array = array())
    {
        $this->array = $array;
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->position = 0;
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return $this->position < count($this->array);
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->array[$this->position];
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->position++;
    }
}
