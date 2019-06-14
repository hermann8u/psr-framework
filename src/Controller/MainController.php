<?php

namespace App\Controller;

use Psr\Http\Message\ResponseFactoryInterface;

class MainController
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function index()
    {
        return $this
            ->responseFactory
            ->createResponse()
            ->withBody($this->render('cool'));
    }

    public function name(string $name)
    {
        return $this
            ->responseFactory
            ->createResponse()
            ->withBody($this->render($name));
    }
}
