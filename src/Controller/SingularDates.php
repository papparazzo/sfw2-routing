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

class SingularDates extends Controller {

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
            'sdstartdate'  => '',
            'sdenddate'    => '',
            'sdstarttime'  => '',
            'sdendtime'    => '',
            'sddesc'       => '',
            'sdchangeable' => false
        ];

        $content = new Content($this->getPageId());
        $content->assign('singularDates', $this->getSingularDates());
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

    protected function getSingularDates() {
        $stmt =
            "SELECT `sfw_calendar`.`Id`, `sfw_calendar`.`Description`, " .
                   "`sfw_calendar`.`StartDate`, `sfw_calendar`.`StartTime`, " .
                   "`sfw_calendar`.`EndDate`, `sfw_calendar`.`EndTime`, " .
                   "`sfw_calendar`.`Changeable` " .
            "FROM `sfw_calendar` " .
            "WHERE `sfw_calendar`.`PathId` = '%s' " .
            "AND `sfw_calendar`.`Day` IS NULL " .
            "ORDER BY `sfw_calendar`.`StartDate`, " .
                     "`sfw_calendar`.`StartTime` ";

        $rs = $this->database->select($stmt, array($this->ctrl->getPathId()));

        $changeable = false;
        $rv = array();
        foreach($rs as $row){
            $date = array();
            $date['id'        ] = $row['Id'];
            $date['startDate' ] = new \SFW\View\Helper\Date($row['StartDate']);
            $date['endDate'   ] = new \SFW\View\Helper\Date($row['EndDate'  ]);
            $date['startTime' ] = \substr($row['StartTime'], 0, -3);
            $date['endTime'   ] = \substr($row['EndTime'  ], 0, -3);
            $date['desc'      ] = $row['Description'];
            $date['changeable'] = $row['Changeable']=='0' ? false : true;
            if($date['changeable']) {
                $changeable = true;
            }
            $rv[] = $date;
        }
        return ['data' => $rv, 'changeable' => $changeable];
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

    private function create(&$tmp) {
        $tmp['sdstartdate'] = $this->dto->getDate(
            'sdstartdate',
            true,
            'Das Startdatum',
            true
        );
        $tmp['sdenddate'] = $this->dto->getDate(
            'sdenddate',
            false,
            'Das Enddatum',
            true
        );
        $tmp['sdstarttime'] = $this->dto->getTime(
            'sdstarttime',
            false,
            'Die Startzeit'
        );
        $tmp['sdendtime'] = $this->dto->getTime(
            'sdendtime',
            false,
            'Die Endzeit'
        );
        $tmp['sdchangeable'] = $this->dto->getBool(
            'sdchangeable',
            'Die Auswahl'
        );
        $tmp['sddesc'] = $this->dto->getText(
            'sddesc',
            true,
            'Die Beschreibung'
        );

        if(
            !empty($tmp['sdtill']) &&
            intval(str_replace(':', '', $tmp['sdstarttime'])) >
            intval(str_replace(':', '', $tmp['sdendtime']))
        ) {
            $this->dto->getErrorProvider()->addError(
                SFW_Error_Provider::CMP_TIME,
                array(
                    '<NAME>'  => 'Die Endzeit',
                    '<NAME2>' => 'die Startzeit'
                ),
                'sdtill'
            );
        }

        if($this->dto->getErrorProvider()->hasErrors()) {
            return;
        }

        $stmt =
            "INSERT INTO `sfw_calendar` " .
            "SET `StartDate` = '%s', `Description` = '%s', " .
            "`PathId` = '%s', `Changeable` = '%s' ";

        if(!empty($tmp['sdstarttime'])) {
            $stmt .= ", `StartTime` = '"
                   . $this->db->escape($tmp['sdstarttime']) . "' ";
        }

        if(!empty($tmp['sdendtime'])) {
            $stmt .= ", `EndTime` = '"
                   . $this->db->escape($tmp['sdendtime']) . "' ";
        }

        if(
            !empty($tmp['sdenddate']) &&
            $tmp['sdenddate'] != $tmp['sdstartdate']
        ) {
            $stmt .=
                ", `EndDate` = '" .
                $this->db->escape(
                    $this->db->convertToMysqlDate($tmp['sdenddate'])
                ) . "' ";
        }

        $this->db->insert(
            $stmt,
            array(
                $this->db->convertToMysqlDate($tmp['sdstartdate']),
                $tmp['sddesc'],
                $this->ctrl->getPathId(),
                $tmp['sdchangeable']?'1':'0'
            )
        );

        $this->dto->setSaveSuccess();
        $this->ctrl->updateModificationDate();
        $tmp['sdstartdate' ] = '';
        $tmp['sdenddate'   ] = '';
        $tmp['sdstarttime' ] = '';
        $tmp['sdendtime'   ] = '';
        $tmp['sddesc'      ] = '';
        $tmp['sdchangeable'] = false;
    }
}