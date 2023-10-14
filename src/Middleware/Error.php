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

use DateTime;
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
use SFW2\Core\Replacer\ReplaceInterface;
use SFW2\Core\Replacer\SimpleReplacer;
use SFW2\Core\SFW2Exception;
use SFW2\Routing\ResponseEngine;
use Throwable;

class Error implements MiddlewareInterface
{
    public function __construct(
        protected ResponseEngine $responseEngine,
        protected ContainerInterface $config,
        protected LoggerInterface $logger = new NullLogger(),
        protected ReplaceInterface $replacer = new SimpleReplacer()
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
        if (!$exception instanceof HttpException) {
            $exception = new HttpInternalServerError($exception->getMessage(), $exception);
        }

        // TODO: Reformat!
        $message =
            "Error <{$exception->getCode()}> on line: {$exception->getFile()}:{$exception->getLine()} " .
            "{$exception->getMessage()}" . $request->getUri() . 'Interner Fehler [ID: ' . $exception->getIdentifier() . ']'
        ;

        if ($exception->getCode() >= 500 && $exception->getCode() != 503) {
            $this->logger->critical($message, $exception->getTrace());
            $this->sendMail($request, $exception);
        } else {
            $this->logger->warning($message, $exception->getTrace());
        }
        return $exception;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createResponseFromException(Request $request, HttpException $exception): ResponseInterface {
        $data = [
            'title' => $exception->getTitle(),
            'caption' => $exception->getCaption(),
            'description' => $exception->getDescription(),
            'identifier' => $exception->getIdentifier()
        ];

        if ($this->config->get('site.debugMode')) {
            $data['debugData'] = $this->getContentString($request, $exception);
        }

        return $this->responseEngine->render($request, "notice", $data);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    protected function sendMail(Request $request, SFW2Exception $exception): bool
    {
        $header = [
            "From:{$this->config->get('defEMailAddr.name')} <{$this->config->get('defEMailAddr.addr')}>",
            "MIME-Version: 1.0",
            "Content-Type:text/html; charset=utf-8",
            "Content-Transfer-Encoding: 8bit"
        ];

        return mail(
            to: $this->config->get('project.eMailWebMaster'),
            subject: "Interner Fehler [ID: {$exception->getIdentifier()}]",
            message: nl2br($this->getContentString($request, $exception)),
            additional_headers: implode("\r\n", $header)
        );
    }

    protected function getContentString(Request $request, SFW2Exception $exception): string
    {
        $dateTimeObj = new DateTime();
        $dateTimeObj->setTimestamp($exception->getTimeStamp());

        return
            $request->getUri() . ' ' . $dateTimeObj->format('Y-m-d H:i:s') . PHP_EOL . PHP_EOL .
            $exception;

    }
}

