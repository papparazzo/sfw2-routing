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
use SFW2\Core\Helper;
use SFW2\Core\Config;
use SFW2\Routing\Widget\Obfuscator\EMail;
use SFW2\Routing\User;
use SFW2\Routing\Result\Content;

use DateTime;
use DateTimeZone;

class NewspaperController extends Controller {

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var User
     */
    protected $user;

    protected $title;

    public function __construct(int $pathId, Database $database, Config $config, User $user, string $title = null) {
        parent::__construct($pathId);
        $this->database = $database;
        $this->user = $user;
        $this->title = $title;
        $this->config = $config;
    }

    public function index($all = false) {

        $content = new Content('content/newspaper/Newspaperarticles');
        $content->assign('title', 'Pressemitteilungen [' . $this->title . ']');
        $content->assign('about', ''.$this->title);
        $content->assign('mailaddr', (string)(new EMail(
            $this->config->getVal('project', 'eMailWebMaster'),
            $this->config->getVal('project', 'eMailWebMaster')
        )));
        $content->assign('items', $this->loadEntries());
        $content->assign('tmp', [
            'title'     => 'Neuer Titel',
            'date'      => '01. April 2012',
            'newspaper' => 'Spiegel'
        ]);
        $content->appendJSFile('slimbox2');
        $content->appendCSSFile('slimbox2');

        $content->appendJSFile('crud');
        $content->appendJSFile('jquery.fileupload');
        $content->appendJSFile('newspaperarticles');


        return $content;
/*
            $cd = strftime(
                '%a., %d. %B %G',
                (new DateTime($row['CreationDate'], new DateTimeZone('Europe/Berlin')))->getTimestamp()
            );
*/



        $hasErrors = false;
#        if(
#            $this->dto->getErrorProvider()->hasErrors() ||
 #           $this->dto->getErrorProvider()->hasWarning()
  #      ) {
   #         $hasErrors = $this->ctrl->hasCreatePermission();
    #    }



    }

    public function delete($all = false) {
        $entryId = $this->dto->getNumeric('id');
        $params = [$entryId];
        $stmt =
            "DELETE FROM `sfw_newspaperarticles` " .
            "WHERE `Id` = %s ";

        if(!$this->isAdmin()) {
            $stmt .= "AND `UserId` = '%s'";
            $params[] = $this->user->getUserId();
        }

        $this->database->delete($stmt, $params);
        #$this->dto->setSaveSuccess(true);
    }

    public function create() {
        $tmp['title'] = $this->dto->getTitle('title', true);
        $tmp['date'] = $this->dto->getDate('date', true);
        $tmp['newspaper'] = $this->dto->getTitle('newspaper', true);

        $data = $this->dto->getData('filecontent');
        if($data == null) {
            $this->dto->getErrorProvider()->addError(
                SFW_Error_Provider::NO_FILE,
                [],
                'dropzone_fileuploadArea_' . $this->pathId
            );
        }

        if(
            $this->dto->getErrorProvider()->hasErrors() ||
            $this->dto->getErrorProvider()->hasWarning()
        ) {
            return false;
        }

        $img = new SFW_Image(
            SFW_GALLERY_PATH . '/presse_' . $this->getPathId()
        );

        $stmt =
            "INSERT INTO `sfw_newspaperarticles` " .
            "SET `UserId` = '%s', " .
            "`PathId` = '%s', " .
            "`Title` = '%s', " .
            "`Date` = '%s', " .
            "`FileName` = '%s', " .
            "`Source` = '%s', " .
            "`Deleted` = '0'";

        $this->database->insert(
            $stmt,
            [
                $this->user->getUserId(),
                $this->pathId,
                $tmp['title'    ],
                $tmp['date'     ],
                $img->storeImage($data),
                $tmp['newspaper']
            ]
        );

        $tmp['title'    ] = '';
        $tmp['date'     ] = '';
        $tmp['newspaper'] = '';

        $this->dto->setSaveSuccess();
        $this->ctrl->updateModificationDate();

        return array(
            'error' => false,
            'msg' => 'Alles chick.'
        );
    }

    private function loadEntries() {
        $view = new \SFW2\Core\View('web/templates/content/newspaper/Newspaperarticle.phtml');
        $view->assignArray([
            'id' => 1,
            'image' => '/public/layout/zeitung/wisentrun2.png',
            'title' => 'Alle Jahre wieder',
            'mailaddr' => 'stefan.paproth@vfvconcordia.de',
            'source' => 'Spiegel',
            'date' => '12.04.1977',
            'delAllowed' => true
        ]);
        return [
            '1' => $view->getContent(),
        ];



        # $offset = $page * self::ENTRIES_PER_PAGE;
        $entries = [];

        $stmt =
            "SELECT `sfw_newspaperarticles`.`Id`, `Title`, `Date`, `Source`, " .
            "`sfw_users`.`Email`, `FileName`, `Deleted`, `HasBorder`, " .
            "IF((`sfw_newspaperarticles`.`UserId` = '%s' OR '%s') " .
            "AND `sfw_newspaperarticles`.`Deleted` = '0', '1', '0') " .
            "AS `DelAllowed` ,`sfw_users`.`FirstName`, `sfw_users`.`LastName`" .
            "FROM `sfw_newspaperarticles` " .
            "LEFT JOIN `sfw_users` " .
            "ON `sfw_users`.`Id` = `sfw_newspaperarticles`.`UserId` " .
            "WHERE `sfw_newspaperarticles`.`PathId` = '%s' ";

        $stmt .= "ORDER BY `Date` DESC";

        $rows = $this->config->database->select(
            $stmt,
            array(
                1, #$this->ctrl->getUserId(),
                1, #$this->ctrl->isAdmin() ? '1' : '0',
                2, #$this->ctrl->getPathId()
            )#,
           # $offset,
           # self::ENTRIES_PER_PAGE
        );

        $img = new \SFW\Image(
            $this->config->getVal('path', 'gallery') . $this->pathId . '/0/'
        );

        foreach($rows as $row) {
            $entry = array();
            $cd = new \SFW\View\Helper\Date($row['Date']);
            $entry['id'         ] = $row['Id'];
            $entry['deleted'    ] = (bool)$row['Deleted'];
            $entry['delAllowed' ] = (bool)$row['DelAllowed'];
            $entry['title'      ] = $row["Title"];
            $entry['source'     ] = $row["Source"]?$row["Source"]:'[unbekannt]';
            $entry['file'       ] = $img->getImage($row["FileName"]);
            $entry['date'       ] = $cd;
            $entry['hasBorder'  ] = (bool)$row['HasBorder'];
            $entry['mailaddr'   ] = new \SFW\View\Helper\Obfuscator\EMail(
                $row["Email"],
                $row["FirstName"] . ' ' . $row["LastName"],
                "Zeitungsartikel vom " . $cd->getFormatedDate(true)
            );
            $entry['image'      ] = '/public/layout/' . Helper::getImageFileName(
                    # FIXME: _No hardcoded path
                    'public/layout/',
                    $row['FirstName'],
                    $row['LastName']
            );

            $view = new \SFW\View($this->getPageId());
            $view->assignArray($entry);
            $view->assignTpl(
                $this->conf->getTemplateFile('PageContent/Newspaperarticle')
            );
            $entries[] = $view->getContent();
        }
        return $entries;
    }
}
