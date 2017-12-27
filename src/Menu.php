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

class Menu {

    protected $menu = [];

    /**
     * @var \SFW2\Routing\Permission
     */
    protected $permission = null;

    /**
     * @var \SFW2\Core\Database;
     */
    protected $database = null;

    public function __construct(Database $database, Permission $permission) {
        $this->database = $database;
        $this->permission = $permission;
    }

    public function getMenu() {
        return $this->menu;
    }

}

/*
 */

class Menu5 {

    public function __construct(Database $db) {
        $this->db     = $db;
        $this->createMenu();
    }

    protected function createMenu() {
        $this->createMenuRecursive();
    }
/*
    protected function createMenuRecursive($pid = 0, $level = 0) {
        $stmt =
            "SELECT `sfw2_menu`.`Id`, `sfw2_menu`.`Displayname`, " .
                   "`sfw2_path`.`Module`, `sfw2_path`.`Controller`, " .
                   "`sfw2_path`.`Action`, `sfw2_menu`.`Description`, " .
                   "UNIX_TIMESTAMP(`sfw2_path`.`LastModified`) AS " .
                   "`LastModified` " .
            "FROM `sfw2_menu` " .
            "LEFT JOIN `sfw2_permission_path` AS `sfw2_path` " .
            "ON `sfw2_path`.`PathId` = `sfw2_menu`.`PathId` " .
            "WHERE `sfw2_menu`.`PId` = '%s' " .
            "ORDER BY `sfw2_menu`.`Pos` ASC";
        $res = $this->db->select($stmt, array($pid));

        foreach($res as $v) {
            $this->createMenuItem(
                $level,
                $v['Displayname'],
                $v['Module'],
                $v['Controller'],
                $v['Action'],
                $v['Description'],
                $v['LastModified']
            );
            $this->createMenuRecursive($v['Id'], $level + 1);
        }
    }

    protected function createMenuItem(
        $level, $displayname, $module, $controller, $action, $title,
        $lastModified
    ) {
        $menuitem = new Menu\Item(
            $displayname,
            $title,
            $this->generateURL($module, $controller, $action),
            $lastModified,
            $level == 0
        );
        switch($level) {
            case 0:
                if(!isset($this->menu[$module])) {
                    $this->menu[$module] = $menuitem;
                }
                break;

            case 1:
                $this->menu[$module]->addSubMenuItem($menuitem);
                break;
        }
    }

    protected function generateURL($module, $controller, $action) {
        $rv = '/' . $module;

        if($controller == null) {
            return $rv;
        }

        $rv .= '/' . $controller;

        if($action == null) {
            return $rv;
        }

        return $rv . '/' . $action;
    }







/*
    public function getMenuArray() {
        $rv = array();

        foreach($this->menu as $menuitem) {
            $rv[$menuitem->getDisplayName()] = array();
            $rv[$menuitem->getDisplayName()][$menuitem->getURL()] =
                $menuitem->getDisplayName();

            $sub1 = $menuitem->getSubMenu();

            foreach($sub1 as $menuItem1) {
                $rv[$menuitem->getDisplayName()][$menuItem1->getURL()] =
                    $menuItem1->getDisplayName();

                $sub2 = $menuItem1->getSubMenu();
                foreach($sub2 as $menuItem2) {
                    $rv[$menuitem->getDisplayName()][$menuItem2->getURL()] =
                        $menuItem2->getDisplayName();
                }
            }
        }
        return $rv;
    }

    protected function markMenuItem($module, $controller, $action) {
        if(!isset($this->menu[$module])){
            return;
        }

        $this->menu[$module]->setMenuChecked();
        $sub = $this->menu[$module]->getSubmenuItem($controller);
        if(!($sub instanceof Menu\Item)) {
            return;
        }

        if($action == 'index') {
            $sub->setMenuChecked();
            return;
        }
        $sub = $sub->getSubmenuItem($action);
        if(!($sub instanceof Menu\Item)) {
            return;
        }
        $sub->setMenuChecked();
    }
 */
}
