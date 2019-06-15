<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * The router try to find an action to execute based on the Request, execute it and return its Response.
 */
final class Router implements MiddlewareInterface
{
    const CONTROLLER_METHOD_SEPARATOR = '::';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var UrlMatcherInterface
     */
    private $matcher;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(
        ContainerInterface $container,
        UrlMatcherInterface $matcher,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->container = $container;
        $this->matcher = $matcher;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $parameters = $this->matcher->match($request->getUri()->getPath());
        } catch (ResourceNotFoundException $e) {
            return $this->responseFactory->createResponse(404, 'Not Found');
        }

        $class = $parameters['_controller'];

        if (!$this->container->has($class)) {
            throw new \LogicException(sprintf(
                'Invalid route config for route "%s". Action "%s" not found.',
                $parameters['_route'],
                $class
            ));
        }

        $action = $this->container->get($class);
        $arguments = $this->getArguments($request, $action, $parameters);

        return $action(...$arguments);
    }

    /**
     * Get the action arguments
     *
     * @param RequestInterface $request
     * @param callable $action The callable action formatted as array
     * @param array $parameters The parameters extract by the router
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    private function getArguments(RequestInterface $request, callable $action, array $parameters): array
    {
        $reflection = new \ReflectionMethod($action, '__invoke');
        foreach ($reflection->getParameters() as $param) {
            if (isset($parameters[$param->getName()])) {
                $arguments[] = $parameters[$param->getName()];
                continue;
            }

            if ($type = $param->getType()) {
                if (RequestInterface::class === $type->getName()) {
                    $arguments[] = $request;
                }
            }
        }

        return $arguments ?? [];
    }
}
