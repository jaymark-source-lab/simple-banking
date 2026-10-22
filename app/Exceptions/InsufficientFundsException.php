<?php

namespace App\Exceptions;

use Exception;

class InsufficientFundsException extends Exception
{
    protected $message = 'Insufficient funds: this withdrawal would drop the balance below the minimum.';
}