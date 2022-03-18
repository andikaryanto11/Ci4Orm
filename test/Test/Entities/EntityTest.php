<?php

namespace Ci4OrmTest\Test\Repository;

use Ci4OrmTest\Entity\Transaction;
use Mockery;
use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    /**
     * @var Transaction
     */
    protected Transaction $entity;

    public function setUp(): void
    {
        $configEntity = Mockery::mock('alias:\Config\Entity');
        $configEntity->shouldReceive('register')->andReturn('test/Entity/Mapping');

        $this->entity = new Transaction();
    }

    public function testGetPrimaryKeyName()
    {

        $primaryKey = $this->entity->getPrimaryKeyName();

        expect($primaryKey)->toEqual('Id');
    }

    public function testGetTableName()
    {
        $primaryKey = $this->entity->getTableName();
        expect($primaryKey)->toEqual('transaction');
    }
}
