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

namespace SFW2\Routing\Resolver;

use SFW2\Core\SFW2Exception;

class ResolverException extends SFW2Exception {

    const PAGE_NOT_FOUND     = 1;
    const UNKNOWN_ERROR      = 2;
    const FILE_NOT_FOUND     = 3;
    const NO_DATA_FOUND      = 4;
    const NO_PERMISSION      = 5;
    const INVALID_DATA_GIVEN = 6;

}
