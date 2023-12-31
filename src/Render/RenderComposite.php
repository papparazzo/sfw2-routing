<?php

namespace SFW2\Routing\Render;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class RenderComposite implements RenderInterface
{
    /**
     * @var RenderInterface[]
     */
    protected array $engines = [];

    public function add(RenderInterface $engine): self
    {
        $this->engines[] = $engine;
        return $this;
    }

    public function addEngines(RenderInterface ...$engines): self
    {
        $this->engines = array_merge($this->engines, $engines);
        return $this;
    }

    public function render(Request $request, Response $response, array $data = [], ?string $template = null): Response
    {
        foreach ($this->engines as $engine) {
            $response = $engine->render($request, $response, $data, $template);
        }
        return $response;
    }
}