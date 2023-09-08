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

declare(strict_types=1);

namespace SFW2\Routing;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFW2\Routing\ControllerMap\ControllerMapInterface;
use SFW2\Routing\PathMap\PathMap;
use ReflectionException;
use ReflectionMethod;
use SFW2\Routing\Router\Exception as RouterException;
use Throwable;

class Runner implements RequestHandlerInterface
{
    protected ControllerMapInterface $controllerMap;

    protected PathMap $pathMap;

    protected ContainerInterface $container;

     public function __construct(PathMap $pathMap, ControllerMapInterface $controllerMap, ContainerInterface $container)
    {
        $this->controllerMap = $controllerMap;
        $this->pathMap = $pathMap;
        $this->container = $container;
    }

    /**
     * @inheritDoc
     * @throws RouterException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        #$action preview, show (index), getContent (read), delete, update, create

        if (!$this->pathMap->isValidPath($path)) {
            throw new RouterException("could not load <$path>", RouterException::NOT_FOUND);
        }

        $pathId = $this->pathMap->getPathId($path);

        $controller = $this->controllerMap->getControllerRulsetByPathId($pathId);

        $action = $request->getAction();
        $ctrl = $this->getController($controller[ControllerMapInterface::CLASS_NAME], $action);

        $ctrl->setPathId($pathId);
        $ctrl->appendAdditionalData($controller[ControllerMapInterface::ADDITIONAL_DATA]);

        return call_user_func([$ctrl, $action]);
    }

    /**
     * @throws ReflectionException
     * @throws RouterException
     */
    protected function getController(string $class, string $action): AbstractController
    {
        if (!class_exists($class)) {
            throw new RouterException("class <$class> does not exists", RouterException::NOT_FOUND);
        }

        $refl = new ReflectionMethod($class, $action);

        if (!$refl->isPublic()) {
            throw new RouterException("method <$action> is not public", RouterException::NOT_FOUND);
        }

        try {
            $ctrl = $this->container->get($class);
        } catch (Throwable $exc) {
            throw new RouterException("container exception <{$exc->getMessage()}> catched", RouterException::INTERNAL_SERVER_ERROR, $exc);
        }

        if (!($ctrl instanceof AbstractController)) {
            throw new RouterException("class <$class> is no controller", RouterException::NOT_FOUND);
        }

        return $ctrl;
    }
}