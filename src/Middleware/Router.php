<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * The router try to find an action based on the request and populates the request attributes with it.
 */
final class Router implements MiddlewareInterface
{
    /**
     * @var UrlMatcherInterface
     */
    private $matcher;

    public function __construct(UrlMatcherInterface $matcher)
    {
        $this->matcher = $matcher;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     *
     * @throws NoConfigurationException   If no routing configuration could be found
     * @throws ResourceNotFoundException  If the resource could not be found
     * @throws MethodNotAllowedException  If the resource was found but the request method is not allowed
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $parameters = $this->matcher->match($request->getUri()->getPath());

        $route = $parameters['_route'];
        $action = $parameters['_controller'] ?? null;

        unset($parameters['_route']);
        unset($parameters['_controller']);

        return $handler->handle($request
            ->withAttribute('route', $route)
            ->withAttribute('route_parameters', $parameters)
            ->withAttribute('action', $action));
    }
}
