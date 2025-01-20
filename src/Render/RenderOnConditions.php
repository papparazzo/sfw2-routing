<?php

namespace SFW2\Routing\Render;

use OutOfRangeException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SFW2\Routing\Render\Conditions\ConditionInterface;

class RenderOnConditions implements RenderInterface
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
            if ($entry['on']($request, $response)) {
                return $entry['render']->render($request, $response, $data, $template);
            }
        }
        throw new OutOfRangeException("no render engines found");
    }
}