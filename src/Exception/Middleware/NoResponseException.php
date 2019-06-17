<?php

declare(strict_types=1);

namespace App\Exception\Middleware;

use App\Exception\ExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class NoResponseException extends \LogicException implements ExceptionInterface
{
    public function __construct($code = 0, \Throwable $previous = null)
    {
        $message = sprintf(
            'The last middleware should return an object of type "%s"',
            ResponseInterface::class
        );

        parent::__construct($message, $code, $previous);
    }
}
