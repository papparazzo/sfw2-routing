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

use Exception;

class Permission {

    const NO_PERMISSION = 0;
    const READ_OWN      = 1;
    const READ_ALL      = 2;
    const CREATE        = 4;
    const UPDATE_OWN    = 8;
    const UPDATE_ALL    = 16;
    const DELETE_OWN    = 32;
    const DELETE_ALL    = 64;

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

    public function loadPermissions() {
        if($this->user->isAdmin()) {
            return;
        }

        $stmt =
            'SELECT `PathId`, `Permission` ' .
            'FROM `sfw2_permission` ';# .
#            'LEFT JOIN `sfw2_role` ' .
#            'ON `sfw2_permission`.`RoleId` = `sfw2_role`.`Id` ' .
#            'LEFT '
#            'WHERE ';

        $rows = $this->database->select($stmt, [$this->user->getUserId()]);

        foreach($rows as $row) {
            $perm = 0;
            foreach(explode(',', $row['Permission']) as $permission) {
                switch($permission) {
                    case 'READ_OWN':
                        $perm &= self::READ_OWN;
                        break;

                    case 'READ_ALL':
                        $perm |= self::READ_ALL;
                        break;

                    case 'CREATE':
                        $perm |= self::CREATE;
                        break;

                    case 'UPDATE_OWN':
                        $perm |= self::UPDATE_OWN;
                        break;

                    case 'UPDATE_ALL':
                        $perm |= self::UPDATE_ALL;
                        break;

                    case 'DELETE_OWN':
                        $perm |= self::UPDATE_OWN;
                        break;

                    case 'DELETE_ALL':
                        $perm |= self::DELETE_ALL;
                        break;

                    default:
                        throw new Exception(); // TODO: Permission-Exception
                }
            }
            $this->permissions[$row['PathId']] = $perm;
        }
    }

    public function getPermission($pathId) {
        if($this->user->isAdmin()) {
            return
                self::READ_OWN | self::READ_ALL | self::CREATE | self::UPDATE_OWN |
                self::UPDATE_ALL | self::DELETE_OWN | self::DELETE_ALL;
        }

        if(!isset($this->permissions[$pathId])) {
            return self::NO_PERMISSION;
        }
        return $this->permissions[$pathId];
    }

    public function readOwnAllowed($pathId) {
        return (bool)$this->getPermission($pathId) & self::READ_OWN;
    }

    public function readAllAllowed($pathId) {
        return (bool)$this->getPermission($pathId) & self::READ_ALL;
    }

    public function createAllowed($pathId) {
        return (bool)$this->getPermission($pathId) & self::CREATE;
    }

    public function updateOwnAllowed($pathId) {
        return (bool)$this->getPermission($pathId) & self::UPDATE_OWN;
    }

    public function updateAllAllowed($pathId) {
        return (bool)$this->getPermission($pathId) & self::UPDATE_ALL;
    }

    public function deleteOwnAllowed($pathId) {
        return (bool)$this->getPermission($pathId) & self::DELETE_OWN;
    }

    public function deleteAllAllowed($pathId) {
        return (bool)$this->getPermission($pathId) & self::DELETE_ALL;
    }
}
