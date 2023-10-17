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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFW2\Core\HttpExceptions\HttpNotFound;
use SFW2\Routing\ControllerMap\ControllerMapInterface;
use SFW2\Routing\PathMap\PathMapInterface;
use ReflectionException;
use ReflectionMethod;

class Runner implements RequestHandlerInterface
{
    public function __construct(
        protected PathMapInterface $pathMap,
        protected ControllerMapInterface $controllerMap,
        protected ContainerInterface $container,
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
        $path = $request->getUri()->getPath();

        if (!$this->pathMap->isValidPath($path)) {
            throw new HttpNotFound("could not load <$path>");
        }

        $pathId = $this->pathMap->getPathId($path);

        $controller = $this->controllerMap->getControllerRulsetByPathId($pathId);
        $action = $this->getAction($request);

        $requestData = [
            RequestData::ACTION => $action,
            RequestData::IS_HOME => $request->getUri()->getPath() == '/',
            RequestData::PATH_ID => $pathId
        ];

        $ctrl = $this->getController($controller->getClassName(), $action);
        $ctrl->appendAdditionalData($controller->getAdditionalData());

        return call_user_func(
            [$ctrl, $action],
            $request->withAttribute('request', $requestData),
            $this->responseEngine
        );
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

    protected function getAction(ServerRequestInterface $request): string
    {
        $request->getUri();
        $params = $request->getQueryParams();
        if(!isset($params['do'])) {
            return 'index';
        }
        return $params['do'];
    }
}