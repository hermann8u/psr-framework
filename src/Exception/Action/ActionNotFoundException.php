<?php

declare(strict_types=1);

namespace App\Exception\Action;

use App\Exception\ExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class ActionNotFoundException extends \InvalidArgumentException implements ExceptionInterface, NotFoundExceptionInterface
{
    /** @var string */
    private $action;

    /** @var string */
    private $route;

    /** @var string[] */
    private $availableActions;

    public function __construct(?string $action, ?string $route, array $availableActions = [], $code = 0, Throwable $previous = null)
    {
        $message = sprintf(
            'Action "%s" not found for route "%s"',
            $action ?? 'NULL',
            $route ?? 'NULL'
        );

        parent::__construct($message, $code, $previous);

        $this->action = $action;
        $this->route = $route;
        $this->availableActions = $availableActions;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getAvailableActions(): array
    {
        return $this->availableActions;
    }
}
