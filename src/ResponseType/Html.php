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
use SFW2\Routing\Menu;
use SFW2\Routing\User;
use SFW2\Routing\Permission\PagePermission;

use SFW2\Core\Session;
use SFW2\Core\View;
use SFW2\Core\Config;

class Html extends ResponseType {

    const HTTP_STATUS_INTERNAL_SERVER_ERROR = 0;
    const HTTP_STATUS_NOT_FOUND             = 1;
    const HTTP_STATUS_FORBIDDEN             = 2;

    /**
     * @var Menu
     */
    protected $menu;

    /**
     * @var Session
     */
    protected $session;

    public function __construct(Result $result, PagePermission $pagePermission, Config $config, Menu $menu, Session $session) {
       parent::__construct($result, $pagePermission, $config);
       $this->menu = $menu;
       $this->session = $session;
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
        $view->append('jsFileStartUp', 'https://code.jquery.com/jquery-3.2.1.min.js');
        $view->append('jsFileStartUp', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js');
        $view->append('jsFileStartUp', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js');

        $view->assign('xssToken', $this->session->generateToken());

        $view->appendArray(
            'jsFiles', array_merge([
                '/' . $this->config->getVal('path', 'jsPath') . 'helper.js',
                '/' . $this->config->getVal('path', 'jsPath') . 'starter.js'
            ], $this->result->getJSFiles($this->config->getVal('path', 'jsPath')))
        );

        $this->result->assign('permission', $this->pagePermission);

        $title =
            $this->config->getVal('project', 'title') . ' - ' .
            $this->result->getValue('title', '');

        $view->assign('title', trim($title, ' - '));
        $view->assign('content', $this->getInnerContent());
        echo $view->getContent();
    }

    protected function getInnerContent( ) {
        $view = new View('web/templates/' . $this->result->getTemplateFile() . '.phtml');
        $view->assignArray($this->result->getData());
        $view0 = new View('web/templates/decorate.phtml');
        $view0->assign('content', $view->getContent());
        $view0->assign('mainMenu', $this->menu->getMainMenu());
        $view0->assign('sideMenu', $this->menu->getSideMenu());
        $view0->assign('authenticated', (bool)$this->session->getGlobalEntry(User::class));
        return $view0->getContent();
    }
}
