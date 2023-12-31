<?php

namespace SFW2\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequestHandler implements RequestHandlerInterface
{

    private MiddlewareInterface $middleware;

    private RequestHandlerInterface $next;

    public function __construct(MiddlewareInterface $middleware, RequestHandlerInterface $next)
    {
        $this->middleware = $middleware;
        $this->next = $next;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->middleware->process($request, $this->next);
    }
}