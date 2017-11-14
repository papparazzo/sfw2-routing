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

use SFW2\Core\View;

class Content {

    const HTTP_STATUS_INTERNAL_SERVER_ERROR = 0;
    const HTTP_STATUS_NOT_FOUND             = 1;
    const HTTP_STATUS_FORBIDDEN             = 2;

    protected $jsFiles  = [];
    protected $cssFiles = [];
    protected $view     = null;

    protected $title    = '';

    public function setTitle(string $title) {
        $this->title = $title;
    }

    public function appendView(View $view) {
        $this->view = $view;
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

    public function getTitle() : string {
        return $this->title;
    }

    public function getContent() : string {
        $this->view->getContent();
    }

    public function setHeader($header) {
        /*
        switch($headerType) {
            case self::HTTP_STATUS_INTERNAL_SERVER_ERROR:
                header("HTTP/1.0 500 Internal Server Error");
                break;

            case self::HTTP_STATUS_FORBIDDEN:
                header("HTTP/1.0 403 Forbidden");
                break;

            case self::HTTP_STATUS_NOT_FOUND:
                header("HTTP/1.0 404 Not Found");
                break;
        }
         *
         */
    }
}