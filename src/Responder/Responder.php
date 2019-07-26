<?php

declare(strict_types=1);

namespace App\Responder;

use App\Responder\Payload\PayloadInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Responder implements ResponderInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function respond(ServerRequestInterface $request, PayloadInterface $payload): ResponseInterface
    {
        return $this->responseFactory->createResponse();
    }
}
