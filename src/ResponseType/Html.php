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
use SFW2\Routing\Result;
use SFW2\Core\View;
use Dice\Dice;

class Html extends ResponseType {

    const HTTP_STATUS_INTERNAL_SERVER_ERROR = 0;
    const HTTP_STATUS_NOT_FOUND             = 1;
    const HTTP_STATUS_FORBIDDEN             = 2;

    /**
     * @var \Dice\Dice
     */
    protected $dice = null;

    public function __construct(Result $result, Dice $dice) {
       parent::__construct($result);
       $this->dice = $dice;
    }

    public function dispatch() {
        $view = new View('web/templates/skeleton.phtml');

        $css = 'public/css/' . $this->result->getTemplateFile() . '.css';
        if(is_file($css)) {
            $view->append('cssFiles', $css);
        }

        #$view->assign('cssFiles', $this->content->getCSSFiles());
        $view->append('cssFiles', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css');
        $view->append('cssFiles', 'https://fonts.googleapis.com/css?family=Montserrat');
        $view->append('cssFiles', '/public/css/base.css');
        #$view->assign('jsFiles', $this->content->getJSFiles());
        $view->appendArray(
            'jsFiles', [
                'https://code.jquery.com/jquery-3.2.1.min.js',
                'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js',
                'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js'
            ]
        );

        #$view->assign('authenticated', $this->container['authenticated']);

        $view->assign('title', $this->result->getValue('title', $this->dice->create('SFW2\Core\Config')->getVal('project', 'title')));
        $view->assign('content', $this->getInnerContent());
        echo $view->getContent();
    }

    protected function getInnerContent( ) {
        $view = new View('web/templates/' . $this->result->getTemplateFile() . '.phtml');
        $view->assignArray($this->result->getData());
        $view0 = new View('web/templates/decorate.phtml');
        $view0->assign('content', $view->getContent());
        $view0->assign('mainMenu', $this->dice->create('SFW2\Routing\Menu')->getMenu());
        $view0->assign('sideMenu', $this->dice->create('SFW2\Routing\Menu')->getMenu(1, 2));
        return $view0->getContent();
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
