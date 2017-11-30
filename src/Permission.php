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
     * @var \SFW2\Core\Database
     */
    protected $database = null;

    /**
     * @var \SFW2\Routing\User
     */
    protected $user = null;

    /**
     * @var Array
     */
    protected $permissions = [];

    public function __construct(Database $database, User $user) {
        $this->user = $user;
        $this->database = $database;
        $this->loadPermissions();
    }

    public function loadPermissions($userId) {
        if($this->user->isAdmin()) {
            return;
        }


        $stmt =
            'SELECT * ' .
            'FROM `sfw2_login_role` ' .
            'LEFT JOIN `sfw2_role_permission` ' .
            'ON `sfw2_login_role`.`RoleId` = `sfw2_role_permission`.`RoleId` ' .
            'LEFT JOIN `sfw2_permission`.`Id` = `sfw2_role_permission`.`PermissionId`' .
            'WHERE ';

        $this->permission = $this->database->select($stmt, [$userId]);
    }

    public function getActionPermission($path, $action) {
        if($this->user->isAdmin()) {
            return true;
        }

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
        if($this->user->isAdmin()) {
            return
                self::READ_OWN | self::READ_ALL | self::CREATE | self::UPDATE_OWN |
                self::UPDATE_ALL | self::DELETE_OWN | self::DELETE_ALL;
        }


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
