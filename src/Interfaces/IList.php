<?php
namespace Ci4Orm\Interfaces;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;

interface IList extends JsonSerializable, Countable {

	/**
	 * Count element
	 *
	 * @return int
	 */
	public function getSize();
}
