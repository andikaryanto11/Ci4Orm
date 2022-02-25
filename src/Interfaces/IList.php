<?php
namespace Ci4Orm\Interfaces;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;

interface IList extends IteratorAggregate, JsonSerializable, Countable {
    
}