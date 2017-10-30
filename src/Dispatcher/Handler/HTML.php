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

namespace SFW2\Routing\Dispatcher\Handler;

use SFW2\Routing\Dispatcher\Handler;

class HTML extends Handler {

    public function handle() {
        $view = new \SFW\View();
        $view->appendCSSFiles($this->container['cssfiles']);
        $view->appendJSFiles($this->container['jsfiles']);

        $view->assign('content',       $this->container['content']);
        $view->assign('title',         $this->container['title']);
        $view->assign('menu',          $this->container['menu']);
        $view->assign('authenticated', $this->container['authenticated']);
        $view->showContent($this->config->getTemplateFile('olframe'));
    }
}
