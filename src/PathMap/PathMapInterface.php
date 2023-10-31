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

namespace SFW2\Routing\PathMap;

// TODO: Outsource into sfw-core!

interface PathMapInterface {

    public function isValidPath(string $path): bool; // TODO Rename into hasPath(string...

    public function getPathId(string $path): int;

    public function getPath(int $pathId): string;

    public function getPathIdOfParentPath(string $currentPath): int;

    public function updateModificationDateRecursive(string $path);
}
