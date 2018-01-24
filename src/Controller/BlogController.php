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
use SFW2\Routing\Permission;
use SFW2\Routing\Result\Content;
use SFW2\Routing\Widget\Obfuscator\EMail;
use SFW2\Routing\User;
use SFW2\Core\Helper;

use SFW2\Core\Database;

class BlogController extends Controller {

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Permission
     */
    protected $permission;

    /**
     * @var User
     */
    protected $user;

    protected $title;

    public function __construct(int $pathId, Database $database, User $user, Permission $permission, string $title = null) {
        parent::__construct($pathId);
        $this->database = $database;
        $this->user = $user;
        $this->permission = $permission;
        $this->title = $title;
    }

    public function index() {
        $editable = $this->permission->createAllowed($this->pathId);
        if($editable) {
#            $this->addJSFile('crud');
#            $this->ctrl->addJSFile('ckeditor/ckeditor');
        }
        #$this->ctrl->addJSFile('slimbox2');
        #$this->ctrl->addJSFile('jquery.comments');
        #$this->ctrl->addCSSFile('slimbox2');
        #$this->ctrl->addCSSFile('comments');

        $tmp = array(
            'title'    => '',
            'section'  => '',
            'location' => '',
            'content'  => '',
            'date'     => '1. April'
        );

        $content = new Content('content/blog/blog');

        $content->assign('deleteable', $this->permission->deleteOwnAllowed($this->pathId));
        $content->assign('divisions', $this->getDivisions());

        $content->assign('editable', $editable);
        $content->assign('tmp', $tmp);
        $content->assign('isAdmin', $this->user->isAdmin());
        $content->assign('title', (string)$this->title);
        $content->assign('items', $this->loadEntries());
        return $content;
    }

    protected function loadEntries() {
        $entries = array();

        $stmt =
            "SELECT `sfw2_blog`.`Id`, `sfw2_blog`.`CreationDate`, " .
            "`sfw2_blog`.`Link`, `sfw2_user`.`Email`, `sfw2_blog`.`Content`, " .
            "`sfw2_blog`.`Title`, `sfw2_user`.`FirstName`, `sfw2_user`.`LastName`, " .
            "`sfw2_division`.`Name` AS `Resource`, " .
#            "`sfw2_division`.`Module` AS `Module`, " .
            "IF(`sfw2_blog`.`UserId` = '%s' OR '%s', '1', '0') " .
            "AS `DelAllowed` " .
            "FROM `sfw2_blog` " .
            "LEFT JOIN `sfw2_user` " .
            "ON `sfw2_user`.`Id` = `sfw2_blog`.`UserId` " .
            "LEFT JOIN `sfw2_division` " .
            "ON `sfw2_division`.`Id` = `sfw2_blog`.`DivisionId` ";

        $stmt .=  "ORDER BY `sfw2_blog`.`Id` DESC ";
        $rows = $this->database->select(
            $stmt,
            [$this->user->getUserId(), $this->user->isAdmin() ? '1' : '0']
        );
/*
        $cmt = new \SFW\Comments(
            $this->db,
            $this->dto,
            $this->editable,
            $this->isAdmin
        );
*/
        foreach($rows as $row) {
            #$cd = new \SFW\View\Helper\HDate($row['CreationDate'], new \SFW\Locale());
            $entry = [];
            $entry['id'         ] = $row['Id'];
            $entry['date'       ] = '1. April'; # $cd;
            $entry['title'      ] = $row['Title'];
            #$entry['location'   ] = $row['Location'];
            $entry['content'    ] = $row['Content'];
            $entry['resname'    ] = $row['Resource'];
            #$entry['resurl'     ] = '/';# . $row['Module'];
            $entry['delAllowed' ] = (bool)$row['DelAllowed'];
            $entry['commentscnt'] = 215; #$cmt->getEntriesCount('BLOG', $row['Id']);

            $entry['image'      ] = '/public/layout/' . Helper::getImageFileName(
                # FIXME: _No hardcoded path
                'public/layout/',
                $row['FirstName'],
                $row['LastName']
            );

            $entry['mailaddr'   ] = (string)(new EMail(
                $row["Email"],
                $row['FirstName'] . ' ' . $row['LastName'],
                "Blogeintrag vom " #. $cd->getFormatedDate(true)
             ));

            $entries[] = $entry;
        }
        return $entries;
    }

    public function delete() {
        if(!$this->hasDeletePermission()) {
            return false;
        }
        $entryId = $this->dto->getNumeric('id');
        $stmt =
            "DELETE ".
            "FROM `sfw2_blog` " .
            "WHERE `Id` = %s ";

        if(!$this->ctrl->isAdmin()) {
            $stmt .=
                "AND `UserId` = '" .
                $this->db->escape($this->ctrl->getUserId()) . "'";
        }

        if($this->config->database->update($stmt, array($entryId)) != 1) {
            $this->dto->getErrorProvider()->addError(
                sfw2_Error_Provider::ERR_DEL,
                array('<NAME>' => 'Der Blogeintrag')
            );
        }
        $this->dto->setSaveSuccess(true);
        return true;
    }

    public function create() {
        if(!$this->hasCreatePermission()) {
            return false;
        }

        $tmp['title'] = $this->dto->getTitle(
            'title_' . $this->getPageId(),
            true,
            'Die Überschrift'
        );
        $tmp['location'] = $this->dto->getArrayValue(
            'location_' . $this->getPageId(),
            false,
            'Der Link',
            $this->getMenueArray()
        );
        $tmp['content'] = $this->dto->getTitle(
            'content_' . $this->getPageId(),
            true,
            'Der Inhalt'
        );
        $tmp['section'] = $this->dto->getArrayValue(
            'section_' . $this->getPageId(),
            true,
            'Das Resort',
            $this->sections
        );

        if(
            $this->dto->getErrorProvider()->hasErrors() ||
            $this->dto->getErrorProvider()->hasWarning()
        ) {
            return false;
        }

        $stmt =
            "INSERT INTO `sfw2_blog` " .
            "SET `CreationDate` = NOW(), " .
            "`Title` = '%s', " .
            "`DivisionId` = '%s', " .
            "`Location` = '%s', " .
            "`Content` = '%s', " .
            "`UserId` = %d";

        $this->config->database->insert(
            $stmt,
            array(
                $tmp['title'   ],
                $tmp['section' ],
                $tmp['location'],
                $tmp['content' ],
                $this->ctrl->getUserId()
            )
        );

        $this->dto->setSaveSuccess();
        $this->ctrl->updateModificationDate();
        return true;
    }

    protected function getDivisions() {
        $stmt =
            'SELECT `Id`, `Name` ' .
            'FROM `sfw2_division` ' .
            'ORDER BY `Position`';

        return $this->database->select($stmt);
    }

}