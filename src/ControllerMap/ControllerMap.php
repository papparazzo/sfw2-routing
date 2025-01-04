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

declare(strict_types=1);

namespace SFW2\Routing\ControllerMap;

use OutOfRangeException;

class ControllerMap implements ControllerMapInterface
{
    /**
     * @var ControllerData[]
     */
    protected array $controllerMap = [];

    public function appendControllerData(int $pathId, string $method, ControllerData $controllerData): void
    {
        $this->controllerMap["$method:$pathId"] = $controllerData;
    }

    public function getControllerRulesetByPathId(int $pathId, string $method): ControllerData
    {
        if (!isset($this->controllerMap["$method:$pathId"])) {
            throw new OutOfRangeException("path for id <$pathId> and method <$method> not set");
        }

        return $this->controllerMap["$method:$pathId"];
    }
}
