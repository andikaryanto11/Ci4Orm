<?php

namespace Ci4Orm\Entities;

use App\Controllers\Admin\Mpayment;
use Ci4Orm\Interfaces\IEntity;
use Ci4Orm\Repository\Repository;
use Ci4Orm\Entities\EntityConstraint;
use ReflectionClass;

class Entity implements IEntity
{
    /**
     *
     * @var array
     */
    public array $constraints = [];

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    public function getPrimaryKeyName()
    {
        return ORM::getProps(get_class($this))['primaryKey'];
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

                    if (!empty($dataExist)) {
                        return $dataExist;
                    }

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
                } else {
                    $dataExist = call_user_func_array([$this, $name], $arguments);

                    if (!empty($dataExist)) {
                        return $dataExist;
                    }

                    $currentProps = ORM::getProps($currentClass);
                    $relatedEntity = $currentProps['props'][$field]['type'];
                    $foreignKey = $currentProps['props'][$field]['foreignKey'];

                    $listOf = get_class($this);
                    $looper = EntityLooper::getInstance($listOf);

                    // which mean this call comes from loop EntityList
                    if ($looper->hasEntityList()) {
                        $entitylist = $looper->getEntityList();
                        $primaryKey = '';
                        $relatedClass = ORM::getProps($relatedEntity);
                        $primaryKey = $relatedClass['primaryKey'];
                        if (empty($looper->getItems())) {
                            $param = [
                                'whereIn' => [
                                    $primaryKey => $entitylist->getAssociatedKey()[$foreignKey]
                                ]
                            ];

                            $entities = (new Repository($relatedEntity))->collect($param)->getItems();
                            $items = [];
                            foreach ($entities as $entity) {
                                $getFn = 'get' . $primaryKey;
                                $pkValue = $entity->$getFn();
                                $items[$pkValue] = $entity;
                            }
                            $looper->setItems($items);
                        }

                        $result = null;
                        $itemOfLooper = $looper->getItems();
                        if (count($itemOfLooper) > 0) {
                            if (!empty($this->constraints)) {
                                $keyValue = $this->constraints[$foreignKey];
                                if (isset($itemOfLooper[$keyValue])) {
                                    $result = $itemOfLooper[$keyValue];
                                }
                            }
                        }

                        if ($looper->isLastIndex()) {
                            $looper->clean();
                        }

                        if (!is_null($result)) {
                            $setFn = 'set' . $field;
                            call_user_func_array([$this, $setFn], [$result]);
                        }
                    } else {
                        $key = isset($this->constraints[$foreignKey]) ? $this->constraints[$foreignKey] : null;
                        if (!empty($key)) {
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
