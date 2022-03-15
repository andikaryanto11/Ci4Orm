<?php

namespace Ci4OrmTest\Test\Repository;

use Ci4Orm\Entities\EntityList;
use Ci4Orm\Entities\MappingReader;
use Ci4Orm\Entities\ORM;
use Ci4Orm\Exception\EntityException;
use Codeception\Specify;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Ci4Orm\Repository\Repository;
use Ci4OrmTest\Entity\Transaction;
use Mockery;
use stdClass;

class RespositoryTest extends TestCase
{
    use ProphecyTrait;
    use Specify;

    /**
     * @var Repository
     */
    protected Repository $repository;



    public function parseEntity()
    {
        return MappingReader::read('test/Entity/Mapping');
    }

    public function props()
    {
        foreach ($this->parseEntity() as $key => $item) {
            if (Transaction::class == $key) {
                return $item;
            }
        }
    }

    public function selectColumns()
    {
        $columns = [];
        foreach ($this->parseEntity() as $key => $item) {
            if (Transaction::class == $key) {
                foreach ($item['props'] as $propKey => $prop) {
                    if (!$prop['isEntity']) {
                        $columns[] = $item['table'] . '.' . $propKey;
                    } else {
                        if ($prop['relationType'] != 'many_to_one')
                            $columns[] = $item['table'] . '.' . $prop['foreignKey'];
                    }
                }
                return $columns;
            }
        }
    }

    public function mock(){

        $orm = Mockery::mock('alias:' . ORM::class);
        $database = Mockery::mock('alias:\Config\Database');
        $connection = Mockery::mock('alias:\CodeIgniter\Database\BaseConnection');
        $this->builder = Mockery::mock('alias:\CodeIgniter\Database\BaseBuilder');

        $connection->shouldReceive('table')->with($this->props()['table'])->andReturn($this->builder);
        $orm->shouldReceive('getProps')->with(Transaction::class)->andReturn($this->props());
        $orm->shouldReceive('getSelectColumns')->with(Transaction::class)->andReturn($this->selectColumns());
        $database->shouldReceive('connect')->andReturn($connection);
        $this->repository = new Repository(Transaction::class);
    }

    public function testGetProps()
    {
        $this->mock();

        $props = $this->repository->getProps();
        expect($props)->toEqual($this->props());
    }

    public function testNewEntity()
    {
        $this->mock();

        $props = $this->repository->newEntity();
        expect($props)->toBeInstanceOf(Transaction::class);
    }

    public function testCollect()
    {

        $Transaction = new stdClass();
        $Transaction->Id = 1;
        $Transaction->NoOrder = 'AJDW-12345';

        $Transaction2 = new stdClass();
        $Transaction2->Id = 2;
        $Transaction2->NoOrder = 'AJDW-123456';
        $this->mock();

        $this->builder->shouldReceive('select')->andReturn($this->builder);
        $this->builder->shouldReceive('get')->andReturn($this->builder);
        $this->builder->shouldReceive('getResult')->andReturn([$Transaction, $Transaction2]);

        $entities = $this->repository->collect();
        expect($entities)->toBeInstanceOf(EntityList::class);
        expect($entities->getListOf())->toEqual(Transaction::class);
    }

    public function testFind()
    {

        $Transaction = new stdClass();
        $Transaction->Id = 1;
        $Transaction->NoOrder = 'AJDW-12345';

        $this->mock();

        $this->builder->shouldReceive('where')->with(['Id' => 1])->andReturn($this->builder);
        $this->builder->shouldReceive('select')->andReturn($this->builder);
        $this->builder->shouldReceive('get')->andReturn($this->builder);
        $this->builder->shouldReceive('getResult')->andReturn([$Transaction]);

        $entities = $this->repository->find(1);
        expect($entities)->toBeInstanceOf(Transaction::class);
    }

    /**
     * will return new object
     *
     * @return void
     */
    public function testFindOrNew()
    {

        $this->mock();

        $this->builder->shouldReceive('where')->with(['Id' => 1])->andReturn($this->builder);
        $this->builder->shouldReceive('select')->andReturn($this->builder);
        $this->builder->shouldReceive('get')->andReturn($this->builder);
        $this->builder->shouldReceive('getResult')->andReturn([]);

        $entities = $this->repository->findOrNew(1);
        expect($entities)->toBeInstanceOf(Transaction::class);
        expect($entities->getId())->toEqual(0);
    }

    /**
     * will throw error
     *
     * @return void
     */
    public function testFindOrFail()
    {

        $this->mock();

        $this->builder->shouldReceive('where')->with(['Id' => 1])->andReturn($this->builder);
        $this->builder->shouldReceive('select')->andReturn($this->builder);
        $this->builder->shouldReceive('get')->andReturn($this->builder);
        $this->builder->shouldReceive('getResult')->andReturn([]);

        try{
            $entities = $this->repository->findOrFail(1);
        } catch (EntityException $e){
            expect($e->getMessage())->toEqual('Data with id 1 not found');
        }
       
    }


    /**
     * will throw error
     *
     * @return void
     */
    public function testFindOne()
    {

        $Transaction = new stdClass();
        $Transaction->Id = 1;
        $Transaction->NoOrder = 'AJDW-12345';

        $Transaction2 = new stdClass();
        $Transaction2->Id = 2;
        $Transaction2->NoOrder = 'AJDW-123456';
        
        $this->mock();

        $this->builder->shouldReceive('select')->andReturn($this->builder);
        $this->builder->shouldReceive('get')->andReturn($this->builder);
        $this->builder->shouldReceive('getResult')->andReturn([$Transaction, $Transaction2]);

        $entities = $this->repository->findOne();
        expect($entities)->toBeInstanceOf(Transaction::class);
       
    }
}
