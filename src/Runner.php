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
 */

declare(strict_types=1);

namespace SFW2\Routing;

use DI\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use SFW2\Exception\HttpExceptions\Status4xx\HttpStatus404NotFound;
use SFW2\Exception\HttpExceptions\Status4xx\HttpStatus405MethodNotAllowed;
use SFW2\Exception\HttpExceptions\Status5xx\HttpStatus500InternalServerError;
use SFW2\Routing\ControllerMap\ControllerMapInterface;
use ReflectionException;

class Runner implements RequestHandlerInterface
{
    public function __construct(
        protected ControllerMapInterface $controllerMap,
        protected FactoryInterface $container,
        protected ResponseFactoryInterface $responseFactory
    ) {
    }

    /**
     * @inheritDoc
     * @throws     HttpStatus404NotFound | HttpStatus500InternalServerError | HttpStatus405MethodNotAllowed
     * @throws     ContainerExceptionInterface
     * @throws     ReflectionException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $ctrl = $this->controllerMap->getControllerRulesetByPath($request->getMethod(), $request->getUri()->getPath());

        $class = $ctrl->getClassName();
        $action = $ctrl->getAction();
        $response = $this->responseFactory->createResponse();

        $this->checkControllerAndAction($class, $action);

        $obj = $this->container->make($class, $ctrl->getAdditionalData());

        /** @var ResponseInterface */
        return call_user_func([$obj, $action], $request, $response, $ctrl->getActionData());
    }

    /**
     * @param class-string $class
     * @param string $action
     * @return void
     * @throws HttpStatus404NotFound
     * @throws HttpStatus500InternalServerError
     */
    protected function checkControllerAndAction(string $class, string $action): void
    {
        try {
            $refl = new ReflectionClass($class);
            /** @phpstan-ignore catch.neverThrown */
        } catch (ReflectionException) {
            throw new HttpStatus404NotFound("class <$class> does not exists");
        }

        if (!$refl->hasMethod($action)) {
            throw new HttpStatus404NotFound("action <$action> does not exists");
        }

        $method = $refl->getMethod($action);

        if (!$method->isPublic()) {
            throw new HttpStatus404NotFound("method <$action> is not public");
        }

        if ($method->getNumberOfRequiredParameters() != 3) {
            throw new HttpStatus500InternalServerError("method <$action> does not have a required parameter");
        }

        foreach ($method->getParameters() as $param) {
            $name = $param->getType()?->getName() ?? '';
            switch($param->getPosition()) {
                case 0:
                    if ($name != ServerRequestInterface::class) {
                        throw new HttpStatus500InternalServerError("method <$action> has invalid signature");
                    }
                    break;
                case 1:
                    if ($name != ResponseInterface::class) {
                        throw new HttpStatus500InternalServerError("method <$action> has invalid signature");
                    }
                    break;
                case 2:
                    if ($name != 'array') {
                        throw new HttpStatus500InternalServerError("method <$action> has invalid signature");
                    }
                    break;

                default:
                    throw new HttpStatus500InternalServerError("method <$action> has invalid signature");
            }
        }
    }
}
