<?php

namespace SFW2\Routing\Middleware;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Offline implements MiddlewareInterface
{
    private string $bypassToken;

    private bool $offline;

    private ContainerInterface $container;

    public function __construct(bool $offline, string $bypassToken = '', ContainerInterface $container)
    {
        $this->bypassToken = $bypassToken;
        $this->offline = $offline;
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if(!$this->container->has('site.offline') && !$this->container->get('site.offline')) {
            return $handler->handle($request);
        }

        if($session->isGlobalEntrySet('bypass')) {
            return $handler->handle($request);
            return;
        }

        if(isset($this->get['bypass']) && $this->get['bypass'] == $this->container->get('site.offlineBypassToken')) {
            $session->setGlobalEntry('bypass', true);
            return;
        }

       # 503 Service Unavailable

        throw new RouterException("offline", RouterException::SERVICE_UNAVAILABLE);
    }
}