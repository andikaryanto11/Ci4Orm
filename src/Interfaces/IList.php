<?php
namespace AndikAryanto11\Interfaces;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;

interface IList extends IteratorAggregate, JsonSerializable, Countable {
    
}