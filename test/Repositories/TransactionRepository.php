<?php

namespace Ci4OrmTest\Repositories;

use Ci4Orm\Repository\Repository;
use Ci4OrmTest\Entity\Transaction;

class TransactionRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(Transaction::class);
    }
}
