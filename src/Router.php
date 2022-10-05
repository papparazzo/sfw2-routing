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

use DI\Container;
use ReflectionMethod;
use SFW2\Routing\ControllerMap\ControllerMapInterface;
use SFW2\Routing\PathMap\PathMap;
use SFW2\Routing\Router\Exception as RouterException;
use Throwable;

class Router {

    protected ControllerMapInterface $controllerMap;

    protected PathMap $pathMap;

    protected array $middlewareHandlers;

    protected Container $container;

    public function __construct(PathMap $pathMap, ControllerMapInterface $controllerMap) {
        $this->controllerMap = $controllerMap;
        $this->pathMap = $pathMap;

        $this->container = new Container();
    }

    public function addMiddlewareHandler(MiddlewareInterface $handler): void {
        $this->middlewareHandlers[] = $handler;
    }

    /**
     * @throws \ReflectionException
     * @throws \SFW2\Routing\Router\Exception
     */
    public function handleRequest(Request $request): Content {
        $path = $request->getPath();

        #$action preview, show (index), getContent (read), delete, update, create

        $action = $request->getAction();

        foreach($this->middlewareHandlers as $handler) {
            $handler->handle($request);
        }

        if(!$this->pathMap->isValidPath($path)) {
            throw new RouterException("could not load <$path>", RouterException::NOT_FOUND);
        }

        $controller = $this->controllerMap->getControllerRulsetByPathId($this->pathMap->getPathId($path));

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

        try {
            $ctrl = $this->container->get($class);
        } catch (Throwable $exc) {
            throw new RouterException("container exception <{$exc->getMessage()}> cathced", RouterException::INTERNAL_SERVER_ERROR, $exc);
        }

        if(!($ctrl instanceof AbstractController)) {
            throw new RouterException("class <$class> is no controller", RouterException::NOT_FOUND);
        }
        return $ctrl;
    }
}