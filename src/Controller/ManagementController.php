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

namespace SFW2\Routing\Controller;

use SFW2\Core\Database;

use SFW2\Routing\Controller;
use SFW2\Routing\Result\Content;
use SFW2\Routing\Widget\Obfuscator\Phone;
use SFW2\Routing\Widget\Obfuscator\EMail;
use SFW2\Core\Helper;

class ManagementController extends Controller {

    /**
     * @var Database
     */
    protected $database;

    public function __construct(int $pathId, Database $database) {
        parent::__construct($pathId);
        $this->database = $database;
    }

    public function index($all = false) {
        $stmt =
            "SELECT IF(`sfw2_user`.`Sex` = 'MALE', 'Herr', 'Frau') AS `Sex`, " .
            "`sfw2_user`.`FirstName`, `sfw2_user`.`LastName`, " .
            "`sfw2_user`.`Email`, `sfw2_user`.`Phone1`, `sfw2_user`.`Phone2`, " .
            "IFNULL(`sfw2_division`.`Alias`, 'Spartenleitung') AS `Title`, " .
            "IF(`sfw2_position`.`Position` = 'Spartenleitung', " .
            "CONCAT('Leitung ', `sfw2_division`.`Name`) , " .
            "`sfw2_position`.`Position`) AS `Position` " .
            "FROM `sfw2_position` " .
            "INNER JOIN `sfw2_user` " .
            "ON `sfw2_position`.`UserId` = `sfw2_user`.`Id` " .
            "LEFT JOIN `sfw2_division` " .
            "ON `sfw2_division`.`Id` = `sfw2_position`.`DivisionId` " .
            "WHERE `sfw2_position`.`TopMost` = 1 ";

        $rows = $this->database->select($stmt);
        $data = array();
        foreach($rows as $row) {
            $user = [];
            $user['name'     ] = $row['Sex'] . ' ' . $row['FirstName'] . ' ' . $row['LastName'];
            $user['position' ] = $row['Position'];
            $user['phone1'   ] = (string)(new Phone($row['Phone1'], 'Tel.: ' . $row['Phone1']));
            $user['phone2'   ] = (string)(new Phone($row['Phone2'], 'Tel.: ' . $row['Phone2']));

            $user['emailaddr'] = (string)(new EMail($row['Email']));
            $user['image'    ] = '/public/layout/' . Helper::getImageFileName(
                # FIXME: _No hardcoded path
                'public/layout/',
                $row['FirstName'],
                $row['LastName']
            );
            $data[$row['Title']][] = $user;
        }
        foreach($data as $k => $v) {
            $data[$k] = array_chunk($v, 2);
        }

        $content = new Content('content/leitung');
        $content->assign('data', $data);
        return $content;
    }
}
