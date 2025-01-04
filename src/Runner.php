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
use ReflectionClass;
use SFW2\Exception\HttpExceptions\HttpInternalServerError;
use SFW2\Exception\HttpExceptions\HttpNotFound;
use SFW2\Routing\ControllerMap\ControllerMapInterface;
use ReflectionException;

class Runner implements RequestHandlerInterface
{
    public function __construct(
        protected ControllerMapInterface $controllerMap,
        protected FactoryInterface $container,
        protected ResponseEngine $responseEngine
    ) {
    }

    /**
     * @inheritDoc
     * @throws HttpNotFound
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     * @throws HttpInternalServerError
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pathId = $request->getAttribute('sfw2_routing')[RequestData::PATH_ID];

        if (is_null($pathId)) {
            throw new HttpNotFound("could not load <{$request->getUri()->getPath()}>");
        }

        $controller = $this->controllerMap->getControllerRulesetByPathId((int)$pathId, $request->getMethod());

        $class = $controller->getClassName();
        $action = $controller->getAction();

        $this->checkControllerAndAction($class, $action);

        $ctrl = $this->container->make($class, $controller->getAdditionalData());

        return call_user_func([$ctrl, $action], $request, $this->responseEngine, []);
    }

    /**
     * @throws HttpNotFound
     * @throws HttpInternalServerError
     */
    protected function checkControllerAndAction(string $class, string $action): void
    {
        try {
            $refl = new ReflectionClass($class);
        } catch(ReflectionException) {
            throw new HttpNotFound("class <$class> does not exists");
        }

        if (!$refl->hasMethod($action)) {
            throw new HttpNotFound("action <$action> does not exists");
        }

        $method = $refl->getMethod($action);

        if (!$method->isPublic()) {
            throw new HttpNotFound("method <$action> is not public");
        }

        if ($method->getNumberOfRequiredParameters() != 3) {
            throw new HttpInternalServerError("method <$action> does not have a required parameter");
        }

        foreach ($method->getParameters() as $param) {
            switch($param->getPosition()) {
                case 0:
                    if ($param->getType()->getName() != ServerRequestInterface::class) {
                        throw new HttpInternalServerError("method <$action> has invalid signature");
                    }
                    break;
                case 1:
                    if ($param->getType()->getName() != ResponseEngine::class) {
                        throw new HttpInternalServerError("method <$action> has invalid signature");
                    }
                    break;
                case 2:
                    if ($param->getType()->getName() != 'array') {
                        throw new HttpInternalServerError("method <$action> has invalid signature");
                    }
                    break;

                default:
                    throw new HttpInternalServerError("method <$action> has invalid signature");
            }
        }
    }
}
