<?php

namespace SFW2\Routing\Render;

use JsonException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class RenderJson implements RenderInterface
{
    public function render(Request $request, Response $response, string $template, array $data = []): Response
    {
        if ($request->getHeaderLine('Accept') !== 'application/json') {
            return $response;
        }
        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
   }
}