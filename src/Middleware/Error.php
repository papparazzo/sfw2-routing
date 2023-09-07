<?php

/**
 *  SFW2 - SimpleFrameWork
 *
 *  Copyright (C) 2018  Stefan Paproth
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/agpl.txt>.
 *
 */

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

