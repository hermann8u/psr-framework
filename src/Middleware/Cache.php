<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Cache request to improve performance.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Cache implements MiddlewareInterface
{
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cacheKey = sha1($request->getUri()->getPath());

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($handler, $request) {
            $item->expiresAfter(3600);

            return $handler->handle($request);
        });
    }
}
