<?php

namespace SFW2\Routing\Render;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

interface RenderInterface
{
    public function render(Request $request, Response $response, string $template, array $data = []): Response;
}