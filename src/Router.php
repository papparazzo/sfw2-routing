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

namespace SFW2\Routing;

use ReflectionMethod;
use SFW2\Routing\ControllerMap\ControllerMapInterface;
use SFW2\Routing\Router\Exception as RouterException;

class Router {

    protected ControllerMapInterface $controllerMap;

    public function __construct(ControllerMapInterface $controllerMap) {
        $this->controllerMap = $controllerMap;
    }

    /**
     * @throws \ReflectionException
     * @throws \SFW2\Routing\Router\Exception
     */
    public function handleRequest(Request $request): Result {
        $path = $request->getPath();

        #$action preview, show (index), getContent (read), delete, update, create

        $action = $request->getAction();

        /* TODO XSRF-Check! FIXME Middelware
        if($request->hasPostParams() && !$this->session->compareToken((string)filter_input(INPUT_POST, 'xss'))) {
           throw new ResolverException("class <$class> does not exists", ResolverException::INVALID_DATA_GIVEN);
        }
        */

        if(!$this->controllerMap->isValidPath($path)) {
            throw new RouterException("could not load <$path>", RouterException::NOT_FOUND);
        }
        $controller = $this->controllerMap->getControllerRulsetByPathId($path);

        // TODO Permission Check! FIXME Middelware
        #$hasFullPermission = $this->hasFullPermission($controller['PathId'], $action);

        $ctrl = $this->getController(key($controller['Controller']), $action);
        return call_user_func([$ctrl, $action]);
    }

    /**
     * @throws \ReflectionException
     * @throws \SFW2\Routing\Router\Exception
     */
    protected function getController(string $class, string $action): AbstractController {
        if(!class_exists($class)) {
            throw new RouterException("class <$class> does not exists", RouterException::NOT_FOUND);
        }

        $refl = new ReflectionMethod($class, $action);

        if(!$refl->isPublic()) {
            throw new RouterException("method <$action> is not public", RouterException::NOT_FOUND);
        }

        # $this->container->addRules([
        #     $controller['Controller'] => [
        #         'constructParams' => $controller['AdditionalParams']
        #     ]
        # ]);

        #$ctrl = $this->container->create($class);
        if(!($ctrl instanceof AbstractController)) {
            throw new RouterException("class <$class> is no controller", RouterException::NOT_FOUND);
        }
        return $ctrl;
    }
}