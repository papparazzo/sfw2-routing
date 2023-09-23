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
 *  along with this program. If not, see <https://www.gnu.org/licenses/agpl.txt>.
 *
 */

namespace SFW2\Routing\Middleware;

use ErrorException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SFW2\Core\HttpExceptions\HttpException;
use SFW2\Core\HttpExceptions\HttpInternalServerError;
use SFW2\Core\SFW2Exception;
use SFW2\Routing\ResponseEngine;
use Throwable;

class Error implements MiddlewareInterface
{
    public function __construct(
        protected ResponseEngine $responseEngine,
        protected ContainerInterface $config,
        protected LoggerInterface $logger = new NullLogger()
    )
    {
    }

    /**
     * @param Request $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(Request $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch(Throwable $exception) {
            $exception = $this->convertException($request, $exception);
            $response = $this->createResponseFromException($request, $exception);
            // FIXME Response-phrase missing!
            return $response->withStatus($exception->getCode());
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function convertException(Request $request, Throwable $exception): HttpException {
        if($exception instanceof ErrorException) {
            // TODO: Write propper Error-Message
            $this->logger->critical("Error on line: {$exception->getLine()}  {$exception->getMessage()}");
            return new HttpInternalServerError($exception->getMessage(), $exception);
        }

        if (!$exception instanceof HttpException) {
            $exception = new HttpInternalServerError($exception->getMessage(), $exception);
        }

        if ($exception->getCode() >= 500) {
            $this->logger->critical($exception->getMessage());
            $this->sendMail($request, $exception);
        } else {
            $this->logger->warning($exception->getMessage());
        }
        return $exception;
    }

    protected function createResponseFromException(Request $request, HttpException $exc): ResponseInterface {
        $data = [
            'title' => $exc->getTitle(),
            'caption' => $exc->getCaption(),
            'description' => $exc->getDescription(),
        ];

        return $this->responseEngine->render($request, $data);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    protected function sendMail(Request $request, SFW2Exception $exception): bool
    {
        $content =
            $request->getUri() . PHP_EOL . PHP_EOL .
            $exception->getTimeStamp() . PHP_EOL . PHP_EOL .
            $exception;

        $header = [
            'From:' . $this->config->get('defEMailAddr.name') . ' <' . $this->config->get('defEMailAddr.addr') . '>',
            'MIME-Version: 1.0',
            'Content-Type:text/html; charset=utf-8',
            'Content-Transfer-Encoding: 8bit'
        ];

        return mail(
            $this->config->get('project.eMailWebMaster'),
            'Interner Fehler [ID: ' . $exception->getIdentifier() . ']',
            nl2br($content),
            implode("\r\n", $header)
        );
    }
}

