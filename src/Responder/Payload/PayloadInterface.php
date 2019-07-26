<?php

declare(strict_types=1);

namespace App\Responder\Payload;

use Psr\Http\Message\ServerRequestInterface;

interface PayloadInterface
{
    public function getRequest(): ServerRequestInterface;
}
