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

namespace SFW2\Routing\Dispatcher;

use SFW2\Routing\Dispatcher\Handler;

class FatalError extends Handler {

    public function handle() {
        if(
            $this->container['exception'] == null ||
            !($this->container['exception'] instanceof Throwable)
        ) {
            $this->container['exception'] = new \SFW\Exception(
                'unknown exception',
                \SFW\Exception::UNKNOWN
            );
        } else if(!($this->container['exception'] instanceof \SFW\Exception)) {
            $this->container['exception'] = new \SFW\Exception(
                $this->container['exception']->getMessage(),
                \SFW\Exception::UNKNOWN,
                $this->container['exception']
            );
        }
        $this->saveError($this->container['exception']);
        header("HTTP/1.0 500 Internal Server Error");
        $view = new \SFW\View();
        $view->assign('email',   $this->container['eMailWebMaster']);
        $view->assign('title',   $this->container['title']);
        $view->assign('ex',      $this->container['exception']);
        $view->assign('isDebug', $this->container['debug']);
        $view->showContent($this->config->getTemplateFile('exframe'));
    }

    protected function saveError($ex) {
        if($ex == null) {
            return;
        }
        $fd = fopen(
            $this->container['path'] . DIRECTORY_SEPARATOR .
            $ex->getIdentifier() . '.log',
            'a'
        );
        fwrite($fd, $ex->getTimeStamp());
        fwrite($fd, $ex->__toString());
        fclose($fd);
    }
}
