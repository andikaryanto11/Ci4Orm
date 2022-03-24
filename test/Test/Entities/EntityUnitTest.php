<?php

namespace Ci4OrmTest\Test\Repository;

use Ci4Orm\Entities\EntityManager;
use Ci4Orm\Entities\EntityScope;
use Ci4Orm\Entities\EntityUnit;
use Ci4OrmTest\Entity\Transaction;
use Mockery;
use PHPUnit\Framework\TestCase;

class EntityUnitTest extends TestCase
{
    /**
     * @var EntityUnit
     */
    protected EntityUnit $entityUnit;

    public function setUp(): void
    {
        $configEntity = Mockery::mock('alias:\Config\Entity');
        $DbtransLib = Mockery::mock('alias:\Ci4Common\Libraries\DbtransLib');
        $this->baseConnection = Mockery::mock('alias:\CodeIgniter\Database\BaseConnection');
        $this->baseBuilder = Mockery::mock('alias:\CodeIgniter\Database\BaseBuilder');
        $this->baseResult = Mockery::mock('alias:\CodeIgniter\Database\BaseResult');
        $configDatabase = Mockery::mock('alias:\Config\Database');
        $this->entityManager = Mockery::mock(EntityManager::class);

        $this->baseConnection->shouldReceive('table')->andReturn($this->baseBuilder);
        $configDatabase->shouldReceive('connect')->andReturn($this->baseConnection);
        $DbtransLib->shouldReceive('beginTransaction');
        $DbtransLib->shouldReceive('commit');

        $configEntity->shouldReceive('register')->andReturn('test/Entity/Mapping');

        $this->entityManager->shouldReceive('beginTransaction');
        $this->entityManager->shouldReceive('commit');

        $this->entityUnit = new EntityUnit();
    }

    public function testFlush_delete()
    {

        $transaction1 = (new Transaction())
            ->setId(1)
            ->setNoOrder('NO_ORDER1');

        $entityScope = EntityScope::getInstance();
        $entityScope->addEntity(EntityScope::PERFORM_DELETE, $transaction1);

        $this->baseBuilder->shouldReceive('where')->once()->with('Id', 1)->andReturn($this->baseBuilder);
        $this->baseBuilder->shouldReceive('delete')->once()->andReturn(true);

        $return = $this->entityUnit->flush();

        expect($return)->toBeNull();
    }

    public function testFlush_perist()
    {

        $transaction1 = (new Transaction())
            ->setNoOrder('NO_ORDER1');
        $transaction2 = (new Transaction())
            ->setNoOrder('NO_ORDER2');

        $entityScope = EntityScope::getInstance();
        $entityScope->addEntity(EntityScope::PERFORM_ADD_UPDATE, $transaction1);
        $entityScope->addEntity(EntityScope::PERFORM_ADD_UPDATE, $transaction2);


        $this->baseBuilder->shouldReceive('set')->twice()->andReturn($this->baseBuilder);
        $this->baseBuilder->shouldReceive('insert')->twice()->andReturn($this->baseResult);
        $this->baseConnection->shouldReceive('insertID')->twice()->andReturn(1, 2);

        $return = $this->entityUnit->flush();
        expect($transaction1->getId())->toEqual(1);
        expect($transaction2->getId())->toEqual(2);
        expect($return)->toBeNull();
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
