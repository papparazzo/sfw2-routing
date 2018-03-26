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

    const DEFAULT_ACTION = 'index';

    const REQUEST_TYPE_AJAX_XML  = 1;
    const REQUEST_TYPE_AJAX_JSON = 2;
    const REQUEST_TYPE_HTML      = 3;
    const REQUEST_TYPE_UNKNOWN   = 4;

    protected $action = self::DEFAULT_ACTION;

    protected $server = [];
    protected $get    = [];
    protected $post   = [];

    protected $path = '';

    public function __construct(array $server, array $get = [], array $post = []) {
        $this->server = $server;
        $this->get    = $get;
        $this->post   = $post;
        $this->path   = $this->checkPath($server['REQUEST_URI']);
        $this->action = $this->getGetParam('do', self::DEFAULT_ACTION);
    }

    public function getAction() {
        return $this->action;
    }

    public function getPath() {
        return $this->path;
    }

    public function hasPostParams() : bool {
        return (bool)count($this->post);
    }

    public function hasGetParams() : bool {
        return (bool)count($this->get);
    }

    public function getRequestType() {
        if(!isset($this->server['HTTP_X_REQUESTED_WITH'])) {
            return self::REQUEST_TYPE_HTML;
        }
        if(strpos($this->server["HTTP_ACCEPT"], "application/json") !== false) {
            return self::REQUEST_TYPE_AJAX_JSON;
        }
        return self::REQUEST_TYPE_AJAX_XML;
    }

    public function getGetParam($name, $def = null) {
        if(!isset($this->get[$name])) {
            return $def;
        }
        return $this->get[$name];
    }

    public function getPostParam($name, $def = null) {
        if(!isset($this->post[$name])) {
            return $def;
        }
        return $this->post[$name];
    }

    protected function checkPath($path) {
        $pos = strpos($path, '?');
        if($pos !== false) {
            $path = mb_substr($path, 0, $pos);
        }
        $path = strtolower($path);
        $path = preg_replace('#[^A-Za-z0-9/]#', '', $path);
        $path = '/' . trim($path, '/');
        return $path;
    }
}