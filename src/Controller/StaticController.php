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

use SFW2\Routing\Controller;
use \SFW2\Routing\Result\Content;
use SFW2\Core\Database;
use SFW2\Core\Config;

class StaticController extends Controller {

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Config
     */
    protected $config;

    protected $template;

    protected $title;

    public function __construct(int $pathId, string $template, Database $database, Config $config, string $title = null) {
        parent::__construct($pathId);
        $this->template = $template;
        $this->database = $database;
        $this->config = $config;
        $this->title = $title;
    }

    public function index() {
        $content = new Content($this->template);
        $content->assign('chairman', $this->getChairman());
        $content->assign('mailaddr', $this->config->getVal('project', 'eMailWebMaster'));
        $content->assign('title', $this->title);
        return $content;
    }

    protected function getChairman() {
        $stmt =
            "SELECT CONCAT(IF(`sfw2_user`.`Sex` = 'MALE', 'Herr ', 'Frau '), " .
            "`sfw2_user`.`FirstName`, ' ', `sfw2_user`. `LastName`) AS `Chairman` " .
            "FROM `sfw2_position` " .
            "LEFT JOIN `sfw2_division` " .
            "ON `sfw2_division`.`Id` = `sfw2_position`.`DivisionId` " .
            "LEFT JOIN `sfw2_user` " .
            "ON `sfw2_user`.`Id` = `sfw2_position`.`UserId` " .
            "WHERE `sfw2_position`.`Order` = '1' " .
            "AND `sfw2_division`.`Position` = '0' ";

        return $this->database->selectSingle($stmt);
    }
}
