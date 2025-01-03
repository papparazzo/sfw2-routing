<?php

namespace SFW2\Routing\Render;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SFW2\Routing\Render\Conditions\ConditionInterface;

class RenderComposite implements RenderInterface
{
    /**
     * @var list<array{on: ConditionInterface, render: RenderInterface}>
     */
    protected array $engines = [];

    public function addEngine(ConditionInterface $on, RenderInterface $engine): self
    {
        $this->engines[] = ['on' => $on, 'render' => $engine];
        return $this;
    }

    public function render(Request $request, Response $response, array $data = [], ?string $template = null): Response
    {
        foreach ($this->engines as $entry) {
            $response =
                $entry['on']($request) ? $entry['render']->render($request, $response, $data, $template) : $response;
        }
        return $response;
    }
}