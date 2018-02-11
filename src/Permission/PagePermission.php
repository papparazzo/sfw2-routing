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

namespace SFW2\Routing\Permission;

use Exception;

class PagePermission {
    const NO_PERMISSION = 0;
    const READ_OWN      = 1;
    const READ_ALL      = 2;
    const CREATE        = 4;
    const UPDATE_OWN    = 8;
    const UPDATE_ALL    = 16;
    const DELETE_OWN    = 32;
    const DELETE_ALL    = 64;

    protected $permission = self::NO_PERMISSION;

    public function __construct(array $permissions = []) {
        $this->setPermissions($permissions);
    }

    public function setAllPermissions() {
        $this->permission =
            self::READ_OWN | self::READ_ALL | self::CREATE | self::UPDATE_OWN |
            self::UPDATE_ALL | self::DELETE_OWN | self::DELETE_ALL;
        return $this;
    }

    public function setPermissions($permissions) {
        $this->permission = self::NO_PERMISSION;

        foreach($permissions as $permission) {
            switch($permission) {
                case '':
                    break;

                case 'READ_OWN':
                    $this->permission |= self::READ_OWN;
                    break;

                case 'READ_ALL':
                    $this->permission |= self::READ_ALL;
                    break;

                case 'CREATE':
                    $this->permission |= self::CREATE;
                    break;

                case 'UPDATE_OWN':
                    $this->permission |= self::UPDATE_OWN;
                    break;

                case 'UPDATE_ALL':
                    $this->permission |= self::UPDATE_ALL;
                    break;

                case 'DELETE_OWN':
                    $this->permission |= self::DELETE_OWN;
                    break;

                case 'DELETE_ALL':
                    $this->permission |= self::DELETE_ALL;
                    break;

                default:
                    throw new Exception(); // TODO: Permission-Exception
            }
        }
        return $this;
    }

    public function readOwnAllowed() {
        return (bool)$this->permission & self::READ_OWN;
    }

    public function readAllAllowed() {
        return (bool)$this->permission & self::READ_ALL;
    }

    public function createAllowed() {
        return (bool)$this->permission & self::CREATE;
    }

    public function updateOwnAllowed() {
        return (bool)$this->permission & self::UPDATE_OWN;
    }

    public function updateAllAllowed() {
        return (bool)$this->permission & self::UPDATE_ALL;
    }

    public function deleteOwnAllowed() {
        return (bool)$this->permission & self::DELETE_OWN;
    }

    public function deleteAllAllowed() {
        return (bool)$this->permission & self::DELETE_ALL;
    }
}
