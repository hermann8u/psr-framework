<?php

declare(strict_types=1);

namespace App\Action;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class HelloWorldAction implements ActionInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    public function __construct(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
    }

    public function process(ServerRequestInterface $request, array $arguments): ResponseInterface
    {
        return $this
            ->responseFactory
            ->createResponse()
            ->withBody($this
                ->streamFactory
                ->createStream(sprintf("Hello %s", $arguments['name'] ?: 'world')));
    }
}
