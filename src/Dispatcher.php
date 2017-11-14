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
use SFW2\Core\View;

class Dispatcher {

    /**
     * @var Content
     */
    public $content = '';

    public function __construct(Content $content) {
        $this->content = $content;
    }

    protected function dispatchRequestType() {

        if(is_file($this->content)) {
            return new Dispatcher\Handler\FileDownload();
        }
        if(isset($this->server['HTTP_X_REQUESTED_WITH']) && !is_array($this->content)) {
            return new Dispatcher\Handler\XML($this->registry, $data);
        }
        if(isset($this->server['HTTP_X_REQUESTED_WITH']) && is_array($this->content)) {
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
            $this->container['content'] = ['error' => false];
        }
        header('Content-type: application/json');
        echo json_encode($this->container['content']);
    }

    public function handleHTML() {
        $view = new View();
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
