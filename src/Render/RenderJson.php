<?php

namespace SFW2\Routing\Render;

use JsonException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class RenderJson implements RenderInterface
{
    /**
     * @throws JsonException
     */
    public function render(Request $request, Response $response, array $data = [], ?string $template = null): Response
    {
        if ($request->getHeaderLine('Accept') !== 'application/json') {
            return $response;
        }

        $data = array_merge($request->getAttributes(), $data);

        $payload = json_encode($data, JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
   }
}