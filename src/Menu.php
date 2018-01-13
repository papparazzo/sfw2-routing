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
use SFW2\Routing\Permission;

use SFW2\Routing\Menu\MenuItem;

class Menu {

    /**
     * @var \SFW2\Routing\Permission
     */
    protected $permission = null;

    /**
     * @var \SFW2\Core\Database;
     */
    protected $database = null;

    /**
     * @var \SFW2\Routing\Path
     */
    protected $path;

    public function __construct(Database $database, Path $path, Permission $permission) {
        $this->database = $database;
        $this->path = $path;
        $this->permission = $permission;
    }

    public function getMainMenu() {
        return $this->getMenu(0, 1, $this->path->getPathIdOfCurrentTopPath());
    }

    public function getSideMenu() {
        return $this->getMenu(
            $this->path->getPathIdOfCurrentTopPath(),
            2,
            $this->path->getPathIdOfCurrentPath()
        );
    }

    public function getFullMenu() {
        return $this->getMenu(0, -1);
    }

    protected function getMenu(int $parentId, $depth, $checked = 0) {
        $stmt =
            "SELECT `menu`.`PathId`, `ParentPathId`, `menu`.`Name`, `Position` " .
            "FROM  `sfw2_menu` AS `menu` " .
            "LEFT JOIN `sfw2_path` " .
            "ON `menu`.`PathId` = `sfw2_path`.`PathId` " .
            "WHERE `ParentPathId` = '%s' " .
            "ORDER BY `Position` ASC";

        $res = $this->database->select($stmt, [$parentId]);

        $map = [];

        foreach($res as $row) {
            if(!$this->permission->readOwnAllowed($row['PathId'])) {
                continue;
            }
            $item = new MenuItem($row['Name'], $this->path->getPath($row['PathId']), $row['PathId'] == $checked);
            if($depth > 1) {
                $item->addSubMenuItems($this->getMenu($row['PathId'], $depth - 1, $checked));
            } else if($depth == -1) {
                $item->addSubMenuItems($this->getMenu($row['PathId'], $depth, $checked));
            }
            $map[] = $item;
        }
        return $map;
    }
}
