<?php

declare(strict_types=1);

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ActionInterface
{
    /**
     * The action's method itself.
     *
     * @param ServerRequestInterface $request
     * @param array $arguments Arguments from the routing system
     *
     * @return ResponseInterface This method should always return a Response
     */
    public function process(ServerRequestInterface $request, array $arguments): ResponseInterface;
}
