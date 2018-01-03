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

use SFW2\Core\Database;

class ControllerMap {

    /**
     * @var \SFW2\Core\Database
     */
    protected $database = null;

    /**
     * @var array
     */
    protected $map = [];

    public function __construct(Database $database) {
        $this->database = $database;

        $this->map = [
            '' => [
                'class' => 'SFW2\Routing\Controller\StaticController',
                'params' => [
                    'home'
                ]
            ],
            'verein' => [
                'class' => 'SFW2\Routing\Controller\BaseController',
                'params' => [
                    150,
                    'Hallddo'
                ]
            ]
        ];
    }

    public function isPath($path) {
        return isset($this->map[$path]);
    }

    public function getClassByPath($path) {
        return $this->map[$path]['class'];
    }

    public function getParamsByPath($path) {
        return $this->map[$path]['params'];
    }

    protected function loadController() {
        $stmt =
            'SELECT * ' .
            'FROM `sfw2_controller_map` ' .
            '';

        $res = $this->database->select($stmt);
    }
}
