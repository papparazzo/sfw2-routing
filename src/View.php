<?php

/**
 *  SFW2 - SimpleFrameWork
 *
 *  Copyright (C) 2019  Stefan Paproth
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

use SFW2\Routing\View\ViewException;

use DateTime;
use DateTimeZone;

class View {

    protected $vars = [];
    protected $template;
    protected $request;

    public function __construct(string $template = null, Request $request = '/') {
        $this->template = $template;
        $this->request  = $request;
    }

    public function assign(string $name, $val) {
        $this->vars[$name] = $val;
    }

    public function assignArray(array $values) {
        $this->vars = array_merge($this->vars, $values);
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
        $this->vars[$name] = array_merge($this->vars[$name], $values);
    }

    public function __toString() : string {
        return $this->getContent();
    }

    public function __isset(string $name) : bool {
        return isset($this->vars[$name]);
    }

    public function __get(string $name) {
        if(isset($this->vars[$name])) {
            return $this->vars[$name];
        }
        throw new ViewException("template-var <$name> not set", ViewException::VARIABLE_MISSING);
    }

    public function getCurrentPath() : string {
        return $this->request->getPath();
    }

    public function getPathSimplified() : string {
        return $this->request->getPathSimplified();
    }

    public function getContent() {
        ob_start();
        $this->showContent();
        return ob_get_clean();
    }

    protected function showContent() {
        if(!file_exists($this->template) || !is_readable($this->template)) {
            throw new ViewException("Could not find template <{$this->template}>", ViewException::TEMPLATE_MISSING);
        }

        if(!isset($this->vars['modificationDate']) || $this->vars['modificationDate'] == '') {
            $this->vars['modificationDate'] = new DateTime('@' . filemtime($this->template), new DateTimeZone('Europe/Berlin'));
        }

        if(is_string($this->vars['modificationDate'])) {
            $this->vars['modificationDate'] = new DateTime($this->vars['modificationDate'], new DateTimeZone('Europe/Berlin'));
        }

        #Mi., 11. Mai. 2016
        $this->vars['modificationDate'] = strftime('%a., %d. %b. %Y', $this->vars['modificationDate']->getTimestamp());

        include($this->template);
    }

}
