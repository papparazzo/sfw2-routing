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
     * @var \Dice\Dice
     */
    protected $container = null;

    public function __construct(Array $controllers, Dice $container) {
        $this->controllers = $controllers;
        $this->container = $container;
    }

    public function getResult(Request $request) : Result {
        $data = $this->getData($request);
        if(!($data instanceof Result)) {
            throw new ResolverException('invalid data', ResolverException::UNKNOWN_ERROR);
        }
        return $data;
    }

    protected function getData(Request $request) {
        $path = $request->getPath();
        $action = $request->getAction();

        $msg = $path . '-' . $action;
        if(!isset($this->controllers[$path])) {
            throw new ResolverException(
                'could not load "' . $msg . '"',
                ResolverException::PAGE_NOT_FOUND
            );
        }

        $params = $this->controllers[$path];
        $class = key($params);
        $rule = [
            $class => [
                'constructParams' => current($params)
            ]
        ];
        $this->container->addRules($rule);
        return $this->callMethode($class, $action, $request);
    }

    protected function callMethode(string $class, string $action, Request $request) {
        try {
            if(!class_exists($class)) {
                throw new ResolverException(
                    'class "' . $class . '" does not exists',
                    ResolverException::PAGE_NOT_FOUND
                );
            }

            $refl = new ReflectionMethod($class, $action);

            if(!$refl->isPublic()) {
                throw new ResolverException(
                    'method "' . $action . '" is not public',
                    ResolverException::PAGE_NOT_FOUND
                );
            }

            $ctrl = $this->container->create($class);
            $args = $this->getArguments($refl, $request);
            return call_user_func_array(array($ctrl, $action), $args);
        } catch(Throwable $ex) {
            throw new ResolverException(
                $ex->getMessage(),
                ResolverException::PAGE_NOT_FOUND,
                $ex
            );
        }
    }

    protected function getArguments(ReflectionMethod $methode, Request $request) : Array {
        $params = [];

        foreach($methode->getParameters() as $param) {

            /* @var $param \ReflectionParameter */
            $gParam = $request->getGetParam($param->getName());

            if(is_null($gParam) && $param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
                continue;
            }

            switch((string)$param->getType()) {
                case 'int':
                    $params[] = (int)$gParam;
                    break;

                case 'string':
                default:
                    $params[] = (string)$gParam;
                    break;
            }

        }
        return $params;
    }
}
