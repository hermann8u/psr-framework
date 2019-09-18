<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exception\Action\ActionNotFoundException;
use App\Exception\Action\InvalidActionTypeException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The action handler try to execute an action based on the request and return the response from it.
 * That's why this middleware has to be the last of the stack.
 */
final class ActionHandler implements MiddlewareInterface
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $action = $request->getAttribute('action');
        if (!$this->container->has($action)) {
            throw new ActionNotFoundException(
                $action,
                $request->getAttribute('route')
            );
        }

        $action = $this->container->get($action);
        if (!$action instanceof RequestHandlerInterface) {
            throw new InvalidActionTypeException($action);
        }

        return $action->handle($request);
    }
}
