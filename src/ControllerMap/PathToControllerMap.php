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
 */

declare(strict_types=1);

namespace SFW2\Routing\ControllerMap;

use SFW2\Exception\HttpExceptions\HttpMethodNotAllowed;
use SFW2\Exception\HttpExceptions\HttpNotFound;

class PathToControllerMap implements ControllerMapInterface
{
    /**
     * @var array<string, array<string, ControllerData>>
     */
    protected array $controllerMap = [];

    public function appendControllerData(string $method, string $path, ControllerData $controllerData): void
    {
        $this->controllerMap[$path][strtoupper($method)] = $controllerData;
    }

    public function getControllerRulesetByPath(string $method, string $path): ControllerData
    {
        foreach($this->controllerMap as $pattern => $controllerData) {
            if(!preg_match("{^$pattern$}", $path, $matches)) {
                continue;
            }
            if(!isset($controllerData[$method])) {
                throw new HttpMethodNotAllowed(array_keys($controllerData));
            }
            array_shift($matches);
            return $controllerData[$method]->withActionParams($matches);
        }

        throw new HttpNotFound();
    }
}
