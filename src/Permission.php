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
use SFW2\Routing\Permission\PagePermission;
use SFW2\Routing\Permission\Exception as PermissionException;

class Permission {

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

    /**
     * @var Array
     */
    protected $roles = [];

    public function __construct(Database $database, User $user) {
        $this->user = $user;
        $this->database = $database;
        $this->loadRoles();
        $this->loadPermissions(0, '');
        die();
    }

    protected function loadRoles() {
        if($this->user->isAdmin()) {
            return;
        }

        $stmt =
            "SELECT `RoleId` " .
            "FROM `sfw2_user_role` " .
            "WHERE `UserId` = '%s'";

        $rows = $this->database->select($stmt, [$this->user->getUserId()]);
        foreach($rows as $row) {
            $this->roles[] = $row['RoleId'];
        }

        if(empty($this->roles)) {
            throw new PermissionException('No roles defines', PermissionException::NO_ROLES_DEFINED);
        }
    }

    public function loadPermissions(int $parentPathId, $initPermission) {
        if($this->user->isAdmin()) {
            return;
        }

        $stmt =
            "SELECT `sfw2_path`.`Id`, GROUP_CONCAT(`Permission`) AS `Permission` " .
            "FROM `sfw2_path` " .
            "LEFT JOIN `sfw2_permission` " .
            "ON `sfw2_path`.`Id` = `PathId` " .
            "WHERE `ParentPathId` = '%s' " .
            "AND `RoleId` IN(%s) " .
            "GROUP BY `RoleId`";

        $rows = $this->database->select($stmt, [$parentPathId, implode(',', $this->roles)]);

        foreach($rows as $row) {
            if($row['Permission'] !== null) {
                $initPermission = $row['Permission'];
            }
            $this->permissions[$row['Id']] = new PagePermission(explode(',', $initPermission));
            $this->loadPermissions($row['Id'], $initPermission);
        }
    }

    public function getPermission($pathId) : PagePermission {
        if($this->user->isAdmin()) {
            return (new PagePermission())->setAllPermissions();
        }

        if(!isset($this->permissions[$pathId])) {
            return new PagePermission();
        }
        return $this->permissions[$pathId];
    }

    public function getActionPermission($pathId, $action = 'index') : bool {
        if($this->user->isAdmin()) {
            return true;
        }

        switch($action) {
            case 'create':
                return $this->getPermission($pathId)->createAllowed();

            case 'update':
                return $this->getPermission($pathId)->updateOwnAllowed();

            case 'delete':
                return $this->getPermission($pathId)->deleteOwnAllowed();

            case 'index':
            default:
                return $this->getPermission($pathId)->readOwnAllowed();
        }
    }

    public function hasFullActionPermission($pathId, $action = 'index') : bool {
        if($this->user->isAdmin()) {
            return true;
        }

        switch($action) {
            case 'create':
                return $this->getPermission($pathId)->createAllowed();

            case 'update':
                return $this->getPermission($pathId)->updateAllAllowed();

            case 'delete':
                return $this->getPermission($pathId)->deleteAllAllowed();

            case 'index':
            default:
                return $this->getPermission($pathId)->readAllAllowed();
        }
    }
}
