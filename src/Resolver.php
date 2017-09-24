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

namespace SFW2;

use SFW2\Resolver\ResolverException;
use Exception;

class Resolver {

    protected $controllers = array();

    public function __construct(Array $controllers) {
        $this->controllers = $controllers;
    }

    public function getContent(Request $request) {
        try {
            return $this->getData(
                $request->getModule(),
                $request->getController(),
                $request->getAction()
            );
        } catch(ResolverException $ex) {
            switch($ex->getCode()) {
                case ResolverException::PAGE_NOT_FOUND:
                    $action = 'pageNotFound';
                    break;

                case ResolverException::FILE_NOT_FOUND:
                    $action = 'fileNotFound';
                    break;

                case ResolverException::NO_DATA_FOUND:
                    $action = 'noDataFound';
                    break;

                case ResolverException::NO_PERMISSION:
                    $action = 'noPermission';
                    break;

                default:
                    $action = 'index';
                    break;

            }
            return $this->getData('sfw2', 'error', $action);
        }
    }

    protected function getCtrlInstance($module, $controller, $action) {
        $msg = $module . '/' . $controller . '/' . $action;
        if(!isset($this->controllers[$module][$controller])) {
            throw new ResolverException(
                'could not load "' . $msg . '"',
                ResolverException::PAGE_NOT_FOUND
            );
        }

        $class = $this->controllers[$module][$controller];
        return $this->loadClass($class, $action);
    }

    protected function loadClass($class, $action) {

        if(!$this->isCallablePublicClassMethod($class, $action)) {
            throw new ResolverException(
                'could not load class / method "' . $class . '/' . $action . '"',
                ResolverException::PAGE_NOT_FOUND
            );
        }
        // FIXME DI-Container
        $data  = $class[2] ?? [];
        $class = $class[0];
        return new $class($this->config, $data);
    }

    protected function isCallablePublicClassMethod($class, $method) {
        if(!class_exists($class[0]) && !$this->tryLoad($class)) {
            return false;
        }

        try {
            $refl = new ReflectionMethod($class[0], $method);
            return $refl->isPublic();
        } catch(Exception $e) {
            return false;
        }
    }

    protected function tryLoad($class) {
        $classPath = $class[1];
        if(!is_readable($classPath)) {
            return false;
        }

        if(!class_exists($class[0], false)) {
            require_once($classPath);
        }
        return true;
    }

    protected function getData($module, $controller, $action) {
        $ctrl = $this->getCtrlInstance($module, $controller, $action);
        $data = $ctrl->{$action}();
        if($data == '') {
            throw new ResolverException('no data', ResolverException::NO_DATA_FOUND);
        }
        return $data;
    }
}
