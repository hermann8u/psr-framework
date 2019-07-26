<?php

declare(strict_types=1);

namespace App\Responder;

use App\Responder\Payload\PayloadInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ResponderInterface
{
    public function respond(ServerRequestInterface $request, PayloadInterface $payload): ResponseInterface;
}
