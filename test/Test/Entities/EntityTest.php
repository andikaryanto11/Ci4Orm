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

    public function testGetPrimaryKeyName(){

        $configEntity = Mockery::mock('alias:\Config\Entity');
        $configEntity->shouldReceive('register')->andReturn('test/Entity/Mapping');
        
        $entity = new Transaction();
        $primaryKey = $entity->getPrimaryKeyName();

        expect($primaryKey)->toEqual('Id');
    }
}