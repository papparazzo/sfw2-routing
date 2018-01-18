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
use SFW2\Core\Database;

class DownloadController extends Controller {

    /**
     * @var Database
     */
    protected $database;

    public function __construct(int $pathId, Database $database) {
        parent::__construct($pathId);
        $this->database = $database;
        $this->clearTmpFolder();
    }

    public function index() {

#        if($this->ctrl->hasCreatePermission()) {
# FIXME
#            $this->ctrl->addJSFile('crud');
#            $this->ctrl->addJSFile('jquery.fileupload');
#            $this->ctrl->addJSFile('download');
#        }


        $tmp = array(
            'title'    => ''
        );

        $content = new Content('content/download');
        $content->assign('entries',  $this->loadEntries());
        $content->assign('tmp',      $tmp);
        $content->assign('title',    $this->title);

        #FIXME $view->assign('modiDate',   $this->ctrl->getModificationDate());
#        $content->assign('editable',   $this->ctrl->hasCreatePermission());
        $content->assign('mailaddr', $this->config->getVal('project', 'eMailWebMaster'));
#        $view->assign('webmaster',  new \SFW\View\Helper\Obfuscator\EMail(
#            $this->conf->getVal('project', 'eMailWebMaster'),
#            $this->conf->getVal('project', 'eMailWebMaster')
#        ));
        return $content;
        return
            #FIXME $this->dto->getErrorProvider()->getContent() .
            $view->getContent();
    }

    protected function loadEntries() {
        $stmt =
            "SELECT `sfw_media`.`Name`, `sfw_media`.`CreationDate`, " .
            "`sfw_media`.`Description`, `sfw_media`.`FileType`, " .
            "`sfw_media`.`Deleted`, `sfw_media`.`Autogen`, " .
            "`sfw_media`.`Token`,`sfw_division`.`Name` AS `Category`, " .
            "IF((`sfw_media`.`UserId` = '%s' OR '%s') " .
            "AND `sfw_media`.`Deleted` = '0', '1', '0') AS `DelAllowed`, " .
            "`sfw_users`.`FirstName`, `sfw_users`.`LastName`, " .
            "`sfw_users`.`Email` " .
            "FROM `sfw_media` " .
            "LEFT JOIN `sfw_division` " .
            "ON `sfw_division`.`DivisionId` = `sfw_media`.`DivisionId` " .
            "LEFT JOIN `sfw_users` " .
            "ON `sfw_users`.`Id` = `sfw_media`.`UserId` ";

        $rows = $this->database->select(
            $stmt,
            array(
                $this->ctrl->getUserId(),
                $this->ctrl->isAdmin() ? '1' : '0'
            )
        );

        $entries = [];

        foreach($rows as $row) {
            $entry = [];
            $entry['description'] = $row['Description'];
            $entry['token'      ] = $row['Token'      ];
            $entry['filename'   ] = $row['Name'       ];
            $entry['deleted'    ] = $row['Deleted'    ] ? true : false;
            $entry['auto'       ] = $row['Autogen'    ] ? true : false;
            $entry['delAllowed' ] = $row['DelAllowed' ] ? true : false;
            $entry['email'      ] = $this->getShortName($row);
            $entry['addFileInfo'] = $this->getAdditionalFileInfo($row);
            $entry['icon'       ] =
                '/public/layout/icon_' . $row['FileType'] . '.png';
            $entries[$row['Category']][] = $entry;
        }
        return $entries;
    }

    public function delete() {
        $stmt =
            "DELETE FROM `sfw_media` " .
            "WHERE `sfw_media`.`Token` = '%s' " .
            "AND `Autogen` = '0'";

        if(!$this->ctrl->isAdmin()) {
            $stmt .=
                "AND `UserId` = '" .
                $this->database->escape($this->ctrl->getUserId()) . "'";
        }

        if(
            $this->database->delete(
                $stmt, array($this->dto->getSimpleText('id'))
            ) != 1
        ) {
            $this->dto->getErrorProvider()->addError(
                SFW_Error_Provider::ERR_DEL,
                array('<NAME>' => 'Die Datei')
            );
            return;
        }
        $this->dto->setSaveSuccess(true);
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

        $tmp['title'] = $this->dto->getTitle(
            'title_' . $this->getPageId(),
            true,
            'Die Beschreibung'
        );

        $tmp['section'] = $this->dto->getArrayValue(
            'section_' . $this->getPageId(),
            true,
            'Das Resort',
            $this->sections
        );

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
        $path = SFW_DATA_PATH . $this->ctrl->getPathId() . '/';

        if(!is_dir($path) && !mkdir($path)) {
            throw new SFW_Exception(
                'could not create path <' . $path . '>'
            );
        }

        $token =
            md5($file['tmp_name'] . getmypid() . SFW_AuxFunc::getRandomInt());

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
            "INSERT INTO `sfw_media` " .
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

        $this->db->insert(
            $stmt,
            array(
                $token,
                $this->ctrl->getUserId(),
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
        $this->ctrl->updateModificationDate();
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
        $date = new \SFW\View\Helper\Date($row['CreationDate']);

        if($date != '') {
            return '(' .  $name . '; Stand: ' . $date . ')';
        }
        return '(' . $name . ')';
    }
}
