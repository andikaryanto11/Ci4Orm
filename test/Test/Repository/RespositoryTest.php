<?php

namespace Ci4OrmTest\Test\Repository;

use Codeception\Specify;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Ci4Orm\Repository\Repository;
use Ci4OrmTest\Entity\Transaction;

class RespositoryTest extends TestCase {
    use ProphecyTrait;
    use Specify;

    /**
     * @var Repository
     */
    protected Repository $repository;

    public function test()
    {
        $this->beforeSpecify(function () {

            $this->transaction = (new Transaction())
                ->setId(1)
                ->setNoOrder("NHYT-123345");


            $this->repository = new Repository(Transaction::class);
        });

        $this->describe('->newEntity()', function () {
            $entity = $this->repository->newEntity();
            expect($entity)->toBeInstanceOf(Transaction::class);
        });
    }
    
}