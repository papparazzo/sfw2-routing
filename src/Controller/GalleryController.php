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
use SFW2\Core\Config;

class GalleryController extends Controller {

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
        #$this->ctrl->addJSFile('slimbox2');
        #$this->ctrl->addCSSFile('slimbox2');

#        if($this->hasCreatePermission()) {
#            $this->ctrl->addJSFile('crud');
#            $this->ctrl->addJSFile('jquery.fileupload');
#            $this->ctrl->addJSFile('gallery');
#        }

        $tmp = [
            'caption'     => '',
            'description' => ''
        ];

        $content = new Content('content/gallery/summary');
        $content->assign('title', 'Sitemap');

        $content->assign('galleries',  $this->loadSummary());
        $content->assign('tmp',        $tmp);
        $content->assign('caption',    $this->title ? $this->title : 'Gallerieübersicht');
        $content->assign('title',      $this->title);
        #FIXME $view->assign('modiDate',   $this->ctrl->getModificationDate());
        $content->assign('editable',   true || $this->ctrl->hasCreatePermission());
        $content->assign('webmaster', $this->config->getVal('project', 'eMailWebMaster'));
#        $view->assign('webmaster',  new \SFW\View\Helper\Obfuscator\EMail(
#            $this->conf->getVal('project', 'eMailWebMaster'),
#            $this->conf->getVal('project', 'eMailWebMaster')
#        ));
        return $content;
    }








            public function create() {
                return $this->createGallery();
            }

            public function delete() {
                $this->deleteGallery($this->dto->getNumeric('id'));
                return
                    $this->dto->getErrorProvider()->getContent() .
                    $this->getSummary($page);
            }

            public function undelete() {
                $this->undeleteGallery($this->dto->getNumeric('id'));
                return
                    $this->dto->getErrorProvider()->getContent() .
                    $this->getSummary($page);
            }

    public function showgallery() {
        return $this->getGallery(45); #$this->dto->getNumeric('g'), $page);
    }

    public function deleteImg() {
        $this->deleteImage(
            $this->dto->getNumeric('g'),
            $this->dto->getFileName('id')
        );
        return
            $this->dto->getErrorProvider()->getContent() .
            $this->getGallery($this->dto->getNumeric('g'), $page);
    }

    public function chgprevImg() {
        $this->changePrevImg(
            $this->dto->getNumeric('g'),
            $this->dto->getFileName('id')
        );
        return
            $this->dto->getErrorProvider()->getContent() .
            $this->getGallery($this->dto->getNumeric('g'), $page);
    }

    public function addImg() {
        $galid = $this->addImg($this->dto->getNumeric('g'));

        $stmt =
            "SELECT `sfw_media`.`Path`, `sfw_imagegalleries`.`PreviewImage`, " .
            "`sfw_media`.`Id` " .
            "FROM `sfw_imagegalleries` " .
            "LEFT JOIN `sfw_media` " .
            "ON `sfw_media`.`Id` = `sfw_imagegalleries`.`MediaId` " .
            "WHERE `sfw_imagegalleries`.`Id` = '%s' ";

        $rv = $this->db->selectRow($stmt, array($galid));

        if(empty($rv)) {
            throw new SFW_Exception(__METHOD__ . ': no gallery fetched!');
        }

        if(!is_dir($rv["Path"] . '/thumb/')) {
            throw new SFW_Exception(
                __METHOD__ . ': path <' . $rv["Path"] . '> is invalid'
            );
        }

        $stmt =
            "UPDATE `sfw_media` " .
            "SET `sfw_media`.`Deleted` = '0' " .
            "WHERE `sfw_media`.`Id` = '%s'";

        if($this->db->update($stmt, array($rv['Id'])) != 1) {
            $this->dto->getErrorProvider()->addError(
                SFW_Error_Provider::ERR_UNDEL,
                array('<NAME>' => 'Die Galerie')
            );
        }

        $chunk = explode(';', $this->dto->getData('file'));
        $type = explode(':', $chunk[0]);
        $type = $type[1];
        $data = explode(',', $chunk[1]);

        switch($type) {
            case 'image/pjpeg':
            case 'image/jpeg':
            case 'image/jpg':
                $type = 'jpg';
                break;

            case 'image/png':
            case 'image/x-png':
                $type = 'png';
                break;

            default:
                return array(
                    'error' => true,
                    'msg'   => 'Es wurde eine ungültige Datei übermittelt.' .
                    $chunk[0] . print_r($_REQUEST, true) . print_r($_FILES, true)
                );
        }

        $cnt = count(glob($rv["Path"] . '/high/*'));
        if($cnt >= 999) {
            throw new SFW_Exception(
                'more then <' . $cnt . '> images are not allowed'
            );
        }

        $filename = str_repeat('0', 4 - mb_strlen('' . $cnt)) . ++$cnt . '.' . $type;

        if(!file_put_contents(
            $rv["Path"] . '/high/' . $filename,
            base64_decode($data[1]))
        ) {
            throw new SFW_Exception(
                'could not store file <' . $filename .
                '> in path <' . $rv["Path"] . '/high/>'
            );
        }

        $this->generateThumb(
            $filename,
            170,
            $rv["Path"] . '/high/', $rv["Path"] . '/thumb/'
        );
        $this->generateThumb(
            $filename,
            335,
            $rv["Path"] . '/high/', $rv["Path"] . '/regular/'
        );

        if($rv['PreviewImage'] == '') {
            $this->changePrevImg($galid, $filename);
        }

        $this->ctrl->updateModificationDate();

        return array(
            'error' => false,
            'msg' => 'Alles chick.'
        );



            }



    protected function createGallery() {
        $tmp = array(
            'caption'     => '',
            'description' => ''
        );

        $tmp['caption'] = $this->dto->getTitle(
            'caption',
            true,
            'Der Galleriename'
        );

        $tmp['description'] = $this->dto->getTitle(
            'description',
            true,
            'Die Beschreibung'
        );

        if(
            $this->dto->getErrorProvider()->hasErrors() ||
            $this->dto->getErrorProvider()->hasWarning()
        ) {
            return
                $this->dto->getErrorProvider()->getContent() .
                $this->getSummary(0, $tmp);
        }

        $folder = \SFW\Helper::createFolder(SFW_GALLERY_PATH, $tmp['caption']);

        if(
            !mkdir(SFW_GALLERY_PATH . $folder . '/thumb'  ) ||
            !mkdir(SFW_GALLERY_PATH . $folder . '/regular') ||
            !mkdir(SFW_GALLERY_PATH . $folder . '/high'   )
        ) {
            throw new \SFW\Gallery\Exception(
                'could not create gallery-path <' .
                SFW_GALLERY_PATH . $folder . '>',
                \SFW\Gallery\Exception::COULD_NOT_CREATE_GALLERY_PATH
            );
        }

        $sections = $this->db->selectKeyValue(
            'Module',
            'DivisionId',
            'sfw_division'
        );

        $stmt =
            "INSERT INTO `sfw_media` " .
            "SET " .
            "`UserId` = '%s', " .
            "`Token` = '%s', " .
            "`Name` = '%s', " .
            "`Description` = '%s', " .
            "`DivisionId` = '%s', " .
            "`CreationDate` = NOW(), " .
            "`ActionHandler` = 'zipFolder', " .
            "`Path` = '%s', " .
            "`FileType` = 'zip', " .
            "`Deleted` = '1'," .
            "`Autogen` = '1';";

        $data = array(
            $this->ctrl->getUserId(),
            md5($tmp['caption'] . $this->ctrl->getUserId() . time()),
            SFW_AuxFunc::getSimplifiedName($tmp['caption']) . '.zip',
            'Alle Bilder der Galerie "' . $tmp['caption'] . '" als ZIP-Datei',
            $sections[strtolower($this->category)], // FIXME: fixen!!!
            SFW_GALLERY_PATH . $folder
        );

        if(!($id = $this->db->insert($stmt, $data)) != 0) {
            throw new \SFW\Gallery\Exception(
                'insertation of gallery in media-table failed! ' .
                SFW_GALLERY_PATH . $folder . '>',
                \SFW\Gallery\Exception::COULD_NOT_INSERT_INTO_MEDIA_TABLE
            );
        }

        $stmt =
            "INSERT INTO `sfw_imagegalleries` " .
            "SET " .
            "`PathId` = '%s', " .
            "`MediaId` = '%s', " .
            "`Description` = '%s', " .
            "`Deleted` = '0'," .
            "`Name` = '%s'";

        $data = array(
            $this->ctrl->getPathId(),
            $id,
            $tmp['description'],
            $tmp['caption']
        );

        if(!($id = $this->db->insert($stmt, $data)) != 0) {
            throw new \SFW\Gallery\Exception(
                'insertation of gallery failed! <' .
                '<' . SFW_GALLERY_PATH . $folder . '>',
                \SFW\Gallery\Exception::INSERTATION_OF_GALLERY_FAILED
            );
        }

        $url =
            '/' . strtolower($this->category) .
            '/bilder?do=showgallery&g=' . $id . '&p=0';

        $this->ctrl->updateModificationDate();

        $view = new SFW_View();
        $view->assign('url', $url);
        $view->assignTpl('JumpTo');
        return $view->getContent();
    }

    protected function deleteGallery($galleryId) {
        if(!$this->ctrl->hasDeletePermission()) {
            return false;
        }

        $stmt =
            "SELECT `sfw_media`.`Path`, `sfw_media`.`Id` " .
            "FROM `sfw_imagegalleries` " .
            "LEFT JOIN `sfw_media`" .
            "ON `sfw_imagegalleries`.`MediaId` = `sfw_media`.`Id` " .
            "WHERE `sfw_imagegalleries`.`Id` = %s";

        $data = $this->db->selectRow($stmt, array($galleryId));
        $path = SFW_GALLERY_PATH . $data['Path'] . '/.htaccess';
        file_put_contents($path, 'deny from all');

        $stmt =
            "UPDATE `sfw_media` " .
            "SET `sfw_media`.`Deleted` = '1' " .
            "WHERE `sfw_media`.`Id` = '%s'";

        if($this->db->update($stmt, array($data['Id'])) > 1) {
            $this->dto->getErrorProvider()->addError(
                SFW_Error_Provider::ERR_DEL,
                array('<NAME>' => 'Die Galerie')
            );
        }

        $stmt =
            "UPDATE `sfw_imagegalleries` " .
            "SET `sfw_imagegalleries`.`Deleted` = '1' " .
            "WHERE `sfw_imagegalleries`.`MediaId` = '%s'";

        if($this->db->update($stmt, array($data['Id'])) != 1) {
            $this->dto->getErrorProvider()->addError(
                SFW_Error_Provider::ERR_DEL,
                array('<NAME>' => 'Die Galerie')
            );
        }

        $this->dto->setSaveSuccess(true);
        return true;
    }

    protected function getGallery($id, $page = 0) {
        $stmt =
            "SELECT `sfw_media`.`Name` AS `FileName`, " .
            "`sfw_imagegalleries`.`Name`, `sfw_media`.`CreationDate`, " .
            "`sfw_imagegalleries`.`Description`, `sfw_media`.`Token`, " .
            "`sfw_media`.`Path`, `sfw_imagegalleries`.`PreviewImage`, " .
            "`sfw_users`.`Email`,  " .
            "CONCAT(`sfw_users`.`FirstName`, ' ', `sfw_users`.`LastName`) " .
            "AS `Creator` " .
            "FROM `sfw_imagegalleries` " .
            "LEFT JOIN `sfw_media` " .
            "ON `sfw_media`.`Id` = `sfw_imagegalleries`.`MediaId` " .
            "LEFT JOIN `sfw_division` " .
            "ON `sfw_division`.`DivisionId` = `sfw_media`.`DivisionId` " .
            "LEFT JOIN `sfw_users` " .
            "ON `sfw_users`.`Id` = `sfw_media`.`UserId` " .
            "WHERE `sfw_imagegalleries`.`Id` = '%s' " .
            "AND `sfw_division`.`Module` = '%s' ";

        if(!$this->ctrl->isAdmin()) {
            $stmt .= "AND `sfw_imagegalleries`.`Deleted` = '0'";
        }

        $rv = $this->db->selectRow($stmt, array($id, $this->category));

        if(empty($rv)) {
            throw new \SFW\Gallery\Exception(
                'no gallery fetched!',
                \SFW\Gallery\Exception::NO_GALLERY_FETCHED
            );
        }
        if(!is_dir($rv['Path'] . '/thumb/')) {
            throw new \SFW\Gallery\Exception(
                'path <' . $rv['Path'] . '> is invalid',
                \SFW\Gallery\Exception::INVALID_PATH
            );
        }

        $dir = dir($rv['Path'] . '/thumb/');
        $pics = array();

        while(false !== ($entry = $dir->read())) {
            if($entry == '.' || $entry == '..') {
                continue;
            }

            $fi = pathinfo($rv['Path'] . '/thumb/' . $entry);

            if(
                !$this->ctrl->hasDeletePermission() &&
                strtolower($fi['extension']) != 'jpg' &&
                strtolower($fi['extension']) != 'png'
            ) {
                continue;
            }
            if(
                $this->ctrl->hasDeletePermission() &&
                strtolower($fi['extension']) != 'jpg' &&
                strtolower($fi['extension']) != 'png' &&
                strtolower($fi['extension']) != 'del'
            ) {
                continue;
            }
            $pic = array();
            $pic['lnk'] = '/' . $rv['Path'] . '/high/' . $entry;
            $pic['ttp'] = $entry;
            $pic['src'] = '/' . $rv['Path'] . '/thumb/' . $entry;
            $pic['del'] = (strtolower($fi['extension']) == 'del');
            $pic['pre'] = ($rv['PreviewImage'] == $entry);
            $pics[] = $pic;
        }

        $dir->close();

        rsort($pics);

        $count = count($pics);

        $crDate = new \SFW\View\Helper\Date($rv['CreationDate'], new \SFW\Locale());
        $view   = new \SFW\View();
        $view->assign('name',              $rv['Name']);
        $view->assign('filename',          $rv['FileName']);
        $view->assign('page',              (int)$page);
        $view->assign('description',       $rv['Description']);
        $view->assign('creationDate',      $crDate);
        $view->assign('pics',              $pics);
        $view->assign('dllink',            '?getfile=' . $rv['Token']);
        $view->assign('editable',          $this->ctrl->hasDeletePermission());
        $view->assign('galId',             (int)$id);
        $view->assign('maxFileUploads',    ini_get('max_file_uploads'));
        $view->assign('mailaddr',          new \SFW\View\Helper\Obfuscator\EMail(
            $rv['Email'],
            $rv['Creator'],
            'Galerie ' . $rv['Name'] . ' (' .
            $crDate->getFormatedDate(true) . ')'
        ));
        $view->assign('image', '/public/content/users/' . \SFW\Helper::getImageFileName(
 # FIXME: _No hardcoded path
                    'public/content/users/',
                    $row['FirstName'],
                    $row['LastName']
            ));
        $view->assignTpl(
            $this->conf->getTemplateFile('Gallery/Gallery')
        );
        return $view->getContent();
    }

    protected function deleteImage($galid, $fileName) {
        if(!$this->ctrl->hasDeletePermission()) {
            return false;
        }

        $stmt =
            "SELECT `sfw_media`.`Path`, `sfw_imagegalleries`.`PreviewImage` " .
            "FROM `sfw_imagegalleries` " .
            "LEFT JOIN `sfw_media` " .
            "ON `sfw_media`.`Id` = `sfw_imagegalleries`.`MediaId` " .
            "LEFT JOIN `sfw_division` " .
            "ON `sfw_division`.`DivisionId` = `sfw_media`.`DivisionId` " .
            "WHERE `sfw_imagegalleries`.`Id` = '%s' " .
            "AND `sfw_division`.`Module` = '%s' ";

        $rv = $this->db->selectRow($stmt, array($galid, $this->category));

        if(empty($rv)) {
            throw new \SFW\Gallery\Exception(
                'no valid gallery fetched!',
                \SFW\Gallery\Exception::NO_GALLERY_FETCHED
            );
        }

        if($fileName == $rv['PreviewImage']) {
            throw new \SFW\Gallery\Exception(
                'unable to delete preview-img!',
                \SFW\Gallery\Exception::COULD_NOT_DELETE_PREVIEW_IMAGE
            );
        }

        unlink($rv['Path'] . '/thumb/' . $fileName);
        unlink($rv['Path'] . '/regular/' . $fileName);
        unlink($rv['Path'] . '/high/' . $fileName);
        $this->dto->setSaveSuccess(treu);

        return true;
    }

    protected function loadSummary() {
        return [];
        $stmt =
            "SELECT `sfw_imagegalleries`.`Id`, `sfw_imagegalleries`.`Name`, " .
            "`sfw_media`.`Name` as `FileName`, " .
            "`sfw_imagegalleries`.`Description`, " .
            "`sfw_media`.`CreationDate`, `sfw_imagegalleries`.`Deleted`, " .
            "`sfw_media`.`Path`, `sfw_users`.`Email`, `sfw_media`.`Token`, " .
            "`sfw_imagegalleries`.`PreviewImage`, " .
            "CONCAT(`sfw_users`.`FirstName`, ' ', `sfw_users`.`LastName`) " .
            "AS `Creator` " .
            "FROM `sfw_imagegalleries` " .
            "LEFT JOIN `sfw_media` " .
            "ON `sfw_media`.`Id` = `sfw_imagegalleries`.`MediaId` " .
            "LEFT JOIN `sfw_users` " .
            "ON `sfw_users`.`Id` = `sfw_media`.`UserId` " .
            "LEFT JOIN `sfw_division` " .
            "ON `sfw_division`.`DivisionId` = `sfw_media`.`DivisionId` " .
            "WHERE `sfw_division`.`Module` = '%s' ";

        if(!$this->ctrl->hasCreatePermission()) {
            $stmt .= "AND `sfw_imagegalleries`.`PreviewImage` != '' ";
        }

        if(!$this->ctrl->isAdmin()) {
            $stmt .= "AND `sfw_imagegalleries`.`Deleted` = '0' ";
        }

        $stmt .= "ORDER BY `sfw_imagegalleries`.`Id` DESC ";
        $rows = $this->db->select(
            $stmt,
            array($this->category)
        );

        $galleries = array();

        foreach($rows as $row) {
            $entry = array();
            $entry['id'         ] = $row['Id'];
            $entry['name'       ] = $row['Name'];
            $entry['filename'   ] = $row['FileName'];
            $entry['description'] = $row['Description'];
            $entry['date'       ] = new \SFW\View\Helper\Date(
                $row['CreationDate'],
                new \SFW\Locale()
            );
            $entry['preview'    ] = $this->getPreviewPath(
                $row['Path'],
                $row['PreviewImage']
            );
            $entry['dllink'     ] = '?getfile=' . $row['Token'];
            $entry['creator'    ] = new \SFW\View\Helper\Obfuscator\EMail(
                $row['Email'],
                $row['Creator'],
                'Galerie ' . $row['Name'] . ' (' .
                $entry['date']->getFormatedDate(true) . ")"
            );
            $entry['deleted'    ] = $row['Deleted'] ? true : false;
            $galleries[] = $entry;
        }
        return $galleries;
    }

    private function getPreviewPath($path, $file) {
        #if($file == '') {
            return '/public/images/content/thumb/empty.png';
        #}

        if(is_file($path . '/thumb/' . $file)) {
            return '/' . $path . '/thumb/' . $file;
        }
        throw new \SFW\Gallery\Exception(
            'preview <' . $path . '/thumb/' . $file . '> does not exist',
            \SFW\Gallery\Exception::PREVIEW_FILE_DOES_NOT_EXIST
        );
    }

    private function generateThumb($file, $size, $src, $des) {
        if(!is_file($src . '/' . $file)) {
            return false;
        }

        list($srcWidth, $srcHeight, $srcTyp) = getimagesize($src . '/' . $file);

        if($srcWidth >= $srcHeight) {
            $desWidth = $size;
            $desHeight = $srcHeight / $srcWidth * $size;
        } else {
            $desHeight = $size;
            $desWidth = $srcWidth / $srcHeight * $size;
        }

        switch($srcTyp) {
            case IMAGETYPE_JPEG:
                $old = imagecreatefromjpeg($src . '/' . $file);
                $new = imagecreatetruecolor($desWidth, $desHeight);
                imagecopyresampled(
                    $new,
                    $old,
                    0,
                    0,
                    0,
                    0,
                    $desWidth,
                    $desHeight,
                    $srcWidth,
                    $srcHeight
                );
                imagejpeg($new, $des . '/' . $file, 100);
                imagedestroy($old);
                imagedestroy($new);
                return true;

            case IMAGETYPE_PNG:
                $old = imagecreatefrompng($src . '/' . $file);
                $new = imagecreatetruecolor($desWidth, $desHeight);
                imagecopyresampled(
                    $new,
                    $old,
                    0,
                    0,
                    0,
                    0,
                    $desWidth,
                    $desHeight,
                    $srcWidth,
                    $srcHeight
                );
                imagepng($new, $des . '/' . $file);
                imagedestroy($old);
                imagedestroy($new);
                return true;
        }
    }

    private function changePrevImg($id, $fileName) {
        if(!$this->ctrl->hasCreatePermission()) {
            return false;
        }

        $stmt =
            "SELECT `sfw_media`.`Path`, `sfw_imagegalleries`.`PreviewImage` " .
            "FROM `sfw_imagegalleries` " .
            "LEFT JOIN `sfw_media` " .
            "ON `sfw_media`.`Id` = `sfw_imagegalleries`.`MediaId` " .
            "LEFT JOIN `sfw_division` " .
            "ON `sfw_division`.`DivisionId` = `sfw_media`.`DivisionId` " .
            "WHERE `sfw_imagegalleries`.`Id` = '%s' " .
            "AND `sfw_division`.`Module` = '%s' ";

        $rv = $this->db->selectRow($stmt, array($id, $this->category));

        if(empty($rv)) {
            throw new \SFW\Gallery\Exception(
                'no valid gallery fetched!',
                \SFW\Gallery\Exception::NO_GALLERY_FETCHED
            );
        }

        if($fileName == $rv['PreviewImage']) {
            throw new \SFW\Gallery\Exception(
                'unable to change preview-img!',
                \SFW\Gallery\Exception::COULD_NOT_CHANGE_PREVIEW_IMAGE
            );
        }

        // Prüfen, ob Bild im Ordner vorhanden...
        $file = $rv["Path"] . '/thumb/' . $fileName;

        if(!is_file($file)) {
            throw new \SFW\Gallery\Exception(
                'file <' . $file . '> is not a valid image!',
                \SFW\Gallery\Exception::INVALID_IMAGE
            );
        }

        $stmt =
            "UPDATE `sfw_imagegalleries` " .
            "SET `PreviewImage` = '%s' " .
            "WHERE `Id` = %s";

        if($this->db->update($stmt, array($fileName, $id)) != 1) {
            throw new \SFW\Gallery\Exception(
                'updating imagegalleries failed!',
                \SFW\Gallery\Exception::UPDATING_GALLERY_FAILED
            );
        }
        $this->dto->setSaveSuccess();
        return true;
    }
}