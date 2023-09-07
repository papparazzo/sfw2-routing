<?php

namespace SFW2\Routing\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFW2\Routing\Router\Exception as RouterException;
use Throwable;

class Error implements MiddlewareInterface
{
    protected ResponseFactoryInterface $factory;

    public function __construct(ResponseFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch(Throwable $exc) {
            return $this->handleException($exc);
        }
    }

    protected function handleException(Throwable $exception): ResponseInterface {

        if (!$exception instanceof RouterException) {
            $exception = new RouterException($exception->getMessage(), RouterException::INTERNAL_SERVER_ERROR, $exception);
        }

/*
        $this->assignArray([
            'title' => $exception->getCode(),
            'caption' => $exception->getError(),
            'description' => $exception->getMessage()
        ]);*/
        return $this->factory->createResponse();
    }
}

