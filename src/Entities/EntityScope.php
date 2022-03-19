<?php

namespace Ci4Orm\Entities;

use Ci4Common\Libraries\DbtransLib;
use Ci4Common\Services\DbTransService;
use Ci4Orm\Interfaces\IEntity;
use Exception;

class EntityScope
{
    public const PERFORM_ADD_UPDATE = '1addUpdate';
    public const PERFORM_DELETE = '2delete';

    /**
     *
     * @var EntityScope|null
     */
    private static ?EntityScope $instance = null;

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
     * @return EntityScope
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
     * @param string $perfom
     * @param IEntity $entity
     * @return void
     */
    public function addEntity(string $perfom, IEntity $entity)
    {
        $isEntityExist = false;
        if (isset($this->entities[$perfom])) {
            foreach ($this->entities[$perfom] as $existedEntity) {
                if ($entity === $existedEntity) {
                    $isEntityExist = true;
                    break;
                }
            }
        }

        if (!$isEntityExist) {
            $this->entities[$perfom][] = $entity;
        }
    }

    /**
     * Sort the entities
     *
     * @return EntityScope
     */
    public function sort()
    {
        ksort($this->entities);
        return $this;
    }

    /**
     * Get entities scope
     *
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Clean entity scope
     *
     * @return void
     */
    public function clean()
    {
        $this->entities = [];
    }
}
