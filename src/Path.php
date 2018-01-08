<?php

/**
 *  SFW2 - SimpleFrameWork
 *
 *  Copyright (C) 2017  Stefan Paproth
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

namespace SFW2\Routing;

use SFW2\Core\Database;
use SFW2\Routing\Request;

class Path {

    /**
     * @var \SFW2\Core\Database
     */
    protected $database = null;

    /**
     * @var string
     */
    protected $currentPath = '';

    /**
     * @var array
     */
    protected $pathMap = [];

    public function __construct(Database $database, Request $request) {
        $this->database = $database;
        $this->currentPath = $request->getPath();
        $this->pathMap['/'] = 0;
        $this->loadPath($this->pathMap);
    }

    public function loadPath(array &$map, int $parentId = 0, string $prefix = '/') {
        $stmt =
            "SELECT `PathId`, `ParentPathId`, `Name` " .
            "FROM `sfw2_path` " .
            "WHERE `ParentPathId` = '%s'";

        $res = $this->database->select($stmt, [$parentId]);

        foreach($res as $item) {
            $map[$prefix . $item['Name']] = $item['PathId'];
            $this->loadPath($map, $item['PathId'], $prefix . $item['Name'] . '/');
        }
    }

    public function isValidPath(string $path) : int {
        return isset($this->pathMap[$path]);
    }

    public function getPathId(string $path) : int {
        if(!$this->isValidPath($path)) {
            // TODO: Implement Exception
            throw new \Exception('// TODO: Implement Exception');
        }
        return $this->pathMap[$path];
    }

    public function getPath(int $pathId) : string {
        $res = array_search($pathId, $this->pathMap);
        if($res === false) {
            // TODO: Implement Exception
            throw new \Exception();
        }
        return $res;
    }

    public function getPathIdOfCurrentTopPath() {
        $chunks =  explode('/', $this->currentPath);
        if($chunks[1] != '') {
            return $this->getPathId('/' . $chunks[1]);
        }
        return -1;
    }

    public function getPathIdOfCurrentPath() {
        return $this->getPathId($this->currentPath);
    }
}
