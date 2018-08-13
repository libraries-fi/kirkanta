<?php

namespace App\Module\Ptv\Exception;

use GuzzleHttp\Exception\RequestException;
use RuntimeException;

class AuthenticationException extends RuntimeException
{
    public function __construct(RequestException $previous = null)
    {
        $message = 'Failed to authenticate with PTV';
        parent::__construct($message, 0, $previous);
    }
}
