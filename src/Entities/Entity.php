<?php

namespace Ci4Orm\Entities;

use App\Controllers\Admin\Mpayment;
use Ci4Orm\Interfaces\IEntity;
use Ci4Orm\Repository\Repository;
use Ci4Orm\Entities\EntityConstraint;
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
				if ($arrayType[$classIndex] == 'EntityList') {
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

					if(!empty($arguments)){
						$primaryKey = '';
						$relatedClass = ORM::getProps($relatedEntity);
						$primaryKey = $relatedClass['primaryKey'];
						if(empty($arguments[0]->getAssociatedEntities())){
							$param = [
								'whereIn' => [
									$primaryKey => $arguments[0]->getAssociatedKey()[$foreignKey]
								]
							];

							$entities = (new Repository($relatedEntity))->collect($param);
							$arguments[0]->setAssociatedEntities($entities);
						}

						$getFn = 'get' . $primaryKey;
						foreach($arguments[0]->getAssociatedEntities() as $entity){
							if(!isset($this->constraints[$foreignKey]))
								return null;
								
							if($entity->$getFn() == $this->constraints[$foreignKey]){
								return $entity;
							}
						}

					} else {
						$key = isset($this->constraints[$foreignKey]) ? $this->constraints[$foreignKey] : null;
						if(!empty($key)){
							$instance = (new Repository($relatedEntity))->find($key);

							$setFn = 'set' . $field;
							call_user_func_array([$this, $setFn], [$instance]);
						}
					}
				}
			}
			return call_user_func_array([$this, $name], []);
		}

		return call_user_func_array([$this, $name], $arguments);
	}
}
