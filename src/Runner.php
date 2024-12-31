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

use DI\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFW2\Exception\HttpExceptions\HttpNotFound;
use SFW2\Routing\ControllerMap\ControllerMapInterface;
use SFW2\Routing\HelperTraits\getRoutingDataTrait;
use ReflectionException;
use ReflectionMethod;

class Runner implements RequestHandlerInterface
{
    use getRoutingDataTrait;

    public function __construct(
        protected ControllerMapInterface $controllerMap,
        protected FactoryInterface $container,
        protected ResponseEngine $responseEngine
    )
    {
    }

    /**
     * @inheritDoc
     * @throws HttpNotFound
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pathId = $this->getPathId($request);

        $controller = $this->controllerMap->getControllerRulesetByPathId($pathId);
        $action = $this->getAction($request);

        $ctrl = $this->getController($controller->getClassName(), $action, $controller->getAdditionalData());

        return call_user_func([$ctrl, $action], $request, $this->responseEngine);
    }

    /**
     * @throws HttpNotFound
     * @throws ContainerExceptionInterface
     */
    protected function getController(string $class, string $action, array $additionalParams): AbstractController
    {
        if (!class_exists($class)) {
            throw new HttpNotFound("class <$class> does not exists");
        }

        try {
            $refl = new ReflectionMethod($class, $action);
        } catch(ReflectionException) {
            throw new HttpNotFound("method <$action> does not exist");
        }

        if (!$refl->isPublic()) {
            throw new HttpNotFound("method <$action> is not public");
        }

        $ctrl = $this->container->make($class, $additionalParams);

        if (!($ctrl instanceof AbstractController)) {
            throw new HttpNotFound("class <$class> is no controller");
        }

        return $ctrl;
    }
}