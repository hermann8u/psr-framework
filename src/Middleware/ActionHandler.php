<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exception\Action\ActionNotFoundException;
use App\Exception\Action\InvalidActionTypeException;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * The action handler try to execute an action based on the request and return the response from it.
 * That's why this middleware has to be the last of the stack.
 */
final class ActionHandler implements MiddlewareInterface
{
    /** @var ServiceProviderInterface */
    private $actionLocator;

    public function __construct(ServiceProviderInterface $actionLocator)
    {
        $this->actionLocator = $actionLocator;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $actionClassName = $request->getAttribute('action');

        try {
            $action = $this->actionLocator->get($actionClassName);
        } catch (NotFoundExceptionInterface $serviceNotFoundException) {
            throw new ActionNotFoundException(
                $actionClassName,
                $request->getAttribute('route'),
                array_keys($this->actionLocator->getProvidedServices())
            );
        }

        if (!$action instanceof RequestHandlerInterface) {
            throw new InvalidActionTypeException($action);
        }

        return $action->handle($request);
    }
}
