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
class Router implements MiddlewareInterface
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

        // We expect $parameters['_controller'] to contain a string on format "ControllerClass::action"
        if (!strpos($parameters['_controller'], self::CONTROLLER_METHOD_SEPARATOR)) {
            throw new \LogicException(sprintf(
                'Invalid route config for route "%s". "%s" expected.',
                $parameters['_route'],
                self::CONTROLLER_METHOD_SEPARATOR
            ));
        }

        list($class, $method) = explode(
            self::CONTROLLER_METHOD_SEPARATOR,
            $parameters['_controller'], 2
        );

        if (!$this->container->has($class)) {
            throw new \LogicException(sprintf(
                'Invalid route config for route "%s". Controller "%s" not found.',
                $parameters['_route'],
                $class
            ));
        }

        $action = [$this->container->get($class), $method];
        $arguments = $this->getArguments($request, $action, $parameters);

        return $action(...$arguments);
    }

    /**
     * Get the action arguments
     *
     * @param RequestInterface $request
     * @param array $action The callable action formatted as array
     * @param array $parameters The parameters extract by the router
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    private function getArguments(RequestInterface $request, array $action, array $parameters): array
    {
        $reflection = new \ReflectionMethod($action[0], $action[1]);
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
