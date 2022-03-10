<?php

namespace Ci4Orm\Entities;

class EntityConstraint {

	private $constraints = [];

	public function addConstraint($key, $value){
		$this->constraints[$key] = $value;
	}

	public function getConstraints(){
		return $this->constraints;
	}
}
