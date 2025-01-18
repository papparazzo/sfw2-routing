<?php

namespace SFW2\Routing\Render;

use JsonException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class RenderJson implements RenderInterface
{
    public function __construct(
        private readonly bool $appendAttributes = false
    ) {
    }

    /**
     * @throws JsonException
     */
    public function render(Request $request, Response $response, array $data = [], ?string $template = null): Response
    {
        $data = $this->appendAttributes ? array_merge($request->getAttributes(), $data) : $data;
        $payload = json_encode($data, JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}