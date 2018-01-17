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

class BlogController extends Controller {

    /**
     * @var Database
     */
    protected $database;

    protected $title;

    public function __construct(int $pathId, Database $database, string $title = null) {
        parent::__construct($pathId);
        $this->database = $database;
        $this->title = $title;
    }

    public function index() {
#        if($this->ctrl->hasCreatePermission()) {
#            $this->ctrl->addJSFile('ckeditor/ckeditor');
#            $this->ctrl->addJSFile('contenteditable');
#        }

        $content = new \SFW2\Routing\Result\Content('content/blog/blog');

#        $content->assign('editable',   $this->ctrl->hasCreatePermission());
#        $content->assign('content',    $tmp);
#        $content->assign('isAdmin',    $this->ctrl->isAdmin());

        $content->assign('title', $this->title);
        $content->assign('items', $this->loadEntries());
        return $content;
    }

    protected function loadEntries($page = 0) {
        $stmt =
            "SELECT `sfw2_blog`.`Id`, `CreationDate`, `Title`, " .
            "`sfw2_user`.`FirstName`, `sfw2_user`.`LastName`, `Email`, " .
            "`Content` " .
            "FROM `sfw2_blog` " .
            "LEFT JOIN `sfw2_user` " .
            "ON `sfw2_user`.`Id` = `sfw2_blog`.`UserId` " .
            "WHERE `sfw2_blog`.`PathId` = '%s' OR 1 " .
            "ORDER BY `Id` DESC ";

        return $this->database->select($stmt, array($this->pathId), $page);

        if(empty($row)) {
            $entry['content'  ] = '';
            $entry['name'     ] = '';
            #$entry['shortna'  ] = '; ' .$this->ctrl->getUserName();
            $entry['title'    ] = $this->title;
            #$entry['date'     ] = new \SFW\View\Helper\Date();
            $entry['haserrors'] = false;
            return $entry;
        }

        $entry['content'  ] = $row['Content'];
        $entry['name'     ] = $row['FirstName'] . ' ' . $row['LastName'];
        $entry['title'    ] = $row['Title'] ? $row['Title'] : $this->title;
        #$entry['date'     ] = new \SFW\View\Helper\Date($row['CreationDate']);
        $entry['haserrors'] = false;
        $entry['shortna'  ] = '';#$this->getShortName($row);
        return $entry;
    }
/*
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