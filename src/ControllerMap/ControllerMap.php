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

namespace SFW2\Routing\ControllerMap;

use InvalidArgumentException;
use OutOfRangeException;

class ControllerMap implements ControllerMapInterface {

    /**
     * @var array
     */
    protected array $controllerMap = [];

    public function __construct(array $controllerMap) {
        $this->controllerMap = $controllerMap;
    }

    /**
     * @param int $pathId
     * @return array[]
     */
    public function getControllerRulsetByPathId(int $pathId): array {

        if(!isset($this->controllerMap[$pathId])) {
            throw new OutOfRangeException("path <$pathId> not set");
        }

        $res = $this->controllerMap[$pathId];

        $params = $res['data'];

        if(!is_array($params)) {
            throw new InvalidArgumentException("invalid params given <{$res['JsonData']}>");
        }

        array_unshift($params, $pathId);

        return [
            $res['className'] => [
                'constructParams' => $params
            ]
        ];
    }
}




