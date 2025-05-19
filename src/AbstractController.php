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

namespace SFW2\Routing;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SFW2\Exception\HttpExceptions\Status4xx\HttpStatus404NotFound;

/**
 * @deprecated
 */
abstract class AbstractController
{
    /**
     * @throws HttpStatus404NotFound
     * @deprecated
     * Consider to change to read as default action
     *
     * read -> readOwn, readOther combined... Permission: 'allow', 'read*'
     * readOwn
     * readOther
     */
    public function index(Request $request, Response $response): Response
    {
        $request->getUri();
        $params = $request->getQueryParams();
        if (!isset($params['do'])) {
            return $response;
        }

        $method = $params['do'];

        if (!is_callable([$this, $method])) {
            throw new HttpStatus404NotFound();
        }

        /** @var Response */
        return $this->$method($request, $response);
    }

    public function read(Request $request, Response $response): Response
    {
        return $this->index($request, $response);
    }

    /**
     * @throws HttpStatus404NotFound
     *
     * Übersicht auf Landingpage
     */
    public function preview(Request $request, Response $response): Response
    {
        throw new HttpStatus404NotFound();
    }

    /**
     * @throws HttpStatus404NotFound
     */
    public function create(Request $request, Response $response): Response
    {
        throw new HttpStatus404NotFound();
    }

    /**
     * @throws HttpStatus404NotFound
     */
    public function update(Request $request, Response $response): Response
    {
        throw new HttpStatus404NotFound("delete-action not found");
    }

    /**
     * @throws HttpStatus404NotFound
     */
    public function delete(Request $request, Response $response): Response
    {
        throw new HttpStatus404NotFound("delete-action not found");
    }
}
