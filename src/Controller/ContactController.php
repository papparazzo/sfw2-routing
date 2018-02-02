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

use SFW2\Routing\Result\Content;
use SFW2\Routing\Widget\Obfuscator\EMail;
use SFW2\Routing\Widget\Obfuscator\Phone;
use SFW2\Routing\Controller;
use SFW2\Core\Database;

class ContactController  extends Controller {

    /**
     * @var Database
     */
    protected $database;

    public function __construct(int $pathId, Database $database) {
        parent::__construct($pathId);
        $this->database = $database;
    }

    public function index() {
        $stmt =
            "SELECT `sfw2_user`.`FirstName`, `sfw2_user`.`LastName`, " .
            "IF(`sfw2_user`.`Sex` = 'MALE', 'Herr', 'Frau') AS `Sex`, " .
            "`sfw2_user`.`Phone2`, `sfw2_user`.`Email`, `sfw2_user`.`Phone1`, " .
            "`sfw2_position`.`Position`, " .
            "IFNULL(`sfw2_division`.`Alias`, `sfw2_division`.`Name`) AS `Division` " .
            "FROM `sfw2_position` " .
            "INNER JOIN `sfw2_user` " .
            "ON `sfw2_position`.`UserId` = `sfw2_user`.`Id` " .
            "LEFT JOIN `sfw2_division` " .
            "ON `sfw2_division`.`Id` = `sfw2_position`.`DivisionId` " .
            "ORDER BY `sfw2_division`.`Position`, `sfw2_position`.`Order` ";

        $rows = $this->database->select($stmt);

        $entries = [];
        $lp = '';
        $ld = '';
        foreach($rows as $row) {
            $user = [];
            $user['position' ] = '';
            $user['name'     ] =
                $row['Sex'] . ' ' . $row['FirstName'] . ' ' . $row['LastName'];

            if($ld != $row['Division'] || $lp != $row['Position']){
                $user['position' ] = $row['Position'];
            }
            $user['phone'    ] = $this->getPhoneNumber($row['Phone1']);
            $user['emailaddr'] = (string)(new EMail($row["Email"]));

            $entries[$row['Division']][] = $user;

            if($row['Phone2'] != '') {
                $user['name'     ] = '';
                $user['position' ] = '';
                $user['emailaddr'] = null;
                $user['phone'    ] = $this->getPhoneNumber($row['Phone2'] ?? '');
                $entries[$row['Division']][] = $user;
            }

            $lp = $row['Position'];
            $ld = $row['Division'];
        }

        $content = new Content('content/kontakt');
        $content->assign('entries', $entries);
        return $content;
    }

    protected function getPhoneNumber($phone) {
        if($phone == '') {
            return '';
        }
        return (string)(new Phone(
            $phone,
            'Tel.: ' . $phone
        ));
    }
}
