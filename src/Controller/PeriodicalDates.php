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
use SFW2\Routing\Permission;

use SFW2\Core\Database;
use SFW2\Core\Config;

class PeriodicalDates extends Controller {

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Permission
     */
    protected $permission;

    public function __construct(int $pathId, Database $database, Config $config, Permission $permission) {
        parent::__construct($pathId);
        $this->database = $database;
        $this->config = $config;
        $this->permission = $permission;
        $this->removeExhaustedDates();
    }

    public function index() {
        $editable = $this->permission->createAllowed($this->pathId);
        if($editable) {
#            $this->ctrl->addJSFile('crud');
        }

        $tmp = [
            'pdday'  => '',
            'pdfrom' => '',
            'pdtill' => '',
            'pddesc' => ''
        ];

        $content = new Content('content/newspaper/PeriodicalDates');
        $content->assign('periodicalDates', $this->getPeriodicalDates());
        $content->assign('tmp', $tmp);
        $content->assign('editable', $editable);
        return $content;
    }

    protected function removeExhaustedDates() {
        $stmt =
            "DELETE FROM `sfw2_calendar` " .
            "WHERE `Day` IS NULL " .
            "AND ((`EndDate` IS NULL AND `StartDate` < NOW()) " .
            "OR (`EndDate` < NOW()))";

        $this->database->delete($stmt);
    }

    protected function getPeriodicalDates() {
        $stmt =
            "SELECT `sfw2_calendar`.`Id`, `sfw2_calendar`.`Description`, " .
                   "`sfw2_calendar`.`Day`, `sfw2_calendar`.`StartTime`, " .
                   "`sfw2_calendar`.`EndTime`, " .
                   "IF(`sfw2_calendar`.`ValidTo` < NOW(), '1', '0') " .
                   " AS `expired` " .
            "FROM `sfw2_calendar` " .
            "WHERE `sfw2_calendar`.`PathId` = '%s' " .
            "AND `sfw2_calendar`.`Day` IS NOT NULL " .
            "ORDER BY `sfw2_calendar`.`Day`, `sfw2_calendar`.`StartTime` ";

        $rs = $this->database->select($stmt, array($this->ctrl->getPathId()));

        if(isset($rs[0]['Day'])) {
            $ld = $rs[0]['Day'];
        }
        $rv = array();
        foreach($rs as $row) {
            if($ld != $row['Day']) {
                $ld = $row['Day'];
                $date = array();
                $date['day' ] = '&nbsp;';
                $date['time'] = '&nbsp;';
                $date['desc'] = '';
                $rv[] = $date;
            }

            $date = array();
            $date['id'  ] = $row['Id'];
            #$date['day' ] = $this->locale->getWeekdayName($row['Day'], true) . '.';
            $date['from'] = mb_substr($row['StartTime'], 0, -3);
            $date['till'] = mb_substr($row['EndTime'], 0, -3);
            $date['desc'] = $row['Description'];
            $rv[] = $date;
        }
        return $rv;
    }

    protected function delete($id) {
        $stmt =
            "DELETE FROM `sfw2_calendar` " .
            "WHERE `sfw2_calendar`.`id` = '%s' " .
            "AND `PathId` = '%s'";

        $this->database->delete($stmt, [$id, $this->pathId]);
        $this->dto->setSaveSuccess(true);
        $this->ctrl->updateModificationDate();
    }

    protected  function create(&$tmp) {
        $tmp['pdfrom'] = $this->dto->getTime('pdfrom', true,  'Die Startzeit');
        $tmp['pdtill'] = $this->dto->getTime('pdtill', false, 'Die Endzeit');
        $tmp['pddesc'] = $this->dto->getText('pddesc', true, 'Die Beschreibung');
        $tmp['pdday' ] = $this->dto->getArrayValue('pdday', true, 'Der Tag', ['1', '2', '3', '4', '5', '6', '7']);

        if(
            !empty($tmp['pdtill']) &&
            intval(str_replace(':', '', $tmp['pdfrom'])) >
            intval(str_replace(':', '', $tmp['pdtill']))
        ) {
            $this->dto->getErrorProvider()->addError(
                SFW_Error_Provider::CMP_TIME,
                array(
                    '<NAME>'  => 'Die Endzeit',
                    '<NAME2>' => 'die Startzeit'
                ),
                'pdtill'
            );
        }

        if($this->dto->getErrorProvider()->hasErrors()) {
            return;
        }

        $stmt =
            "INSERT INTO `sfw2_calendar` " .
            "SET `Day` = '%s', `StartTime` = '%s', `Description` = '%s', " .
            "`PathId` = '%s'";

        if(!empty($tmp['pdtill'])) {
            $stmt .= ", `EndTime` = '" . $this->db->escape($tmp['pdtill']) . "' ";
        }

        $this->database->insert(
            $stmt,
            [$tmp['pdday'], $tmp['pdfrom'], $tmp['pddesc'], $this->pathId]
        );
        #$this->dto->setSaveSuccess();
        #$this->ctrl->updateModificationDate();
        $tmp['pdday' ] = '';
        $tmp['pdfrom'] = '';
        $tmp['pdtill'] = '';
        $tmp['pddesc'] = '';
    }
}