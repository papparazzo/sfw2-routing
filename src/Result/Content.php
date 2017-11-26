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

namespace SFW2\Routing\Result;

use SFW2\Routing\Result;
use SFW2\Core\View;

class Content extends Result {

  /**
     * @var \SFW2\Core\View
     */
    protected $view = null;

    protected $jsFiles  = [];
    protected $cssFiles = [];

    public function __construct(View $view) {
        $this->view = $view;
    }

    public function getData() {
        return $this->view->getContent();
    }

    public function appendJSFile($file) {
        $this->jsFiles[] = $file;
    }

    public function appendCSSFile($file) {
        $this->cssFiles[] = $file;
    }

    public function getJSFiles() : array {
        return $this->jsFiles;
    }

    public function getCSSFiles() : array {
        return $this->cssFiles;
    }




}