<?php

namespace Ci4Orm\Entities;

use Ci4Common\Libraries\DbtransLib;
use Ci4Common\Services\DbTransService;
use Ci4Orm\Interfaces\IEntity;
use Exception;

class EntityUnit
{
    /**
     *
     * @var EntityUnit|null
     */
    private static ?EntityUnit $instance = null;

    /**
     *
     * @var array
     */
    private array $entities = [];

    private function __construct()
    {
    }

    /**
     *
     * @return EntityUnit
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Add entity that will be persisted
     *
     * @param IEntity $entity
     * @return void
     */
    public function addEntity(IEntity $entity)
    {
        $isEntityExist = false;
        foreach ($this->entities as $existedEntity) {
            if ($entity === $existedEntity) {
                $isEntityExist = true;
                break;
            }
        }

        if (!$isEntityExist) {
            $this->entities[] = $entity;
        }
    }

    /**
     * Persist all entities to table
     *
     * @return void
     */
    public function flush()
    {
        DbtransLib::beginTransaction();
        try {
            $entityManager = new EntityManager();
            foreach ($this->entities as $entity) {
                $entityManager->setEntity($entity)->persist();
            }
            DbtransLib::commit();
            $this->entities = [];
        } catch (Exception $e) {
            DbtransLib::rollback();
            throw $e;
        }
    }
}
