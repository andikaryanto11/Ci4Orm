<?php

namespace Ci4Orm\Entity;

use Ci4Orm\Interfaces\IEntity;
use Ci4Orm\Repository\Repository;
use Ci4Orm\Entity\EntityConstraint;
use ReflectionClass;

class Entity implements IEntity
{

	public array $constraints = [];

	/**
	 * Constructor
	 */
	public function __construct()
	{

	}

	public function __call($name, $arguments)
	{
		$method = substr($name, 0, 3);
		if ($method == 'get') {
			$currentClass = get_class($this);
			$reflect = (new ReflectionClass($this))->getMethod($name)->getReturnType();
			$returnType = $reflect->getName();

			$arrayType = explode("\\", $returnType);

			$arrClass = explode('\\', $currentClass);

			$field = substr($name, 3);
			if (count($arrayType) > 1) {
				$classIndex = count($arrayType) - 1;
				if ($arrayType[$classIndex] == 'Lists') {
					$dataExist = call_user_func_array([$this, $name], $arguments);

					if(!empty($dataExist))
						return $dataExist;

					$currentProps = ORM::getProps($currentClass);
					$relatedEntity = $currentProps['props'][$field]['type'];

					$primarykey = 'get' . $currentProps['primaryKey'];

					$relatedProps = ORM::getProps($relatedEntity);

					$currentClassIndex = count($arrClass) - 1;
					$foreignKey = $relatedProps['props'][$arrClass[$currentClassIndex]]['foreignKey'];


					$param = [
						'where' => [
							$foreignKey => $this->$primarykey()
						]
					];
					$list = (new Repository($relatedEntity))->collect($param);

					$setFn = 'set' . $field;
					call_user_func_array([$this, $setFn], [$list]);
				}
				else {
					$dataExist = call_user_func_array([$this, $name], $arguments);

					if(!empty($dataExist))
						return $dataExist;

					$currentProps = ORM::getProps($currentClass);
					$relatedEntity = $currentProps['props'][$field]['type'];

					$foreignKey = $currentProps['props'][$field]['foreignKey'];

					$key = $this->constraints[$foreignKey];
					if(!empty($key)){
						$instance = (new Repository($relatedEntity))->find($key);

						$setFn = 'set' . $field;
						call_user_func_array([$this, $setFn], [$instance]);
					}
				}
			}
		}

		return call_user_func_array([$this, $name], $arguments);
	}
}
