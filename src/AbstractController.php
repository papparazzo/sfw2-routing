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
 *
 */

namespace SFW2\Routing;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SFW2\Core\HttpExceptions\HttpNotFound;
use SFW2\Routing\Render\RenderInterface;

abstract class AbstractController {

    protected RenderInterface $renderEngine;

    public function __construct(RenderInterface $renderEngine)
    {
        $this->renderEngine = $renderEngine;
    }

    abstract public function index(Request $request, Response $response): Response;

   # , bool $all = false

    /**
     * @throws HttpNotFound
     *
     * Übersicht auf Landingpage
     */
    public function preview(Request $request, Response $response): Response {
        throw new HttpNotFound();
    }

    /**
     * @throws HttpNotFound
     */
    public function create(Request $request, Response $response): Response {
        throw new HttpNotFound();
    }

    /**
     * @throws HttpNotFound
     */
    public function read(Request $request, Response $response): Response {
        throw new HttpNotFound();
    }

    /**
     * @throws HttpNotFound
     */
    public function update(Request $request, Response $response): Response {
        throw new HttpNotFound("delete-action not found");
    }

    /**
     * @throws HttpNotFound
     */
    public function delete(Request $request, Response $response): Response {
        throw new HttpNotFound("delete-action not found");
    }
}
