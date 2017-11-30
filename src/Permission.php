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

class Permission {

    const READ_OWN   = 1;
    const READ_ALL   = 2;
    const CREATE     = 4;
    const UPDATE_OWN = 8;
    const UPDATE_ALL = 16;
    const DELETE_OWN = 32;
    const DELETE_ALL = 64;

    protected $permission = [];

    /**
     * @var Database
     */
    protected $database = null;

    public function __construct(Database $database, $userId = 0) {
        $this->database = $database;
        $this->loadPermissions($userId);
    }

    public function loadPermissions($userId) {
        $stmt =
            "SELECT `Permissions` " .
            "FROM `Permissions` ";

        $this->permission = $this->database->select($stmt, [$userId]);
    }

    public function getActionPermission($path, $action) {
        switch($action) {
            case 'create':
                return (bool)$this->getPermission($path) & self::CREATE;

            case 'update':
                return (bool)$this->getPermission($path) & self::UPDATE_OWN;

            case 'delete':
                return (bool)$this->getPermission($path) & self::DELETE_OWN;

            case 'index':
            default:
                return (bool)$this->getPermission($path) & self::READ_OWN;
        }
    }


    public function getPermission($path) {
        $chunks = explode('/', $path);
    }

    public function readOwnAllowed($path) {
        return (bool)$this->getPermission($path) & self::READ_OWN;
    }

    public function readAllAllowed($path) {
        return (bool)$this->getPermission($path) & self::READ_ALL;
    }

    public function createAllowed($path) {
        return (bool)$this->getPermission($path) & self::CREATE;
    }

    public function updateOwnAllowed($path) {
        return (bool)$this->getPermission($path) & self::UPDATE_OWN;
    }

    public function updateAllAllowed($path) {
        return (bool)$this->getPermission($path) & self::UPDATE_ALL;
    }

    public function deleteOwnAllowed($path) {
        return (bool)$this->getPermission($path) & self::DELETE_OWN;
    }

    public function deleteAllAllowed($path) {
        return (bool)$this->getPermission($path) & self::DELETE_ALL;
    }
}
