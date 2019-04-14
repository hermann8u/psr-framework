<?php


namespace App\Controller;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

abstract class AbstractController
{
    /**
     * @var StreamFactoryInterface
     */
    private $streamFactoryInterface;

    /**
     * @required
     *
     * @param StreamFactoryInterface $streamFactory
     */
    public function setStreamFactoryInterface(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactoryInterface = $streamFactory;
    }

    public function render(string $content): StreamInterface
    {
        return $this->streamFactoryInterface->createStream($content);
    }
}