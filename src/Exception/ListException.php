<?php

namespace AndikAryanto11\Exception;

use Exception;

class ListException extends Exception {

    protected $message = "";
    
    public function __construct($message)
    {
        $this->message = $message;
    }


}