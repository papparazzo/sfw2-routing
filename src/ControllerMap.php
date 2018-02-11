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
use SFW2\Routing\ControllerMap\Exception as ControllerMapException;

class ControllerMap {

    /**
     * @var \SFW2\Core\Database
     */
    protected $database = null;

    public function __construct(Database $database) {
        $this->database = $database;
    }

    public function getRulsetByPathId($pathId) {
        $stmt =
            "SELECT `ClassName`, `JsonData` " .
            "FROM `sfw2_path` AS `ctrlMap` " .
            "LEFT JOIN `sfw2_controller_template` AS `ctrlTempl` " .
            "ON `ctrlMap`.`ControllerTemplateId` = `ctrlTempl`.`Id` " .
            "WHERE `ctrlMap`.`Id` = '%s' ";

        $res = $this->database->selectRow($stmt, [$pathId]);

        if(empty($res)) {
            throw new ControllerMapException(
                'found no entry for <' . $pathId . '>',
                ControllerMapException::NO_RESULTSET_GIVEN
            );
        }

        $params = json_decode($res['JsonData'], true);

        if(!is_array($params)) {
            throw new ControllerMapException(
                'invalid params given <' . $res['JsonData'] . '>',
                ControllerMapException::INVALID_PARAMS_GIVEN
            );
        }

        array_unshift($params, $pathId);

        return [
            $res['ClassName'] => [
                'constructParams' => $params
            ]
        ];
    }
}
