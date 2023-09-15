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
 *  along with this program. If not, see <httsp://www.gnu.org/licenses/agpl.txt>.
 *
 */

namespace SFW2\Routing\Middleware;

use ErrorException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SFW2\Core\HttpExceptions\HttpException;
use SFW2\Core\HttpExceptions\HttpInternalServerError;
use Throwable;

class Error implements MiddlewareInterface
{
    protected ResponseFactoryInterface $factory;

    protected LoggerInterface $logger;

    public function __construct(ResponseFactoryInterface $factory, LoggerInterface $logger)
    {
        $this->factory = $factory;
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch(Throwable $exc) {
            return $this->handleException($exc);
        }
    }

    protected function handleException(Throwable $exception): ResponseInterface {
        $exception = $this->convertException($exception);
        return $this->createResponseFromException($exception);
    }

    protected function convertException(Throwable $exc): HttpException {
        if($exc instanceof ErrorException) {
            // TODO: Write propper Error-Message
            $this->logger->critical("Error on line: {$exc->getLine()}  {$exc->getMessage()}");
            return new HttpInternalServerError($exc->getMessage(), $exc);
        }

        if (!$exc instanceof HttpException) {
            $exc = new HttpInternalServerError($exc->getMessage(), $exc);
        }

        if ($exc->getCode() >= 500) {
            $this->logger->critical($exc->getMessage());
        } else {
            $this->logger->warning($exc->getMessage());
        }
        return $exc;
    }

    protected function createResponseFromException(HttpException $exc): ResponseInterface {
        /*
         *

        $this->assignArray([
            'title' => $exc->getTitle(),
            'caption' => $exc->getCaption(),
            'description' => $exc->getDescription(),
            'identifier' => $exc->getIdentifier()
            #'created'
            #'updated'
        ]);
         */
        return $this->factory->createResponse();
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    protected function sendMail(ServerRequestInterface $request, SFW2Exception $exception): bool
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

