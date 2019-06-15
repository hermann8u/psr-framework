<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * This middleware should be the first of the stack.
 *
 * @see https://www.php-fig.org/psr/psr-15/#14-handling-exceptions
 */
final class ExceptionHandler implements MiddlewareInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var string
     */
    private $environment;

    public function __construct(ResponseFactoryInterface $responseFactory, string $environment)
    {
        $this->environment = $environment;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (\Throwable $exception) {
            if ('prod' !== $this->environment) {
                throw $exception;
            }

            $response = $this->responseFactory->createResponse(500);
        }

        return $response;
    }
}
