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
 *  along with this program. If not, see <https://www.gnu.org/licenses/agpl.txt>.
 */

declare(strict_types=1);

namespace SFW2\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Router implements RequestHandlerInterface
{
    public function __construct(
        private RequestHandlerInterface $top
    ) {
    }

    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $tmp = $this->top;
        $this->top = new RequestHandler($middleware, $tmp);

        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->top->handle($this->appendData($request));
    }

    private function appendData(ServerRequestInterface $request): ServerRequestInterface
    {
        $path = $request->getUri()->getPath();

        $requestData = [
            RequestData::IS_HOME => $path == '/',
            RequestData::PATH_SIMPLIFIED => strtolower('p_' . str_replace('/', '_', $path)),
            RequestData::PATH => $path
        ];

        return $request->withAttribute('sfw2_routing', $requestData);
    }
}
