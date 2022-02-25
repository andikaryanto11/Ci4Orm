<?php

namespace Ci4Orm\Exception;

use Exception;

class DatabaseException extends Exception
{
     protected $message = null;
     public function __construct($message)
     {
          parent::__construct($message);
          $this->message = $message;
     }
}
