<?php

namespace SFW2\Routing\Render;

use Handlebars\Handlebars;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SFW2\Routing\Helper\GetRequestType;

final class RenderXml implements RenderInterface
{
    public function __construct(
        private readonly Handlebars $handlebars)
    {
    }

    public function render(Request $request, Response $response, array $data = [], ?string $template = null): Response
    {
        if (!GetRequestType::isXmlRequest($request)) {
            return $response;
        }

        $data = array_merge($request->getAttributes(), $data);

        $payload =
            '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL .
            '<div>' .
            $this->handlebars->render($template, $data) .
            '</div>';

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'text/xml');
    }
}