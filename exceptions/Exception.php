<?php

namespace app\core\exceptions;

use Exception as GlobalException;

class Exception extends GlobalException
{
    public function __construct($msg, $code, $pThrow = null)
    {
        parent::__construct($msg, $code, $pThrow);
    }
}