<?php

declare(strict_types=1);

namespace App\Responder;

use App\Responder\Payload\PayloadInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TwigResponder implements ResponderInterface
{
    /**
     * @var ResponderInterface
     */
    private $responder;

    public function __construct(ResponderInterface $responder)
    {
        $this->responder = $responder;
    }

    public function respond(ServerRequestInterface $request, PayloadInterface $payload): ResponseInterface
    {
        $response = $this->responder->respond($payload);

        if (!$payload instanceof TwigResponder) {
        }

        return $response;
    }
}
