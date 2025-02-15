<?php

namespace SFW2\Routing\Middleware;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use SFW2\Exception\HttpExceptions\Status5xx\HttpStatus503ServiceUnavailable;

class Offline implements MiddlewareInterface
{
    private const BY_PASS_TOKEN_KEY = 'bypass';

    private CacheInterface $bypassTokenCache;

    private ContainerInterface $container;

    public function __construct(CacheInterface $bypassTokenCache, ContainerInterface $container)
    {
        $this->container = $container;
        $this->bypassTokenCache = $bypassTokenCache;
    }

    /**
     * @param  ServerRequestInterface  $request
     * @param  RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     * @throws HttpStatus503ServiceUnavailable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->container->has('site.offline') && !$this->container->get('site.offline')) {
            return $handler->handle($request);
        }

        $tokenFromConfig = $this->getBypassTokenFromConfig();

        if (
            $this->bypassTokenCache->has(self::BY_PASS_TOKEN_KEY) &&
            $tokenFromConfig == $this->bypassTokenCache->get(self::BY_PASS_TOKEN_KEY)
        ) {
            return $handler->handle($request);
        }

        $tokenFromRequest = $request->getAttribute(self::BY_PASS_TOKEN_KEY);

        if ($tokenFromRequest != $tokenFromConfig) {
            throw new HttpStatus503ServiceUnavailable("website offline");
        }

        $this->bypassTokenCache->set(self::BY_PASS_TOKEN_KEY, $tokenFromConfig);
        return $handler->handle($request);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getBypassTokenFromConfig(): string
    {
        if (!$this->container->has('site.offlineBypassToken')) {
            return '';
        }
        /** @var string */
        return $this->container->get('site.offlineBypassToken');
    }
}
