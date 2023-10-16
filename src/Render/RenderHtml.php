<?php

namespace SFW2\Routing\Render;

use Handlebars\Handlebars;
use Handlebars\Loader;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class RenderHtml implements RenderInterface
{
    protected Handlebars $handlebars;

    public function __construct(Loader $loader, private readonly string $skeleton)
    {
        $this->handlebars = new Handlebars([
            "loader" => $loader,
            "partials_loader" => $loader
        ]);
    }

    public function render(Request $request, Response $response, string $template, array $data = []): Response
    {
        if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            return $response;
        }

        $payload = $this->handlebars->render($template, $data);
        $payload = $this->handlebars->render($this->skeleton, ['content' => $payload]);
        
        $response->getBody()->write($payload);

        return $response;
    }
}