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

abstract class Controller {

    /**
     * @var int
     */
    protected $pathId;

    public function __construct(int $pathId) {
        $this->pathId = $pathId;
    }

    abstract function index($all = false);


    public function create() {

    }

    public function delete($all = false) {

    }

    public function update($all = false) {

    }
/*
    protected $pathId       = -1;

    public function updateModificationDate() {
        $stmt =
            "UPDATE `sfw_path` " .
            "SET `LastModified` = NOW() ".
            "WHERE PathId = '%s'";

        $this->registry->getDatabase()->update($stmt, array($this->pathId));
    }

    protected function setPathId() {
        $this->modiDate = new \SFW\View\Helper\Date($rv['LastModified']);
    }
 */
}