<?php

namespace Ci4OrmTest\Test\Repository;

use Ci4Orm\Entities\EntityList;
use Ci4Orm\Entities\EntityLooper;
use Ci4OrmTest\Entity\Transaction;
use Codeception\Verify\Expectations\ExpectXmlFile;
use Mockery;
use PHPUnit\Framework\TestCase;

class EntityLooperTest extends TestCase
{
    /**
     * @var EntityLooper
     */
    protected EntityLooper $looper;

    public function setUp(): void
    {

        $transaction1 = (new Transaction())->setId(1)->setNoOrder('HKM-12345');
        $transaction2 = (new Transaction())->setId(2)->setNoOrder('HKM-12346');
        $entityList = new EntityList([$transaction1, $transaction2]);
        $this->looper = EntityLooper::getInstance(Transaction::class);
        $this->looper->setEntityList($entityList);
    }

    /**
     * Test has entity list
     * @return void
     */
    public function testHasEntityList()
    {
        $hasEntityList = $this->looper->hasEntityList();
        expect($hasEntityList)->toEqual(true);
    }
}
