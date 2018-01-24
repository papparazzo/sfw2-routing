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
use SFW2\Core\Config;
use SFW2\Routing\Result\Content;
use SFW2\Core\Helper;

class NewspaperController extends Controller {

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Config
     */
    protected $config;

    protected $title;

    public function __construct(int $pathId, Database $database, Config $config, string $title = null) {
        parent::__construct($pathId);
        $this->database = $database;
        $this->config = $config;
        $this->title = $title;
    }

    public function index() {
        $content = new Content('content/newspaper/Newspaperarticles');
        $content->assign('title', 'Pressemitteilungen [' . $this->title . ']');
        $content->assign('about', ''.$this->title);
        $content->assign('editable', true);
        $content->assign('mailaddr', $this->config->getVal('project', 'eMailWebMaster'));
        $content->assign('items', $this->loadEntries());
        $content->assign('tmp', [
            'title'     => 'Neuer Titel',
            'date'      => '01. April 2012',
            'newspaper' => 'Spiegel'
        ]);

        return $content;


#        $this->ctrl->addJSFile('slimbox2');
 #       $this->ctrl->addCSSFile('slimbox2');

#    FIXME    if($this->ctrl->hasCreatePermission()){
#            $this->ctrl->addJSFile('crud');
  #          $this->ctrl->addJSFile('jquery.fileupload');
   #         $this->ctrl->addJSFile('newspaperarticles');
    #    }

        $hasErrors = false;
#        if(
#            $this->dto->getErrorProvider()->hasErrors() ||
 #           $this->dto->getErrorProvider()->hasWarning()
  #      ) {
   #         $hasErrors = $this->ctrl->hasCreatePermission();
    #    }

#        $navi = new SFW_NavigationBar($page, $maxPage, false);

    }

    public function delete() {
        if(!$this->hasDeletePermission()) {
            return false;
        }
        $entryId = $this->dto->getNumeric('id');
        $params = array($entryId);
        $stmt =
            "DELETE FROM `sfw_newspaperarticles` " .
            "WHERE `Id` = %s ";

        if(!$this->isAdmin()) {
            $stmt .= "AND `UserId` = '%s'";
            $params[] = $this->ctrl->getUserId();
        }

        if($this->db->update($stmt, $params) != 1) {
            $this->dto->getErrorProvider()->addError(
                SFW_Error_Provider::ERR_DEL,
                array('<NAME>' => 'Der Presseartikel')
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
            'title',
            true,
            'Die Überschrift'
        );

        $tmp['date'] = $this->dto->getDate(
            'date',
            true,
            'Das Veröffentlichskeitsdatum'
        );

        $tmp['newspaper'] = $this->dto->getTitle(
            'newspaper',
            true,
            'Die Quelle'
        );

        $data = $this->dto->getData('filecontent');
        if($data == null) {
            $this->dto->getErrorProvider()->addError(
                SFW_Error_Provider::NO_FILE,
                array(),
                'dropzone_fileuploadArea_' . $this->getPageId()
            );
        }

        if(
            $this->dto->getErrorProvider()->hasErrors() ||
            $this->dto->getErrorProvider()->hasWarning()
        ) {
            return false;
        }

        $img = new SFW_Image(
            SFW_GALLERY_PATH . '/presse_' . $this->ctrl->getPathId()
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

        $this->db->insert(
            $stmt,
            array(
                $this->ctrl->getUserId(),
                $this->ctrl->getPathId(),
                $tmp['title'    ],
                $tmp['date'     ],
                $img->storeImage($data),
                $tmp['newspaper']
            )
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
        $entries = array();

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
            $this->config->getVal('path', 'gallery') . $this->ctrl->getPathId() . '/0/'
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
