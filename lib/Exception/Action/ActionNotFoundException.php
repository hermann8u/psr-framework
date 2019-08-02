<?php

declare(strict_types=1);

namespace PsrFramework\Exception\Action;

use PsrFramework\Exception\ExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class ActionNotFoundException extends \InvalidArgumentException implements ExceptionInterface, NotFoundExceptionInterface
{
    /**
     * @var string
     */
    private $action;

    /**
     * @var string
     */
    private $route;

    public function __construct(string $action, string $route, $code = 0, Throwable $previous = null)
    {
        $message = sprintf('Action "%s" not found for route "%s"', $action, $route);

        parent::__construct($message, $code, $previous);

        $this->action = $action;
        $this->route = $route;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }
}
