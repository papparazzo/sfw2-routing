<?php

/**
 *  SFW2 - SimpleFrameWork
 *
 *  Copyright (C) 2017  Stefan Paproth
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Fondation, either version 3 of the
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

namespace SFW2\Routing\ResponseType;

use SFW2\Routing\ResponseType;
use SFW2\Core\View;

class Html extends ResponseType {

    const HTTP_STATUS_INTERNAL_SERVER_ERROR = 0;
    const HTTP_STATUS_NOT_FOUND             = 1;
    const HTTP_STATUS_FORBIDDEN             = 2;


    public function dispatch() {
        $view = new View('web/templates/skeleton.phtml');


        #$view->assign('cssFiles', $this->content->getCSSFiles());
        $view->append('cssFiles', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css');
        #$view->assign('jsFiles', $this->content->getJSFiles());
        $view->appendArray(
            'jsFiles', [
                'https://code.jquery.com/jquery-3.2.1.min.js',
                'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js',
                'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js'
            ]
        );

        /*
        if(is_null($title)) {
            $title =  $this->conf->getVal('project', 'title');
        }
        $this->title = $title;
        */



        $view->assign('title', 'sdf');#$this->content->getTitle());
        $view->assign('content', $this->getInnerContent());
        echo $view->getContent();

        #$view->assign('menu',          $this->container['menu']);
        #$view->assign('authenticated', $this->container['authenticated']);

    }

    protected function getInnerContent( ) {
        $view = new View('web/templates/' . $this->result->getTemplateFile() . '.phtml');
        $view->assignArray($this->result->getData());
        return $view->getContent();
    }


    protected $title    = '';

    public function setTitle(string $title) {
        $this->title = $title;
    }

    public function getTitle() : string {
        return $this->title;
    }

    public function setHeader($header) {
        /*
        switch($headerType) {
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
         *
         */
    }

}
