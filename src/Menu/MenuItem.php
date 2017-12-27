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

/*
    const TIME_DIFF = 7; // in days

    protected $title;
    protected $url;
    protected $displayname;
    protected $checked;
    protected $submen       = array();
    protected $subActive    = false;
    protected $lastModified = null;
    protected $showItem     = false;
    protected $topMostMenu  = false;

    public function __construct(
        $displayname, $title, $url, $lastModified = null, $topMostMenu = false
    ) {
        $this->title        = $title;
        $this->lastModified = $lastModified;
        $this->displayname  = $displayname;
        $this->topMostMenu  = $topMostMenu;
        $this->url          = $url;
    }

    public function addSubMenuItem(Item $menuitem) {
        $this->submen[] = $menuitem;
    }

    public function subMenuActive($isActive) {
        $this->subActive = $isActive;
    }

    public function setMenuChecked() {
        $this->checked = true;
    }

    public function getTitle() {
        return $this->title;
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

    public function hasSubMenu() {
        return !empty($this->submen);
    }

    public function getSubMenu() {
        return $this->submen;
    }

    public function getSubmenuItem($key) {
        if(!isset($this->submen[$key])) {
            return null;
        }
        return $this->submen[$key];
    }

    public function isSubmenuActive() {
        return $this->subActive;
    }

    public function isRrecentlyModified() {
        return
            $this->hasNewContent() ||
            $this->hasNewContentSubMenu($this->submen);
    }

    protected function hasNewContentSubMenu(Array $items) {
        if(!$this->topMostMenu) {
            return false;
        }
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
            \time() - $this->lastModified < (self::TIME_DIFF * 60 * 60 * 24)
        ) {
            return true;
        }
        return false;
    }
 * 
 */
}