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

namespace SFW2\Routing\PathMap;

use OutOfRangeException;

class PathMap {

    protected string $currentPath = '';

    protected array $pathMap = [];

    public function __construct(string $currentPath, PathMapLoaderInterface $pathMapLoader) {
        $this->currentPath = $currentPath;
        $this->pathMap = $pathMapLoader->getPathMap();
        $this->pathMap['/'] = 0;
    }

    public function isValidPath(string $path): bool {
        return isset($this->pathMap[$path]);
    }

    public function getPathId(string $path): int {
        if(!$this->isValidPath($path)) {
            throw new OutOfRangeException("invalid path <$path> given");
        }
        return $this->pathMap[$path];
    }


    /*

        public function getPath(int $pathId): string {
            $res = array_search($pathId, $this->pathMap);
            if($res === false) {
                throw new OutOfRangeException("path for id <$pathId> does not exists");
            }
            return $res;
        }

        public function getPathIdOfCurrentTopPath(): int {
            $chunks =  explode('/', $this->currentPath);
            if($chunks[1] == '') {
                throw new OutOfRangeException("invalid path <$this->currentPath> given");
            }
            return $this->getPathId('/' . $chunks[1]);
        }

        public function getPathIdOfCurrentPath(): int {
            return $this->getPathId($this->currentPath);
        }
    */
}
