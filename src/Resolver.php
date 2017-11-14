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
use Dice\Dice;

use ReflectionMethod;

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
        $data = $this->getData(
            $request->getModule(),
            $request->getController(),
            $request->getAction()
        );

        if($data == '') {
            throw new ResolverException('no data', ResolverException::NO_DATA_FOUND);
        }
        return $data;
    }

    protected function getData($module, $controller, $action) {
        $msg = $module . '/' . $controller . '/' . $action;
        if(!isset($this->controllers[$module][$controller])) {
            throw new ResolverException(
                'could not load "' . $msg . '"',
                ResolverException::PAGE_NOT_FOUND
            );
        }

        $rule = $this->controllers[$module][$controller];
        $this->container->addRules($rule);
        return $this->callMethode(key($rule), $action);
    }

    protected function callMethode($class, $method) {
        try {
            if(!class_exists($class)) {
                throw new ResolverException(
                    'class "' . (string)$class . '" does not exists',
                    ResolverException::PAGE_NOT_FOUND
                );
            }

            $refl = new ReflectionMethod($class, $method);

            if(!$refl->isPublic()) {
                throw new ResolverException(
                    'method "' . $method . '" is not public',
                    ResolverException::PAGE_NOT_FOUND
                );
            }

            $args = $this->getArguments($refl);

            $ctrl = $this->container->create($class);
            return call_user_func_array(array($ctrl, $action), $args);
        } catch(Throwable $ex) {
            throw new ResolverException(
                $ex->getMessage(),
                ResolverException::PAGE_NOT_FOUND,
                $ex
            );
        }
    }

    protected function getArguments(ReflectionMethod $methode, Array $args = []) : Array {
        $params = [];

        foreach($methode->getParameters() as $param) {

            /* @var $param \ReflectionParameter */
            if(!isset($args[$param->getName()]) && $param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
                continue;
            }

            switch((string)$param->getType()) {
                case 'int':
                    $params[] = (int)$args[$param->getName()];
                    break;

                case 'string':
                default:
                    $params[] = (string)$args[$param->getName()];
                    break;
            }

        }
        return $params;
    }
}
