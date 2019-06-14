<?php

namespace App\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class MainController
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

    public function index()
    {
        return $this
            ->responseFactory
            ->createResponse()
            ->withBody($this->streamFactory->createStream('cool'));
    }

    public function name(string $name)
    {
        return $this
            ->responseFactory
            ->createResponse()
            ->withBody($this->streamFactory->createStream($name));
    }
}
