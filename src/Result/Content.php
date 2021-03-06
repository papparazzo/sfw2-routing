<?php

/**
 *  SFW2 - SimpleFrameWork
 *
 *  Copyright (C) 2018  Stefan Paproth
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

class Content extends AbstractResult {

    protected string $templateFile = '';

    protected array $jsFiles  = [];
    protected array $cssFiles = [];
    protected array $vars     = [];

    public function __construct(string $templateFile = '', bool $hasErrors = false) {
        parent::__construct($hasErrors);
        $this->templateFile = $templateFile;
    }

    public function appendJSFile(string $file) : void {
        $this->jsFiles[] = $file;
    }

    public function appendCSSFile(string $file) : void {
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

    public function assign(string $name, $val) : void {
        $this->vars[$name] = $val;
    }

    public function assignArray(array $values) : void {
        $this->vars += $values;
    }

    public function append(string $name, $val) : void {
        if(!isset($this->vars[$name])) {
            $this->vars[$name] = [];
        }
        $this->vars[$name][] = $val;
    }

    public function appendArray(string $name, array $values) : void {
        if(!isset($this->vars[$name])) {
            $this->vars[$name] = [];
        }
        $this->vars[$name] += $values;
    }

    public function getData() : array {
        return $this->vars;
    }

    public function getValue(string $name, $def = null) {
        if(isset($this->vars[$name])) {
            return $this->vars[$name];
        }
        return $def;
    }

    public function getTemplateFile() : string {
        return $this->templateFile;
    }

    protected function appendPath(array &$items, string $path) : void {
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
