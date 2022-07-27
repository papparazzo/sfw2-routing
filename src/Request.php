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

use SFW2\Routing\Request\Exception as RequestException;
use SFW2\Routing\Request\MethodType;
use SFW2\Routing\Request\RequestType;

class Request {

    protected array $server = [];
    protected array $get    = [];
    protected array $post   = [];

    protected string $path = '';

    /**
     * @throws \SFW2\Routing\Request\Exception
     */
    public function __construct(array $server, array $get = [], array $post = []) {
        $this->server = $server;
        $this->get    = $get;
        $this->post   = $post;
        $this->path   = $this->checkPath($server['REQUEST_URI']);

        if($this->getMethodType() == MethodType::PUT) {
            $input = [];

            parse_str(file_get_contents("php://input"), $input);

            foreach ($input as $name => $value) {
                $this->post[$name] = $value;
            }
        }
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getPathSimplified(): string {
        return 'P_' . str_replace('/', '_', $this->path);
    }

    public function getGetParam(string $name, $def = null) {
        if(!isset($this->get[$name])) {
            return $def;
        }
        return $this->get[$name];
    }

    public function getPostParam(string $name, $def = null) {
        if(!isset($this->post[$name])) {
            return $def;
        }
        return $this->post[$name];
    }

    public function getRequestType(): RequestType {
        if(!isset($this->server['HTTP_X_REQUESTED_WITH'])) {
            return RequestType::HTML;
        }
        if(str_contains($this->server["HTTP_ACCEPT"], "application/json")) {
            return RequestType::AJAX_JSON;
        }
        return RequestType::AJAX_XML;
    }

    /**
     * @throws \SFW2\Routing\Request\Exception
     */
    public function getMethodType(): MethodType {
        foreach(MethodType::cases() as $type) {
            if($type->value == $_SERVER['REQUEST_METHOD']) {
                return $type;
            }
        }
        throw new RequestException("unknown method type given");
    }

    protected function checkPath(string $path): string {
        $pos = strpos($path, '?');
        if($pos !== false) {
            $path = mb_substr($path, 0, $pos);
        }
        $path = strtolower($path);
        $path = preg_replace('#[^A-Za-z\d/]#', '', $path);

        return '/' . trim($path, '/');
    }
}