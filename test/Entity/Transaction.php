<?php

namespace Ci4OrmTest\Entity;

use Ci4Orm\Entities\Entity;
use Ci4Orm\Entities\EntityList;

class Transaction extends Entity {
    
     /**
     * @var int
     */
    private int $Id = 0;

    /**
     *
     * @var string|null
     */
    private ?string $NoOrder = null;
    
    /**
     *
     * @var EntityList|null
     */
    private ?EntityList $TransactionDetails = null; 

    /**
     *
     * @param integer $Id
     * @return Transaction
     */
    public function setId(int $Id){
        $this->Id = $Id;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getId(): int{
        return $this->Id;
    }

    /**
     *
     * @param string $NoOrder
     * @return Transaction
     */
    public function setNoOrder(string $NoOrder){
        $this->NoOrder = $NoOrder;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getNoOrder(): ?string{
        return $this->NoOrder;
    }

    /**
     *
     * @param EntityList $TransactionDetails
     * @return Transaction
     */
    public function setTransactionDetails(EntityList $TransactionDetails){
        $this->TransactionDetails = $TransactionDetails;
        return $this;
    }

    /**
     *
     * @return EntityList
     */
    public function getTransactionDetails(): ?EntityList {
        return $this->TransactionDetails;
    }
}