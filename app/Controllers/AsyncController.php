<?php

namespace App\Controllers;

use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;

class AsyncController
{
    private ResponseFactory $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function index(): Response
    {
        return $this->responseFactory->view("async/index.html.twig");
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->get('id');
        return $this->responseFactory->view("async/show.html.twig", ['id' => $id]);
    }
}
