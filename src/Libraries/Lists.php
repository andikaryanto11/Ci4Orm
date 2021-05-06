<?php

namespace AndikAryanto11\Libraries;

use AndikAryanto11\Exception\ListException;
use AndikAryanto11\Interfaces\IList;
use ArrayIterator;

class Lists implements IList
{

    protected $items = [];
    public function __construct($items)
    {
        $this->items = $items;
    }


    public function add($item)
    {
    }

    public function filter($callback)
    {
        $newdata = [];
        foreach ($this->items as $item) {
            if ($callback($item)) {
                $newdata[] = $item;
            }
        }
        $this->items = $newdata;
        return $this;
    }

    public function where($callback)
    {
        $newdata = [];
        foreach ($this->items as $item) {
            if ($callback($item)) {
                $newdata[] = $item;
            }
        }
        return $newdata;
    }

    public function whereOne($callback){
        $datas = $this->where($callback);
        if(!empty($datas))
            return $datas[0];
        return null;
    }

    public function isEmpty()
    {
        return empty($this->items);
    }

    public function take($number)
    {
        if ($number <= 0)
            throw new ListException("Number must be greater than 0 (zero)");

        if (count($this->items) < $number) {
            return  $this->items;
        } else {
            return array_slice($this->items, 0, $number);
        }
    }

    public function index($callback){
        $i = 0;
        foreach ($this->items as $item) {
            if ($callback($item)) {
                return $i;
            }
            $i++;
        }
        return null;
    }

    /**
     * Get first element data
     */
    public function first(){
        if(empty($this->items))
            throw new ListException("Item empty");

        return $this->items[0];
    }

    /**
     * Get all items
     * 
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get last element data
     */
    public function last(){
        if(empty($this->items))
            throw new ListException("Item empty");

        return end($this->items);
    }

    public function getIterator()
    {

        return new ArrayIterator($this->items);
    }

    public function jsonSerialize()
    {
        return $this->items;
    }

    public function count()
    {
        return count($this->items);
    }

}
