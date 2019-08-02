<?php

declare(strict_types=1);

namespace App\Exception\Action;

use App\Action\ActionInterface;
use App\Exception\ExceptionInterface;

class InvalidActionTypeException extends InvalidActionException implements ExceptionInterface
{
    public function __construct(object $action, $code = 0, \Throwable $previous = null)
    {
        parent::__construct(
            $action,
            sprintf("The action must implements the %s interface.", ActionInterface::class),
            $code,
            $previous
        );
    }
}
