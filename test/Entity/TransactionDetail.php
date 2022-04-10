<?php

namespace Ci4OrmTest\Entity;

use Ci4Orm\Entities\Entity;
use Ci4Orm\Entities\EntityList;

class TransactionDetail extends Entity
{
     /**
     * @var int
     */
    private int $Id = 0;

    /**
     *
     * @var string|null
     */
    private ?string $ItemName = null;

    /**
     *
     * @var Transaction|null
     */
    private ?Transaction $Transaction = null;

    /**
     *
     * @param integer $Id
     * @return TransactionDetail
     */
    public function setId(int $Id)
    {
        $this->Id = $Id;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->Id;
    }

    /**
     *
     * @param string $NoOrder
     * @return TransactionDetail
     */
    public function setItemName(string $ItemName)
    {
        $this->ItemName = $ItemName;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getItemName(): ?string
    {
        return $this->ItemName;
    }

    /**
     *
     * @param Transaction $Transaction
     * @return TransactionDetail
     */
    public function setTransaction(Transaction $Transaction)
    {
        $this->Transaction = $Transaction;
        return $this;
    }

    /**
     *
     * @return Transaction
     */
    public function getTransaction(): ?Transaction
    {
        return $this->Transaction;
    }
}
