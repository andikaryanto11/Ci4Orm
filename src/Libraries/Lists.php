<?php

namespace Ci4Orm\Libraries;

use Ci4Orm\Exception\ListException;
use Ci4Orm\Interfaces\IList;
use ArrayIterator;
use Traversable;

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

    /**
     * Filter item wit criteria
     * @param Closure $callback
     * @return Lists
     */
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

    /**
     * Check if items empty
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Get data from item with from range
     * @param int $number
     * @return array
     */
    public function take($number): array
    {
        if ($number <= 0)
            throw new ListException("Number must be greater than 0 (zero)");

        if (count($this->items) < $number) {
            return  $this->items;
        } else {
            return array_slice($this->items, 0, $number);
        }
    }

    /**
     * Get index of item
     * @param Closure $callback
     * @return int
     */
    public function index($callback): int{
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
     * @return
     */
    public function first(){
        if(empty($this->items))
            throw new ListException("Item empty");

        return $this->items[0];
    }

    /**
     * Get all items
     * @return array
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

	/**
	 *
	 * @inh
	 */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function jsonSerialize()
    {
        return $this->items;
    }

    /**
     * get size of list
     * @return int
     */
    public function getSize()
    {
        return count($this->items);
    }

}
