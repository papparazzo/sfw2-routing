<?php

/**
 *  SFW2 - SimpleFrameWork
 *
 *  Copyright (C) 2018  Stefan Paproth
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

namespace SFW2\Routing\Resolver;

use SFW2\Routing\ControllerMap\ControllerMapException;
use SFW2\Routing\ControllerMap\ControllerMapInterface;
use SFW2\Routing\PathMap\PathMap;
use SFW2\Routing\Request;
use SFW2\Routing\Result\AbstractResult;
use SFW2\Routing\AbstractController;

use SFW2\Core\Permission\PermissionInterface;

use Dice\Dice;

use ReflectionMethod;
use ReflectionException;

class Resolver {

    /**
     * @var \SFW2\Routing\ControllerMap\ControllerMapInterface
     */
    protected $controllerMap = null;

    /**
     * @var \SFW2\Routing\Permission\PermissionInterface
     */
    protected $permission = null;

    /**
     * @var \SFW2\Routing\PathMap\PathMap
     */
    protected $pathMap;

    /**
     * @var \Dice\Dice
     */
    protected $container = null;

    public function __construct(ControllerMapInterface $controllerMap, PathMap $pathMap, Dice $container, PermissionInterface $permission) {
        $this->controllerMap = $controllerMap;
        $this->pathMap = $pathMap;
        $this->container = $container;
        $this->permission = $permission;
    }

    public function getResult(Request $request) : AbstractResult {
        $data = $this->getData($request);
        if(!($data instanceof AbstractResult)) {
            throw new ResolverException('invalid data', ResolverException::UNKNOWN_ERROR);
        }
        return $data;
    }

    protected function getData(Request $request) {
        $path = $request->getPath();
        $action = $request->getAction();

        $msg = $path . '-' . $action;
        if(!$this->pathMap->isValidPath($path)) {
            throw new ResolverException(
                'could not load "' . $msg . '"',
                ResolverException::PAGE_NOT_FOUND
            );
        }

        try {
            $pathId = $this->pathMap->getPathId($path);
            if(!$this->permission->getActionPermission($pathId, $action)) {
                throw new ResolverException(
                    'permission not allowed',
                    ResolverException::NO_PERMISSION
                );
            }

            $rule = $this->controllerMap->getRulsetByPathId($pathId);
            $this->container->addRules($rule);
            $hasFullPermission = $this->permission->hasFullActionPermission($pathId, $action);
            $ctrl = $this->getController(key($rule), $action);
            $res = call_user_func([$ctrl, $action], $hasFullPermission);
            return $res;
        } catch(ControllerMapException $ex) {
            throw new ResolverException(
                $ex->getMessage(),
                ResolverException::PAGE_NOT_FOUND,
                $ex
            );
        } catch(ReflectionException $ex) { // TODO: Use chained Exception as of 7.1
            throw new ResolverException(
                $ex->getMessage(),
                ResolverException::PAGE_NOT_FOUND,
                $ex
            );
        }
    }

    protected function getController(string $class, string $action) {
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
        if (!($ctrl instanceof AbstractController)) {
            throw new ResolverException(
                'class "' . $class . '" is no controller',
                ResolverException::PAGE_NOT_FOUND
            );
        }
        return $ctrl;
    }
}
