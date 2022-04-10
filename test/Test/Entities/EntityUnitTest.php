<?php

namespace Ci4OrmTest\Test\Repository;

use Ci4Orm\Entities\EntityManager;
use Ci4Orm\Entities\EntityScope;
use Ci4Orm\Entities\EntityUnit;
use Ci4OrmTest\Entity\Transaction;
use Ci4OrmTest\Entity\TransactionDetail;
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
        $this->baseConnection = Mockery::mock('alias:\CodeIgniter\Database\BaseConnection');
        $this->baseBuilder = Mockery::mock('alias:\CodeIgniter\Database\BaseBuilder');
        $this->baseResult = Mockery::mock('alias:\CodeIgniter\Database\BaseResult');
        $configDatabase = Mockery::mock('alias:\Config\Database');
        $this->entityManager = Mockery::mock(EntityManager::class);

        $this->baseConnection->shouldReceive('table')->andReturn($this->baseBuilder);
        $this->baseConnection->shouldReceive('transStart')->andReturn($this->baseBuilder);
        $this->baseConnection->shouldReceive('commit')->andReturn($this->baseBuilder);
        $configDatabase->shouldReceive('connect')->andReturn($this->baseConnection);

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

    public function testFlush_persist()
    {

        $transaction1 = (new Transaction())
            ->setNoOrder('NO_ORDER1');
        $transaction2 = (new Transaction())
            ->setNoOrder('NO_ORDER2');


        $transactionDetail1_1 = (new TransactionDetail())
            ->setTransaction($transaction1)
            ->setItemName('Candy');

        $transactionDetail2_1 = (new TransactionDetail())
            ->setTransaction($transaction2)
            ->setItemName('Chitos');

        $transactionDetail2_2 = (new TransactionDetail())
            ->setTransaction($transaction2)
            ->setItemName('Chitato');

        $entityScope = EntityScope::getInstance();
        $entityScope->addEntity(EntityScope::PERFORM_ADD_UPDATE, $transaction1);
        $entityScope->addEntity(EntityScope::PERFORM_ADD_UPDATE, $transaction2);
        $entityScope->addEntity(EntityScope::PERFORM_ADD_UPDATE, $transactionDetail1_1);
        $entityScope->addEntity(EntityScope::PERFORM_ADD_UPDATE, $transactionDetail2_1);
        $entityScope->addEntity(EntityScope::PERFORM_ADD_UPDATE, $transactionDetail2_2);


        $this->baseBuilder->shouldReceive('set')->times(5)->andReturn($this->baseBuilder);
        $this->baseBuilder->shouldReceive('insert')->times(5)->andReturn($this->baseResult);
        $this->baseConnection->shouldReceive('insertID')->times(5)->andReturn(1, 2, 1, 2, 3);

        $return = $this->entityUnit->flush();
        expect($transaction1->getId())->toEqual(1);
        expect($transaction2->getId())->toEqual(2);
        expect($transactionDetail1_1->getId())->toEqual(1);
        expect($transactionDetail2_1->getId())->toEqual(2);
        expect($transactionDetail2_2->getId())->toEqual(3);
        expect($return)->toBeNull();
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
