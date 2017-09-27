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

class Request {

    const DEFAULT_MODULE     = 'home';
    const DEFAULT_CONTROLLER = 'start';
    const DEFAULT_ACTION     = 'index';

    protected $module     = self::DEFAULT_MODULE;
    protected $controller = self::DEFAULT_CONTROLLER;
    protected $action     = self::DEFAULT_ACTION;


    protected $server = [];

    public function __construct(Array $server) {
        $this->server = $server;
        $this->fillParams($server['REQUEST_URI']);
    }

    public function getModule() {
        return $this->module;
    }

    public function getController() {
        return $this->controller;
    }

    public function getAction() {
        return $this->action;
    }

    public function getPath() {
        return $this->module . '/' . $this->controller . '/' . $this->action;
    }

    public function isAjaxRequest() {
        return isset($this->server['HTTP_X_REQUESTED_WITH']);
    }

    protected function fillParams($path) {
        $pos = strpos($path, '?');
        if($pos !== false) {
            $path = mb_substr($path, 0, $pos);
        }
        $path =  mb_substr($path, 1);

        $x = explode("/", $path);
        $x[0] = $x[0] ?? null;
        $x[1] = $x[1] ?? null;
        $x[2] = $x[2] ?? null;

        $this->module     = $this->chkParam($x[0], $this->module    );
        $this->controller = $this->chkParam($x[1], $this->controller);
        $this->action     = $this->chkParam($x[2], $this->action    );
    }

    protected function chkParam($x, $default) {
        if(empty($x)) {
            return $default;
        }

        $x = strtolower($x);
        $rv = preg_replace('/[^A-Za-z0-9]/', '', $x);

        if($rv != $x) {
            return $default;
        }
        return $rv;
    }
}