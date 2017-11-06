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

use SFW2\Routing\Resolver\ResolverException;
use Exception;
use Dice\Dice;

class Resolver {

    protected $controllers = array();

    /**
     *
     * @var \Dice\Dice
     */
    protected $container = null;

    public function __construct(Array $controllers, Dice $container) {
        $this->controllers = $controllers;
        $this->container = $container;
    }

    public function getContent(Request $request) {
        return $this->getData(
            $request->getModule(),
            $request->getController(),
            $request->getAction()
        );
    }

    protected function getData($module, $controller, $action) {
        $ctrl = $this->getCtrlInstance($module, $controller, $action);
        $data = $ctrl->{$action}();
        if($data == '') {
            throw new ResolverException('no data', ResolverException::NO_DATA_FOUND);
        }
        return $data;
    }

    protected function getCtrlInstance($module, $controller, $action) {
        $msg = $module . '/' . $controller . '/' . $action;
        if(!isset($this->controllers[$module][$controller])) {
            throw new ResolverException(
                'could not load "' . $msg . '"',
                ResolverException::PAGE_NOT_FOUND
            );
        }

        $rule = $this->controllers[$module][$controller];
        $this->container->addRules($rule);
        return $this->loadClass(key($rule), $action);
    }

    protected function loadClass($class, $action) {
        if(!$this->isCallablePublicClassMethod($class, $action)) {
            throw new ResolverException(
                'could not load class / method "' . (string)$class . '/' . $action . '"',
                ResolverException::PAGE_NOT_FOUND
            );
        }

        $data  = $class[2] ?? [];
        $class = $class[0];
        return $this->container->create($class);
    }

    protected function isCallablePublicClassMethod($class, $method) {
        if(!class_exists($class[0])) {
            return false;
        }

        try {
            $refl = new ReflectionMethod($class[0], $method);
            return $refl->isPublic();
        } catch(Exception $e) {
            return false;
        }
    }
}
