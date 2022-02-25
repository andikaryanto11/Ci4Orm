<?php

namespace Ci4Orm\Exception;

use Exception;

class ListException extends Exception {

    protected $message = "";
    
    public function __construct($message)
    {
        $this->message = $message;
    }


}