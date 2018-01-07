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

    public function __construct(Database $database, Path $path/*, Permission $permission*/) {
        $this->database = $database;
        $this->path = $path;
        #$this->permission = $permission;
    }

    public function getMenu(int $parentId = 0, $depth = 1) {
        $stmt =
            "SELECT `PathId`, `ParentPathId`, `menu`.`Name`, `Position` " .
            "FROM  `sfw2_menu` AS `menu` " .
            "LEFT JOIN `sfw2_path` " .
            "ON `PathId` " .
            "WHERE `ParentPathId` = '%s' " .
            "ORDER BY `Position` ASC";

        $res = $this->database->select($stmt, [$parentId]);

        $map = [];

        foreach($res as $item) {
            $item = new MenuItem($item['Name'], $this->path->getPath($item['PathId']));
            if($depth > 1) {
                $item->addSubMenuItems($this->loadTree($item['PathId'], $depth - 1));
            } else if($depth == -1) {
                $item->addSubMenuItems($this->loadTree($item['PathId'], $depth));
            }
            $map[] = $item;
        }
        return $map;
    }
}