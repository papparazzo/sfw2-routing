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

use SFW2\Routing\Resolver\Exception as ResolverException;
use SFW2\Routing\ControllerMap\Exception as ControllerMapException;
use Dice\Dice;

use ReflectionMethod;
use ReflectionException;

class Resolver {

    /**
     * @var ControllerMap
     */
    protected $controllers = null;

    /**
     * @var Permission
     */
    protected $permission = null;

    /**
     * @var Path
     */
    protected $path;

    /**
     * @var \Dice\Dice
     */
    protected $container = null;

    public function __construct(ControllerMap $controllers, Permission $permission, Path $path, Dice $container) {
        $this->controllers = $controllers;
        $this->permission = $permission;
        $this->path = $path;
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
        if(!$this->path->isValidPath($path)) {
            throw new ResolverException(
                'could not load "' . $msg . '"',
                ResolverException::PAGE_NOT_FOUND
            );
        }

        try {
            $pathId = $this->path->getPathId($path);
            if(!$this->permission->getActionPermission($pathId, $action)) {
                throw new ResolverException(
                    'permission not allowed',
                    ResolverException::NO_PERMISSION
                );
            }

            $rule = $this->controllers->getRulsetByPathId($pathId);
            $this->container->addRules($rule);
            $hasFullPermission = $this->permission->hasFullActionPermission($pathId, $action);
            return $this->callMethode(key($rule), $action, $hasFullPermission);
        } catch(ControllerMapException $ex) {
            throw new ResolverException(
                $ex->getMessage(),
                ResolverException::PAGE_NOT_FOUND,
                $ex
            );
        } catch(ReflectionException $ex) { // Use chained Exception as of 7.1
            throw new ResolverException(
                $ex->getMessage(),
                ResolverException::PAGE_NOT_FOUND,
                $ex
            );
        }
    }

    protected function callMethode(string $class, string $action, bool $hasFullPermission) {
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
        return call_user_func([$ctrl, $action], $hasFullPermission);
    }
}
