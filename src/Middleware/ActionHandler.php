<?php

declare(strict_types=1);

namespace App\Middleware;

use PsrFramework\Exception\Action\ActionNotFoundException;
use PsrFramework\Exception\Action\InvalidActionReturnTypeException;
use PsrFramework\Exception\Action\InvalidActionTypeException;
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
    /**
     * @var ContainerInterface
     */
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
     *
     * @throws \ReflectionException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $action = $request->getAttribute('_action');
        if (!$this->container->has($action)) {
            throw new ActionNotFoundException(
                $action ?? 'NULL',
                $request->getAttribute('_route') ?? 'NULL'
            );
        }

        $action = $this->container->get($action);
        if (false === \is_callable($action)) {
            throw new InvalidActionTypeException($action);
        }

        $actionClosure = \Closure::fromCallable($action);
        $arguments = $this->extractActionArgumentsFromRequest($actionClosure, $request);

        $response = $actionClosure(...$arguments);
        if (!$response instanceof ResponseInterface) {
            throw new InvalidActionReturnTypeException($action);
        }

        return $response;
    }

    /**
     * Extract the action arguments from the current request
     *
     * @param \Closure $action                The action as a closure
     * @param ServerRequestInterface $request The current request can be injected as parameters of the action if one of
     *                                        its arguments is type-hinted with the ServerRequestInterface
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    private function extractActionArgumentsFromRequest(\Closure $action, ServerRequestInterface $request): array
    {
        $parameters = $request->getAttribute('_route_parameters') ?? [];

        $reflection = new \ReflectionFunction($action);
        foreach ($reflection->getParameters() as $actionArgument) {
            if (array_key_exists($actionArgument->getName(), $parameters)) {
                $arguments[] = $parameters[$actionArgument->getName()];
                continue;
            }

            if ($type = $actionArgument->getType()) {
                if (ServerRequestInterface::class === $type->getName()) {
                    $arguments[] = $request;
                }
            }
        }

        return $arguments ?? [];
    }
}
