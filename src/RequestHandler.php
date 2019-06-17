<?php

declare(strict_types=1);

namespace App;

use App\Exception\Middleware\NoResponseException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Handles a server request and produces a response.
 *
 * An HTTP request handler process an HTTP request in order to produce an HTTP response.
 */
final class RequestHandler implements RequestHandlerInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    private $queue;

    public function __construct(MiddlewareInterface ...$queue)
    {
        $this->queue = $queue;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = array_shift($this->queue);

        if (null === $middleware) {
            throw new NoResponseException();
        }

        return $middleware->process($request, $this);
    }
}
