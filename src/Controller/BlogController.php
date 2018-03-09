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
use SFW2\Routing\Widget\Obfuscator\EMail;
use SFW2\Routing\User;
use SFW2\Routing\Controller\Helper\GetDivisionTrait;
use SFW2\Routing\Resolver\Exception as ResolverException;

use SFW2\Core\Helper;
use SFW2\Core\Database;

use DateTime;
use DateTimeZone;

class BlogController extends Controller {

    use GetDivisionTrait;

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

    public function index($all = false) {
        $content = new Content('content/blog/blog');
        $content->appendJSFile('crud.js');
        #$this->ctrl->addJSFile('ckeditor/ckeditor');
        #$this->ctrl->addJSFile('slimbox2');
        #$this->ctrl->addJSFile('jquery.comments');
        #$this->ctrl->addCSSFile('slimbox2');
        #$this->ctrl->addCSSFile('comments');

        $content->assign('divisions', $this->getDivisions());
        $content->assign('isAdmin', $this->user->isAdmin());
        $content->assign('title', (string)$this->title);
        $content->assign('items', $this->loadEntries());
        return $content;
    }

    protected function loadEntries() {
        $entries = [];

        $stmt =
            "SELECT `sfw2_blog`.`Id`, `sfw2_blog`.`CreationDate`, " .
            "`sfw2_user`.`Email`, `sfw2_blog`.`Content`, " .
            "`sfw2_blog`.`Title`, `sfw2_user`.`FirstName`, `sfw2_user`.`LastName`, " .
            "`sfw2_division`.`Name` AS `Resource`, " .
            "IF(`sfw2_blog`.`UserId` = '%s', '1', '0') AS `OwnEntry` " .
            "FROM `sfw2_blog` " .
            "LEFT JOIN `sfw2_user` " .
            "ON `sfw2_user`.`Id` = `sfw2_blog`.`UserId` " .
            "LEFT JOIN `sfw2_division` " .
            "ON `sfw2_division`.`Id` = `sfw2_blog`.`DivisionId` " .
            "WHERE `PathId` = '%s' " .
            "ORDER BY `sfw2_blog`.`Id` DESC ";

        $rows = $this->database->select($stmt, [$this->user->getUserId(), $this->pathId]);

        foreach($rows as $row) {
            $cd = strftime(
                '%a., %d. %B %G',
                (new DateTime($row['CreationDate'], new DateTimeZone('Europe/Berlin')))->getTimestamp()
            );

            $entry = [];
            $entry['id'      ] = $row['Id'];
            $entry['date'    ] = $cd;
            $entry['title'   ] = $row['Title'];
            $entry['content' ] = $row['Content'];
            $entry['resname' ] = $row['Resource'];
            $entry['ownEntry'] = (bool)$row['OwnEntry'];

            $entry['image'      ] = '/public/layout/' . Helper::getImageFileName(
                # FIXME: _No hardcoded path
                'public/layout/',
                $row['FirstName'],
                $row['LastName']
            );

            $entry['mailaddr'   ] = (string)(new EMail(
                $row["Email"],
                $row['FirstName'] . ' ' . $row['LastName'],
                "Blogeintrag vom " . $cd
             ));

            $entries[] = $entry;
        }
        return $entries;
    }

    public function delete($all = false) {
        $entryId = $this->dto->getNumeric('id');
        $stmt =
            "DELETE ".
            "FROM `sfw2_blog` " .
            "WHERE `Id` = %s " .
            "AND `PathId` = '%s'";

        if(!$all) {
            $stmt .=
                "AND `UserId` = '" .
                $this->database->escape($this->user->getUserId()) . "'";
        }
        if(!$this->database->delete($stmt, [$entryId, $this->pathId])) {
            throw new ResolverException("no entry found", ResolverException::NO_PERMISSION);
        }
        $content = new Content('content/blog/blog');
        $content->append('hallo', 'balli');
        return $content;
    }

    public function create() {
        $stmt =
            "INSERT INTO `sfw2_blog` " .
            "SET `CreationDate` = NOW(), " .
            "`Title` = '%s', " .
            "`Content` = '%s', " .
            "`UserId` = %d, " .
            "`PathId` = %d, " .
            "`DivisionId` = '%s' ";

        $this->database->insert(
            $stmt,
            [
                $this->dto->getTitle('title', true),
                $this->dto->getTitle('content', true),
                $this->user->getUserId(),
                $this->pathId,
                $this->dto->getArrayValue('division', true, $this->sections)
            ]
        );

        #$this->ctrl->updateModificationDate();
        return true;
    }
}