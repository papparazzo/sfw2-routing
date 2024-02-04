<?php

namespace SFW2\Routing\Render;

use Handlebars\Handlebars;
use Handlebars\Loader;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SFW2\Routing\HelperTraits\getRequestTypeTrait;

final class RenderHtml implements RenderInterface
{
    use getRequestTypeTrait;

    public function __construct(
        private readonly Handlebars $handlebars, private readonly string $skeleton)
    {
    }

    public function render(Request $request, Response $response, array $data = [], ?string $template = null): Response
    {
        if ($this->isAjaxRequest($request)) {
            return $response;
        }

        $data = array_merge($request->getAttributes(), $data);

        if($template !== null) {
            $payload = $this->handlebars->render($template, $data);
            $data['content'] = $payload;
        }
        $payload = $this->handlebars->render($this->skeleton, $data);
        $response->getBody()->write($payload);

        return $response;
    }
}