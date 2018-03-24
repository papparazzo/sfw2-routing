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

class Content extends Result {

    protected $templateFile = null;

    protected $jsFiles  = [];
    protected $cssFiles = [];
    protected $vars     = [];

    protected $error   = false;

    public function __construct($templateFile = null, $hasErrors = false) {
        $this->templateFile = $templateFile;
        $this->error = $hasErrors;
    }

    public function appendJSFile($file) {
        $this->jsFiles[] = $file;
    }

    public function appendCSSFile($file) {
        $this->cssFiles[] = $file;
    }

    public function getJSFiles(string $path) : array {
        $this->appendPath($this->jsFiles, $path);
        return $this->jsFiles;
    }

    public function getCSSFiles(string $path) : array {
        $this->appendPath($this->cssFiles, $path);
        return $this->cssFiles;
    }

    public function assign(string $name, $val) {
        $this->vars[$name] = $val;
    }

    public function assignArray(array $values) {
        $this->vars += $values;
    }

    public function append(string $name, $val) {
        if(!isset($this->vars[$name])) {
            $this->vars[$name] = [];
        }
        $this->vars[$name][] = $val;
    }

    public function appendArray(string $name, array $values) {
        if(!isset($this->vars[$name])) {
            $this->vars[$name] = [];
        }
        $this->vars[$name] += $values;
    }

    public function getData() {
        return $this->vars;
    }

    public function getValue($name, $def = null) {
        if(isset($this->vars[$name])) {
            return $this->vars[$name];
        }
        return $def;
    }

    public function getTemplateFile() {
        return $this->templateFile;
    }

    public function setError(bool $error) {
        $this->error = $error;
    }

    public function getError() {
        return $this->error;
    }

    protected function appendPath(array &$items, string $path) {
        array_walk(
            $items,
            function(&$item, $key, $path) {
                if(is_file($item)) {
                    return;
                }
                $item = DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $item;
            },
            $path
        );
    }
}
