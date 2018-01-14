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
use SFW2\Core\Database;

class ManagementController extends Controller {

    /**
     * @var Database
     */
    protected $database;

    public function __construct(int $controllerId, Database $database) {
        parent::__construct($controllerId);
        $this->database = $database;
    }

    public function index() {
        $stmt =
            "SELECT IF(`sfw2_user`.`Sex` = 'MALE', 'Herr', 'Frau') AS `Sex`, " .
            "`sfw2_user`.`FirstName`, `sfw2_user`.`LastName`, " .
            "`sfw2_user`.`Email`, `sfw2_user`.`Phone1`, `sfw2_user`.`Phone2` " .
            #"IFNULL(`sfw2_division`.`Alias`, 'Spartenleitung') AS `Title`, " .
            #"IF(`sfw2_position`.`Position` = 'Spartenleitung', " .
            #"CONCAT('Leitung ', `sfw2_division`.`Name`) , " .
            #"`sfw2_position`.`Position`) AS `Position` " .
            #"FROM `sfw2_position` " .
            "FROM `sfw2_user` ";# .
            #"INNER JOIN `sfw2_user` " .
            #"ON `sfw2_position`.`UserId` = `sfw2_user`.`Id` " .
            #"LEFT JOIN `sfw2_division` " .
            #"ON `sfw2_division`.`DivisionId` = `sfw2_position`.`DivisionId` " .
            #"WHERE `sfw2_position`.`TopMost` = 1 ";

        $rows = $this->database->select($stmt);
        $data = array();
        foreach($rows as $row) {
            $user = [];
            $user['name'     ] =
                $row['Sex'] . ' ' . $row['FirstName'] . ' ' . $row['LastName'];
            $user['position' ] = 'Turner' ;#$row['Position'];
            $user['phone1'   ] = 'Tel.: ' . $row['Phone1']; /*new \SFW\View\Helper\Obfuscator\Phone(
                $row['Phone1'],
                'Tel.: ' . $row['Phone1']
            );*/
            $user['phone2'   ] = $row['Phone2']; /*new \SFW\View\Helper\Obfuscator\Phone(
                $row['Phone2'],
                'Tel.: ' . $row['Phone2']
            );*/

            $user['emailaddr'] = $row['Email']; /*new \SFW\View\Helper\Obfuscator\EMail(
                $row['Email']
            );*/
            $user['image'    ] = strtolower('/public/layout/' . $row['FirstName'] . '_' . $row['LastName'] . '.png');
;
                    /*
 # FIXME: _No hardcoded path
                '/public/content/users/' .
                \SFW\Helper::getImageFileName(
                    'public/content/users/',
                    $row['FirstName'],
                    $row['LastName']
            );*/
            #$data[$row['Title']][] = $user;
            $data['Title'][] = $user;
        }
        foreach($data as $k => $v) {
            $data[$k] = array_chunk($v, 4);
        }

        $content = new \SFW2\Routing\Result\Content('content/leitung');
        $content->assign('data', $data);
        $content->assign('firstname', 'Trude');

        #$content->assign('title', 'Hallo');

        $content->assign('description', 'Hallo Des');
        return $content;



        $stmt =
            "SELECT ";


        $page = 0;
/*
#        if($this->ctrl->hasCreatePermission()) {
#            $this->ctrl->addJSFile('ckeditor/ckeditor');
#            $this->ctrl->addJSFile('contenteditable');
#        }

        $view = new \SFW\View();
        $view->assign('editable',   $this->ctrl->hasCreatePermission());
        $view->assign('content',    $tmp);
        $view->assign('isAdmin',    $this->ctrl->isAdmin());
        $view->assignTpl(
            $this->conf->getTemplateFile('PageContent/ContentEditable')
        );

        return $view->getResult();
 */
    }
/*
    protected function loadContent($page = 0) {
        $stmt =
            "SELECT `sfw_contenteditable`.`Id`, `CreationDate`, `Title`, " .
            "`sfw_users`.`FirstName`, `sfw_users`.`LastName`, `Email`, " .
            "`Content` " .
            "FROM `sfw_contenteditable` " .
            "LEFT JOIN `sfw_users` " .
            "ON `sfw_users`.`Id` = `sfw_contenteditable`.`UserId` " .
            "WHERE `sfw_contenteditable`.`PathId` = '%s' " .
            "ORDER BY `Id` DESC ";

        $row = $this->db->selectRow(
            $stmt,
            array($this->ctrl->getPathId()),
            $page
        );

        if(empty($row)) {
            $entry['content'  ] = '';
            $entry['name'     ] = '';
            $entry['shortna'  ] = '; ' .$this->ctrl->getUserName();
            $entry['title'    ] = $this->title;
            $entry['date'     ] = new \SFW\View\Helper\Date();
            $entry['haserrors'] = false;
            return $entry;
        }

        $entry['content'  ] = $row['Content'];
        $entry['name'     ] = $row['FirstName'] . ' ' . $row['LastName'];
        $entry['title'    ] = $row['Title'  ]?$row['Title']:$this->title;
        $entry['date'     ] = new \SFW\View\Helper\Date($row['CreationDate']);
        $entry['haserrors'] = false;
        $entry['shortna'  ] = $this->getShortName($row);
        return $entry;
    }

    protected function executeOperation(&$page) {
        #FIXME $page = $this->dto->getPage();
        $tmp = array(
            'title'     => '',
            'content'   => '',
            'haserrors' => false
        );

        #FIXME if(
        #    !$this->ctrl->hasCreatePermission() ||
        #    $this->dto->getOperation() != 'create'
        #) {
            return $this->loadContent($page);
        #}

        $tmp['title'] = $this->dto->getTitle(
            'title',
            true,
            'Die Überschrift',
            50
        );
        $tmp['content'] = $this->dto->getData('content');

        if(
            $this->dto->getErrorProvider()->hasErrors() ||
            $this->dto->getErrorProvider()->hasWarning()
        ) {
            $tmp['haserrors'] = true;
            return $tmp;
        }

        $stmt =
            "INSERT INTO `sfw_contenteditable` " .
            "SET `PathId` = '%s', " .
            "`CreationDate` = NOW(), " .
            "`UserId` = %d, " .
            "`Title` = '%s', " .
            "`Content` = '%s'";

        $this->db->insert(
            $stmt,
            array(
                $this->ctrl->getPathId(),
                $this->ctrl->getUserId(),
                $tmp['title'  ],
                $tmp['content']
            )
        );
        $this->dto->setSaveSuccess();
        $this->ctrl->updateModificationDate();
        return $this->loadContent(0);
    }
 *
 */
}