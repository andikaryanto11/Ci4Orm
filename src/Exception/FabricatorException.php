<?php

namespace Ci4Orm\Exception;

use Exception;

class FabricatorException extends Exception
{
    protected $message = null;
    public function __construct($message)
    {
        parent::__construct($message);
        $this->message = $message;
    }
}
