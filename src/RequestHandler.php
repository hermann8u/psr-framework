<?php

declare(strict_types=1);

namespace App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface
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
            throw new \LogicException('The last middleware did not return a response.');
        }

        return $middleware->process($request, $this);
    }
}