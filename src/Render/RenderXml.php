<?php

namespace SFW2\Routing\Render;

use Handlebars\Handlebars;
use Handlebars\Loader;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SFW2\Routing\HelperTraits\getRequestTypeTrait;

final class RenderXml implements RenderInterface
{
    use getRequestTypeTrait;

    public function __construct(
        private readonly Handlebars $handlebars)
    {
    }

    public function render(Request $request, Response $response, array $data = [], ?string $template = null): Response
    {
        if (!$this->isXmlRequest($request)) {
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