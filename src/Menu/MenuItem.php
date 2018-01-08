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

namespace SFW2\Routing\Menu;

class MenuItem {

    const TIME_DIFF = 7; // in days

    protected $url;
    protected $displayname;
    protected $checked;
    protected $submen       = [];
    protected $lastModified = null;

    public function __construct($displayname, $url, $lastModified = null) {
        $this->lastModified = $lastModified;
        $this->displayname  = $displayname;
        $this->url          = $url;
    }

    public function addSubMenuItem(MenuItem $menuItem) {
        $this->submen[] = $menuItem;
    }

    public function addSubMenuItems(array $menuItems) {
        $this->submen = array_merge($this->submen, $menuItems);
    }

    public function setMenuChecked() {
        $this->checked = true;
    }

    public function getURL() {
        return $this->url;
    }

    public function getChecked() {
        return $this->checked;
    }

    public function getDisplayName() {
        return $this->displayname;
    }

    public function getSubMenu() {
        return $this->submen;
    }

    public function isRrecentlyModified() {
        return
            $this->hasNewContent(); #||
            #$this->hasNewContentSubMenu($this->submen);
    }

    protected function hasNewContentSubMenu(Array $items) {
        #if(!$this->topMostMenu) {
        #    return false;
        #}
        foreach($items as $item) {
            if($item->hasNewContent()) {
                return true;
            }

            if(
                $item->hasSubMenu() &&
                $this->hasNewContentSubMenu($item->getSubMenu())
            ) {
                return true;
            }
        }
        return false;
    }

    protected function hasNewContent() {
        if(
            $this->lastModified &&
            time() - $this->lastModified < (self::TIME_DIFF * 60 * 60 * 24)
        ) {
            return true;
        }
        return false;
    }
}