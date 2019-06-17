<?php

declare(strict_types=1);

namespace App\Exception\Action;

use App\Exception\ExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class InvalidActionReturnTypeException extends InvalidActionException implements ExceptionInterface
{
    public function __construct(object $action, $code = 0, \Throwable $previous = null)
    {
        parent::__construct(
            $action,
            sprintf('It should return a response of type "%s"', ResponseInterface::class),
            $code,
            $previous
        );
    }
}
