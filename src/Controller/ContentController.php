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
use SFW2\Routing\User;

use SFW2\Core\Database;


class ContentController extends Controller {

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var User
     */
    protected $user;

    protected $title;

    public function __construct(int $pathId, Database $database, User $user, string $title = null) {
        parent::__construct($pathId);
        $this->database = $database;
        $this->user = $user;
        $this->title = $title;
    }

    public function index() {
        $content = new Content('content/content');
        $content->assign('editable', false);
        $content->assign('title', $this->title);
        $content->assign('content', $this->loadContent());
        return $content;
/*
#        if($this->ctrl->hasCreatePermission()) {
#            $this->ctrl->addJSFile('ckeditor/ckeditor');
#            $this->ctrl->addJSFile('contenteditable');
#        }
 */
    }

    protected function loadContent() {
        $stmt =
            "SELECT `sfw2_content`.`Id`, `CreationDate`, `Title`, " .
            "`sfw2_user`.`FirstName`, `sfw2_user`.`LastName`, `Email`, " .
            "`Content` " .
            "FROM `sfw2_content` " .
            "LEFT JOIN `sfw2_user` " .
            "ON `sfw2_user`.`Id` = `sfw2_content`.`UserId` " .
            "WHERE `sfw2_content`.`PathId` = '%s' " .
            "ORDER BY `Id` DESC ";

        $row = $this->database->selectRow($stmt, [$this->pathId]);

        if(empty($row)) {
            $entry['content'  ] = '';
            $entry['name'     ] = '';
            $entry['shortna'  ] = '; ' . $this->user->getUserName();
            $entry['title'    ] = $this->title;
            $entry['date'     ] = '';#new \SFW\View\Helper\Date();
            $entry['haserrors'] = false;
            return $entry;
        }

        $entry['content'  ] = $row['Content'];
        $entry['name'     ] = $row['FirstName'] . ' ' . $row['LastName'];
        $entry['title'    ] = $row['Title'  ];
        $entry['date'     ] = '';#new \SFW\View\Helper\Date($row['CreationDate']);
        $entry['haserrors'] = false;
        $entry['shortna'  ] = $this->getShortName($row);
        return $entry;
    }

    protected function getShortName(array $data) {
        if(!isset($data['FirstName']) || !isset($data['LastName'])) {
            return '';
        }

        #if(empty($data['Email'])) {
            return substr($data['FirstName'], 0, 1)  . '. ' . $data['LastName'];
        #}
        /*
        return new SFW_View_Helper_MailObfuscator(
            $data['Email'],
            substr($data['FirstName'], 0, 1)  . '. ' . $data['LastName'],
            "Seite: " . SFW_Dispatcher::getURL() . '?p=' . $this->dto->getPage()
        );
        */
    }


/*
    protected function executeOperation(&$page) {
        #FIXME $page = $this->dto->getPage();
        $tmp = array(
            'title'     => $this->title,
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
