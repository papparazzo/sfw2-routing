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

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFW2\Core\HttpExceptions\HttpNotFound;
use SFW2\Routing\ControllerMap\ControllerMapInterface;
use SFW2\Routing\PathMap\PathMap;
use ReflectionException;
use ReflectionMethod;

class Runner implements RequestHandlerInterface
{
    protected ControllerMapInterface $controllerMap;

    protected PathMap $pathMap;

    protected ContainerInterface $container;

    protected ResponseFactoryInterface $responseFactory;

    public function __construct(PathMap $pathMap, ControllerMapInterface $controllerMap, ContainerInterface $container, ResponseFactoryInterface $responseFactory)
    {
        $this->controllerMap = $controllerMap;
        $this->pathMap = $pathMap;
        $this->container = $container;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @inheritDoc
     * @throws HttpNotFound
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        #$action preview, show (index), getContent (read), delete, update, create

        if (!$this->pathMap->isValidPath($path)) {
            throw new HttpNotFound("could not load <$path>");
        }

        $pathId = $this->pathMap->getPathId($path);

        $controller = $this->controllerMap->getControllerRulsetByPathId($pathId);

        $action = 'index'; #$request->getAction();
        $ctrl = $this->getController($controller[ControllerMapInterface::CLASS_NAME], $action);

        $ctrl->setPathId($pathId);
        #$ctrl->appendAdditionalData($controller[ControllerMapInterface::ADDITIONAL_DATA]);

        return call_user_func([$ctrl, $action], $request, $this->responseFactory->createResponse());
    }

    /**
     * @throws ReflectionException
     * @throws HttpNotFound
     * @throws ContainerExceptionInterface
     */
    protected function getController(string $class, string $action): AbstractController
    {
        if (!class_exists($class)) {
            throw new HttpNotFound("class <$class> does not exists");
        }

        $refl = new ReflectionMethod($class, $action);

        if (!$refl->isPublic()) {
            throw new HttpNotFound("method <$action> is not public");
        }

        $ctrl = $this->container->get($class);

        if (!($ctrl instanceof AbstractController)) {
            throw new HttpNotFound("class <$class> is no controller");
        }

        return $ctrl;
    }
}