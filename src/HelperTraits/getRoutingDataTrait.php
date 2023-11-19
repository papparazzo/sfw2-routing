<?php

/**
 *  SFW2 - SimpleFrameWork
 *
 *  Copyright (C) 2023  Stefan Paproth
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

declare(strict_types=1);

namespace SFW2\Routing\HelperTraits;

use Psr\Http\Message\ServerRequestInterface as Request;
use SFW2\Core\HttpExceptions\HttpNotFound;

trait getRoutingDataTrait
{
    protected function getAction(Request $request): string
    {
        return (string)$request->getAttribute('sfw2_routing')['action'];
    }

    /**
     * @throws HttpNotFound
     */
    protected function getPathId(Request $request): int
    {
        $pathId = $request->getAttribute('sfw2_routing')['path_id'];

        if (is_null($pathId)) {
            throw new HttpNotFound("could not load <{$this->getPath($request)}>");
        }
        return (int)$pathId;
    }

    protected function getPath(Request $request): string
    {
        return (string)$request->getAttribute('sfw2_routing')['path'];
    }
}