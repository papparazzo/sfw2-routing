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
use SFW2\Core\SFW2Exception;

class FatalError extends Handler {

    public function handle() {
        $exception = $this->container['exception'];

        if($exception == null || !($exception instanceof Throwable)) {
            $exception = new SFW2Exception(
                'unknown exception',
                SFW2Exception::UNKNOWN
            );
        } else if(!($exception instanceof SFW2Exception)) {
            $exception = new SFW2Exception(
                $exception->getMessage(),
                SFW2Exception::UNKNOWN,
                $exception
            );
        }
        $this->saveError($exception);
        header("HTTP/1.0 500 Internal Server Error");
        $view = new \SFW\View();
        $view->assign('email',   $this->container['eMailWebMaster']);
        $view->assign('title',   $this->container['title']);
        $view->assign('ex',      $exception);
        $view->assign('isDebug', $this->container['debug']);
        $view->showContent($this->config->getTemplateFile('exframe'));
    }

    protected function saveError(SFW2Exception $exception) {
        if($exception == null) {
            return;
        }
        $fd = fopen(
            $this->container['path'] . DIRECTORY_SEPARATOR .
            $exception->getIdentifier() . '.log',
            'a'
        );
        fwrite($fd, $exception->getTimeStamp());
        fwrite($fd, $exception->__toString());
        fclose($fd);
    }
}
