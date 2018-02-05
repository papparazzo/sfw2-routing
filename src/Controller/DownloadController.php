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

use SFW2\Core\Config;
use SFW2\Core\Database;

class DownloadController extends Controller {

    use GetDivisionTrait;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Config
     */
    protected $config;

    protected $title;

    public function __construct(int $pathId, Database $database, Config $config, User $user, string $title = null) {
        parent::__construct($pathId);
        $this->database = $database;
        $this->user = $user;
        $this->title = $title;
        $this->config = $config;
        $this->clearTmpFolder();
    }

    public function index($all = false) {
# FIXME
#            $this->ctrl->addJSFile('crud');
#            $this->ctrl->addJSFile('jquery.fileupload');
#            $this->ctrl->addJSFile('download');

        $content = new Content('content/download');
        $content->assign('entries',  $this->loadEntries());
        $content->assign('title',    $this->title);
        $content->assign('divisions', $this->getDivisions());

        #FIXME $view->assign('modiDate',   $this->ctrl->getModificationDate());
        $content->assign('mailaddr', (string)(new EMail(
            $this->config->getVal('project', 'eMailWebMaster'),
            'Bescheid.'
        )));
        return $content;
    }

    protected function loadEntries() {
        $stmt =
            "SELECT `sfw2_media`.`Name`, `sfw2_media`.`CreationDate`, " .
            "`sfw2_media`.`Description`, `sfw2_media`.`FileType`, " .
            "`sfw2_media`.`Autogen`, `sfw2_user`.`Email`, " .
            "`sfw2_media`.`Token`,`sfw2_division`.`Name` AS `Category`, " .
            "IF(`sfw2_media`.`UserId` = '%s' OR '%s', '1', '0') AS `DelAllowed`, " .
            "`sfw2_user`.`FirstName`, `sfw2_user`.`LastName` " .
            "FROM `sfw2_media` " .
            "LEFT JOIN `sfw2_division` " .
            "ON `sfw2_division`.`Id` = `sfw2_media`.`DivisionId` " .
            "LEFT JOIN `sfw2_user` " .
            "ON `sfw2_user`.`Id` = `sfw2_media`.`UserId` ";

        $rows = $this->database->select(
            $stmt,
            [$this->user->getUserId(), $this->user->isAdmin() ? '1' : '0']
        );

        $entries = [];

        foreach($rows as $row) {
            $entry = [];
            $entry['description'] = $row['Description'];
            $entry['token'      ] = $row['Token'      ];
            $entry['filename'   ] = $row['Name'       ];
            $entry['auto'       ] = (bool)$row['Autogen'];
            $entry['delAllowed' ] = (bool)$row['DelAllowed'];
           # $entry['email'      ] = $this->getShortName($row);
            $entry['addFileInfo'] = $this->getAdditionalFileInfo($row);
            $entry['icon'       ] =
                '/public/layout/icon_' . $row['FileType'] . '.png';
            $entries[$row['Category']][] = $entry;
        }
        return $entries;
    }

    public function delete($all = false) {
        $stmt =
            "DELETE FROM `sfw2_media` " .
            "WHERE `sfw2_media`.`Token` = '%s' " .
            "AND `Autogen` = '0'";

        if(!$all) {
            $stmt .=
                "AND `UserId` = '" .
                $this->database->escape($this->user->getUserId()) . "'";
        }

        $this->database->delete($stmt, [$this->dto->getSimpleText('id')]);
        #$this->dto->setSaveSuccess(true);
        return;
    }

    private function insertFile(&$tmp) {
        if(strtolower($_SERVER['REQUEST_METHOD']) != 'post') {
            $this->dto->getErrorProvider()->addError(
                SFW_Error_Provider::INT_ERR,
                array(),
                'dropzone_' . $this->getPageId()
            );
        }

        $tmp['title'] = $this->dto->getTitle('title', true);
        $tmp['section'] = $this->dto->getArrayValue('section', true, $this->sections);

        if(
            !array_key_exists('userfile', $_FILES) ||
            $_FILES['userfile']['error'] != 0 ||
            $_FILES['userfile']['tmp_name'] == ''
        ) {
            $this->dto->getErrorProvider()->addError(
                SFW_Error_Provider::NO_FILE,
                array(),
                'dropzone_' . $this->getPageId()
            );
        }

        if(
            $this->dto->getErrorProvider()->hasErrors() ||
            $this->dto->getErrorProvider()->hasWarning()
        ) {
            return false;
        }

        $file = $_FILES['userfile'];
        $path = SFW_DATA_PATH . $this->pathId . '/';

        if(!is_dir($path) && !mkdir($path)) {
            throw new SFW_Exception(
                'could not create path <' . $path . '>'
            );
        }

        $token = md5($file['tmp_name'] . getmypid() . SFW_AuxFunc::getRandomInt());

        if(is_file($path . $token)) {
            throw new SFW_Exception(
                'file <' . $path . $token .'> allready exists.'
            );
        }

        if(!move_uploaded_file($file['tmp_name'], $path . $token)) {
            throw new SFW_Exception(
                'could not move file <' . $file['tmp_name'] . '> to <' .
                $path . $token .'>'
            );
        }

        $stmt =
            "INSERT INTO `sfw2_media` " .
            "SET `Token` = '%s', " .
            "`UserId` = '%s', " .
            "`Name` = '%s', " .
            "`Description` = '%s', " .
            "`DivisionId` = '%s', " .
            "`CreationDate` = NOW(), " .
            "`ActionHandler` = '', " .
            "`Path` = '%s', " .
            "`FileType` = '%s', " .
            "`Deleted` = '0', " .
            "`Autogen` = '0'";

        $this->database->insert(
            $stmt,
            array(
                $token,
                $this->user->getUserId(),
                $file['name'],
                $tmp['title'],
                $tmp['section'],
                $path,
                $this->getFileType($path . $token)
            )
        );

        $tmp['title'  ] = '';
        $tmp['section'] = '';
        $this->dto->setSaveSuccess();
        #$this->ctrl->updateModificationDate();
        return true;
    }

    protected function clearTmpFolder() {
return; # FIXME
        $dir = dir(SFW_TMP_PATH);
        while(false !== ($file = $dir->read())) {
            if($file  == '.' || $file == '..' || $file == '.htaccess') {
                continue;
            }

            if(time() - filemtime(SFW_TMP_PATH . $file) > 60 * 60) {
                unlink(SFW_TMP_PATH . $file);
            }
        }
        $dir->close();
    }

    protected function getFileType($file) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mi = finfo_file($finfo, $file);
        finfo_close($finfo);

        switch($mi) {
            case 'application/pdf':
                return 'pdf';

            case 'application/vnd.ms-excel':
            case 'application/vnd.oasis.opendocument.spreadsheet':
                return 'xls';

            case 'application/msword':
            case 'application/rtf':
            case 'application/vnd.oasis.opendocument.text':
                return 'doc';

            case 'application/vnd.ms-powerpoint':
                return 'ppt';

            case 'application/zip':
            case 'application/x-rar-compressed':
                return 'zip';
        }

        if(strstr($mi,'text/')) {
            return 'txt';
        }
        return 'ukn';
    }

    protected function getAdditionalFileInfo($row) {
        $name = substr($row['FirstName'], 0, 1)  . '. ' . $row['LastName'];
        $date = '1. April';#new \SFW\View\Helper\Date($row['CreationDate']);

        if($date != '') {
            return '(' .  $name . '; Stand: ' . $date . ')';
        }
        return '(' . $name . ')';
    }
}
