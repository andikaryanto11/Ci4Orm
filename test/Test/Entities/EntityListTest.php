<?php

namespace Ci4OrmTest\Test\Repository;

use Ci4Orm\Entities\EntityList;
use Ci4OrmTest\Entity\Transaction;
use Mockery;
use PHPUnit\Framework\TestCase;

class EntityListTest extends TestCase
{
    /**
     * @var EntityList
     */
    protected EntityList $entityList;

    public function setUp(): void
    {

        $configEntity = Mockery::mock('alias:\Config\Entity');
        $configEntity->shouldReceive('register')->andReturn('test/Entity/Mapping');

        $Transaction = (new Transaction())
            ->setId(1)
            ->setNoOrder('AHM-12345');

        $Transaction2 = (new Transaction())
            ->setId(2)
            ->setNoOrder('AHM-123456');

        $Transaction3 = (new Transaction())
            ->setId(3)
            ->setNoOrder('AHM-123456');

        $Transaction4 = (new Transaction())
            ->setId(0)
            ->setNoOrder('AHM-123457');

        $items = [
            $Transaction,
            $Transaction2,
            $Transaction3,
            $Transaction4
        ];

        $this->entityList = (new EntityList($items));
    }

    /**
     * will uniq the intended field
     * @return void
     */
    public function testChunk()
    {

        $chunk = $this->entityList->chunk('NoOrder');
        expect($chunk)->toEqual(['AHM-12345', 'AHM-123456', 'AHM-123456', 'AHM-123457']);
    }

    /**
     * will uniq the intended field
     * @return void
     */
    public function testChunkUnique()
    {

        $uniq = $this->entityList->chunkUnique('NoOrder');
        expect($uniq)->toEqual(['AHM-12345', 'AHM-123456', 'AHM-123457']);
    }

    /**
     * will return the unsaved data only
     * @return void
     */
    public function testUnSaved()
    {

        $unsaved = $this->entityList->unSaved();
        expect(count($unsaved))->toEqual(1);
    }

    /**
     * will return the saved data only
     * @return void
     */
    public function testSaved()
    {

        $unsaved = $this->entityList->saved();
        expect(count($unsaved))->toEqual(3);
    }

    /**
     * will return sum of the column
     * @return void
     */
    public function testSum()
    {

        $sum = $this->entityList->sum('Id');
        expect($sum)->toEqual(6);
    }

    /**
     * will return average of the column
     * @return void
     */
    public function testAvg()
    {

        $sum = $this->entityList->avg('Id');
        expect($sum)->toEqual(1.5);
    }

    /**
     * will return minimum value
     * @return void
     */
    public function testMin()
    {

        $sum = $this->entityList->min('Id', 'field');
        expect($sum)->toEqual(0);
    }

    /**
     * will return max value
     * @return void
     */
    public function testMax()
    {

        $sum = $this->entityList->max('Id', 'field');
        expect($sum)->toEqual(3);
    }
}
