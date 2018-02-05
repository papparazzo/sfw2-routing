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
use SFW2\Routing\Result\Content;
use SFW2\Core\DateHelper;
use SFW2\Core\Database;

use SFW2\Routing\Controller\Helper\RemoveExhaustedDatesTrait;

class SeasonSchedule extends Controller {

    use RemoveExhaustedDatesTrait;

    /**
     * @var Database
     */
    protected $database;

    public function __construct(int $pathId, Database $database) {
        parent::__construct($pathId);
        $this->database = $database;
        $this->removeExhaustedDates();
    }

    public function index($all = false) {
        $content = new Content('content/seasonSchedule');
        $content->assign('dates', $this->getDates());
        #$this->ctrl->addJSFile('crud');

        return $content;
    }

    protected function getDates() {
        $stmt =
            "SELECT `sfw2_calendar`.`Id`, `sfw2_calendar`.`Description`, " .
                   "`sfw2_calendar`.`Description2`, " .
                   "`sfw2_calendar`.`StartDate`, `sfw2_calendar`.`StartTime` " .
            "FROM `sfw2_calendar` " .
            "WHERE `sfw2_calendar`.`PathId` = '%s' " .
            "ORDER BY `sfw2_calendar`.`StartDate`, `sfw2_calendar`.`StartTime` ";

        $rs = $this->database->select($stmt, [$this->pathId]);

        $rv = [];
        foreach($rs as $row) {
            $startDate = new DateHelper($row['StartDate']);
            $date = [];
            $date['id' ] = $row['Id'];
            $date['startDate'] = $startDate->getShortDate();
            $date['startTime'] = substr($row['StartTime'], 0, -3);
            $date['desc1'     ] = $row['Description'];
            $date['desc2'     ] = $row['Description2'];
            $rv[] = $date;
        }
        return $rv;
    }

    public function delete($all = false) {
        $stmt =
            "DELETE FROM `sfw2_calendar` " .
            "WHERE `sfw2_calendar`.`id` = '%s' " .
            "AND `PathId` = '%s'";

        $this->database->delete($stmt, [$id, $this->pathId]);
        $this->dto->setSaveSuccess(true);
        $this->ctrl->updateModificationDate();
    }

    public function create() {
        $stmt =
            "INSERT INTO `sfw_seasonschedule` " .
            "SET `Date` = '%s', `Time` = '%s', `Description` = '%s', " .
            "`PathId` = '%s'";

        $this->database->insert(
            $stmt,
            [
                $this->dto->getDate('date', true),
                $this->dto->getTime('time', true),
                $this->dto->getText('desc', true),
                $this->pathId
            ]
        );
        #$this->dto->setSaveSuccess();
        #$this->ctrl->updateModificationDate();
    }
}
