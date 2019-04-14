<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;

class MainController extends AbstractController
{
    public function index(ResponseInterface $response)
    {
        return $response->withBody($this->render('cool'));
    }

    public function name(ResponseInterface $response, string $name)
    {
        return $response->withBody($this->render($name));
    }
}