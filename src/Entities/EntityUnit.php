<?php

namespace Ci4Orm\Entities;

use Ci4Common\Libraries\DbtransLib;
use Ci4Common\Services\DbTransService;
use Ci4Orm\Interfaces\IEntity;
use Exception;

class EntityUnit
{
    /**
     * Prepare entity that will be persisted. Will persisted after entity unit flush
     *
     * @param IEntity $entity
     * @return EntityManager
     */
    public function preparePersistence(IEntity $entity)
    {
        $entityUnit = EntityScope::getInstance();
        $entityUnit->addEntity(EntityScope::PERFORM_ADD_UPDATE, $entity);
        return $this;
    }

    /**
     * Prepare entity that will be removed. Will removed after entity unit flush
     *
     * @param IEntity $entity
     * @return EntityManager
     */
    public function prepareRemove(IEntity $entity)
    {
        $entityUnit = EntityScope::getInstance();
        $entityUnit->addEntity(EntityScope::PERFORM_DELETE, $entity);
        return $this;
    }

    /**
     * Persist all entities to table
     *
     * @return void
     */
    public function flush()
    {
        $entityScope = EntityScope::getInstance();

        $entityManager = new EntityManager();
        $entityManager->beginTransaction();

        try {
            $entityScope->sort();
            foreach ($entityScope->getEntities() as $key => $indexValue) {
                if ($key == EntityScope::PERFORM_ADD_UPDATE) {
                    foreach ($indexValue as $entity) {
                        $entityManager->persist($entity);
                    }
                } else {
                    foreach ($indexValue as $entity) {
                        $entityManager->remove($entity);
                    }
                }
            }
            $entityManager->commit();
            $entityScope->clean();
        } catch (Exception $e) {
            $entityManager->rollback();
            throw $e;
        }
    }
}
