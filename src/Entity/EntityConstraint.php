<?php

namespace Ci4Orm\Entity;

class EntityConstraint {

	private $constraints = [];

	public function addConstraint($key, $value){
		$this->constraints[$key] = $value;
	}

	public function getConstraints(){
		return $this->constraints;
	}
}
