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

use SFW2\Core\Database;

class PathMapByDatabase extends AbstractPathMap {

    /**
     * @var \SFW2\Core\Database
     */
    protected $database = null;

    public function __construct(Database $database, string $currentPath = '/') {
        $this->database = $database;
        parent::__construct($currentPath);
    }

    protected function updateModificationDate($pathId) {
        $stmt =
            "UPDATE `sfw2_path` " .
            "SET `ModificationDate` = NOW() ".
            "WHERE `Id` = '%s'";

        $this->database->update($stmt, [$pathId]);
    }

    protected function loadPath(array &$map, int $parentId = 0, string $prefix = '/') {
        $stmt =
            "SELECT `Id`, `Name` " .
            "FROM `sfw2_path` " .
            "WHERE `ParentPathId` = '%s'";

        $res = $this->database->select($stmt, [$parentId]);

        foreach($res as $item) {
            $map[$prefix . $item['Name']] = $item['Id'];
            $this->loadPath($map, $item['Id'], $prefix . $item['Name'] . '/');
        }
    }

}
