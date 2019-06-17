<?php

declare(strict_types=1);

namespace App\Exception\Action;

use App\Exception\ExceptionInterface;

class InvalidActionTypeException extends InvalidActionException implements ExceptionInterface
{
    public function __construct(object $action, $code = 0, \Throwable $previous = null)
    {
        parent::__construct(
            $action,
            'It should be a callable and implements the method "__invoke()"',
            $code,
            $previous
        );
    }
}
