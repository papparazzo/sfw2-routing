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

namespace SFW2\Routing;

use SFW2\Routing\Dispatcher\Handler\HTML;

class Dispatcher {

    const HTTP_STATUS_INTERNAL_SERVER_ERROR = 0;
    const HTTP_STATUS_NOT_FOUND             = 1;
    const HTTP_STATUS_FORBIDDEN             = 2;


    public function setHeader() {
        switch($i) {
            case self::HTTP_STATUS_INTERNAL_SERVER_ERROR:
                header("HTTP/1.0 500 Internal Server Error");
                break;

            case self::HTTP_STATUS_FORBIDDEN:
                header("HTTP/1.0 403 Forbidden");
                break;

            case self::HTTP_STATUS_NOT_FOUND:
                header("HTTP/1.0 404 Not Found");
                break;
        }
    }

    protected function dispatchRequestType() {

        if(is_file($data['content'])) {
            return new Dispatcher\Handler\FileDownload();
        }
        if(isset($this->server['HTTP_X_REQUESTED_WITH']) && !is_array($data['content'])) {
            return new Dispatcher\Handler\XML($this->registry, $data);
        }
        if(is_array($data['content'])) {
            return new Dispatcher\Handler\Json($this->registry, $data);
        }
        return new HTML($this->registry, $data);
    }

    public function handleXML() {
        header('Content-type: text/xml');
        echo '<?xml version="1.0" encoding="utf-8"?>';
        echo $this->container['content'];
    }

    public function handleJSON() {
        if(!isset($this->container['content']['error'])) {
            $this->container['content'] = [
                "error"    => true,
                "errormsg" => 'Es ist ein interner Fehler aufgetreten.'
            ];
        }
        header('Content-type: application/json');
        echo json_encode($this->container['content']);
    }

    public function handleHTML() {
        $view = new \SFW\View();
        $view->appendCSSFiles($this->container['cssfiles']);
        $view->appendJSFiles($this->container['jsfiles']);

        $view->assign('content',       $this->container['content']);
        $view->assign('title',         $this->container['title']);
        $view->assign('menu',          $this->container['menu']);
        $view->assign('authenticated', $this->container['authenticated']);
        $view->showContent($this->config->getTemplateFile('olframe'));
    }

    public function handleFile() {
        $file = $this->data['path'] . $this->data['fileName'];

        if(!file_exists($file)) {
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $file);
        finfo_close($finfo);
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: ' . $mimetype);
        header('Content-Disposition: attachment; filename="' . $this->data['Name'] . '";');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($file));
        readfile($file);

        if($this->data['unlink']) {
            unlink($file);
        }
    }


}



