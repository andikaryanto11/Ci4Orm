<?php

namespace Ci4Orm\Entities;

use Ci4Orm\Interfaces\IEntity;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use DateTime;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Date;

class EntityManager
{
    /**
     *
     * @var BaseBuilder
     */
    protected BaseBuilder $builder;

    /**
     * @var IEntity $entity
     */
    protected IEntity $entity;

    /**
    * @var BaseConnection $db
    */
    protected BaseConnection $db;

    /**
     * @var array $columns
     */
    protected array $columns;

    /**
     * @var array $props
     */
    protected array $props;

    /**
     * @var string $primaryKey
     */
    protected string $primaryKey;


    /**
     * @var array $reservedField
     */
    protected array $reservedField = [
        'Created',
        'Modified'
    ];

    public function __construct()
    {

        $this->db = \Config\Database::connect();
    }

    /**
     * Set Entity to persist
     *
     * @param IEntity $entity
     * @return EntityManager
     */
    public function setEntity(IEntity $entity)
    {
        $this->entity = $entity;
        $this->primaryKey = $this->entity->getPrimaryKeyName();
        $this->builder = $this->db->table($this->entity->getTableName());
        return $this;
    }

    /**
     * Persist data to storage
     *
     * @return bool
     */
    public function persist()
    {
        $primaryKey = 'get' . $this->primaryKey;
        $primaryValue = $this->entity->$primaryKey();
        if (empty($primaryValue) || is_null($primaryValue)) {
            return $this->insert();
        } else {
            return $this->update();
        }
        return false;
    }

    /**
      * Update data
     * @return bool
     */
    private function update()
    {
        $data = $this->createArray();
        $getPrimaryKey = "get" . $this->primaryKey;
        $this->builder->where($this->primaryKey, $this->entity->$getPrimaryKey());
        if ($this->builder->update($data)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     * insert new data to table
     */
    private function insert()
    {

        $data = $this->createArray();
        if ($this->builder->set($data, true)->insert()) {
            $primaryKey = "set" . $this->primaryKey;
            $this->entity->$primaryKey($this->db->insertID());
            return true;
        }

        return false;
    }

    /**
     * Create array object to persist
     *
     * @return array
     */
    private function createArray()
    {
        $entityAsArray = [];
        $props = $this->entity->getProps();
        foreach ($props as $key => $prop) {
            $getFunction = 'get' . $key;
            $primaryKey = 'get' . $this->primaryKey;
            if (!$prop['isEntity']) {
                if ($prop['type'] != 'datetime') {
                    $entityAsArray[$key] = $this->entity->$getFunction();
                } else {
                    if (in_array($key, $this->reservedField)) {
                        $setDate = 'set' .  $key;
                        $date = new DateTime();
                        if (empty($this->entity->$primaryKey()) && $key == 'Created') {
                            $this->entity->$setDate($date);
                            $entityAsArray[$key] = $date->format('Y-m-d h:i:s');
                        }

                        if (!empty($this->entity->$primaryKey()) && $key == 'Modified') {
                            $this->entity->$setDate($date);
                            $entityAsArray[$key] = $date->format('Y-m-d h:i:s');
                        }
                    }
                }
            } else {
                if (isset($prop['foreignKey'])) {
                    $relatedEntity = ORM::getProps($prop['type']);
                    $relatedPrimaryKey = $relatedEntity['primaryKey'];
                    $getPrimaryKey = 'get' . $relatedPrimaryKey;
                    $entityAsArray[$prop['foreignKey']] = $this->entity->$getFunction()->$getPrimaryKey();
                }
            }
        }
        return $entityAsArray;
    }
}
