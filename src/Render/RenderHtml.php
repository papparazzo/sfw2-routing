<?php

namespace SFW2\Routing\Render;

use Handlebars\Handlebars;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SFW2\Routing\Helper\GetRequestType;

final class RenderHtml implements RenderInterface
{
    public function __construct(
        private readonly Handlebars $handlebars,
        private readonly string $skeleton,
        private readonly bool $appendAttributes = false
    ) {
    }

    public function render(Request $request, Response $response, array $data = [], ?string $template = null): Response
    {
        $data = $this->appendAttributes ? array_merge($request->getAttributes(), $data) : $data;
        if($template !== null) {
            $payload = $this->handlebars->render($template, $data);
            $data['content'] = $payload;
        }
        $payload = $this->handlebars->render($this->skeleton, $data);
        $response->getBody()->write($payload);

        return $response;
    }
}