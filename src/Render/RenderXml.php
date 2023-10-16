<?php

namespace SFW2\Routing\Render;

use Handlebars\Handlebars;
use Handlebars\Loader;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class RenderXml implements RenderInterface
{
    protected Handlebars $handlebars;

    public function __construct(Loader $loader)
    {
        $this->handlebars = new Handlebars([
            "loader" => $loader,
            "partials_loader" => $loader
        ]);
    }

    public function render(Request $request, Response $response, string $template, array $data = []): Response
    {
        if ($request->getHeaderLine('Accept') !== 'application/xml') {
            return $response;
        }

        $payload =
            '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL .
            $this->handlebars->render($template, $data);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'text/xml');
    }
}