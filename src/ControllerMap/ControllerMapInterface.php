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

namespace SFW2\Routing\ControllerMap;

use SFW2\Exception\HttpExceptions\Status4xx\HttpStatus404NotFound;
use SFW2\Exception\HttpExceptions\Status4xx\HttpStatus405MethodNotAllowed;

interface ControllerMapInterface
{
    /**
     * @param  string $method
     * @param  string $path
     * @return ControllerData
     * @throws HttpStatus405MethodNotAllowed
     * @throws HttpStatus404NotFound
     */
    public function getControllerRulesetByPath(string $method, string $path): ControllerData;
}
