<?php

declare(strict_types=1);

namespace App\Responder\Payload;

use Psr\Http\Message\ServerRequestInterface;

class TwigPayload implements PayloadInterface
{
    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $data;

    public function __construct(ServerRequestInterface $request, string $template, array $data = [])
    {
        $this->request = $request;
        $this->template = $template;
        $this->data = $data;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
