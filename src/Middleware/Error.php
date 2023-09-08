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

namespace SFW2\Routing\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SFW2\Core\SFW2Exception;
use SFW2\Routing\Router\Exception;
use SFW2\Routing\Router\Exception as RouterException;
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

        if (!$exception instanceof SFW2Exception) {
            $exception = new SFW2Exception($exception->getMessage(), RouterException::INTERNAL_SERVER_ERROR, $exception);
            $this->logger->critical($exception->getMessage());
        }

    #    switch($exception->getCode()) {
    #        case
    #    }


/*
        $this->assignArray([
            'title' => $exception->getCode(),
            'caption' => $exception->getError(),
            'description' => $exception->getMessage()
        ]);*/
        return $this->factory->createResponse();
    }

    public function getInvalidDataGiven() {
        $title = '400';
        $caption = 'Ungültige Daten';
        $description = 'Die Anfrage-Nachricht enthielt ungültige Daten. Bitte prüfe die URL auf Fehler und drücke dann den reload-Button in deinem Browser.';

        return $this->handle($title, $caption, $description);
    }

    public function getNoPermission() {
        $title = '403';
        $caption = 'Keine Berechtigung';
        $description = 'Dir fehlt die Berechtigung für diese Seite. Bitte melde dich an und probiere es erneut.';

        return $this->handle($title, $caption, $description);
    }

    public function getNoDataFound() {
        $title = '404';
        $caption = 'Daten nicht vorhanden';
        $description =
            'Die angeforderten Daten konnten nicht gefunden werden. Bitte prüfe die URL auf Fehler und drücke dann den reload-Button in deinem Browser.';

        return $this->handle($title, $caption, $description);
    }

    public function getPageNotFound() {
        $title = '404';
        $caption = 'Seite nicht vorhanden';
        $description =
            'Die gewünschten Daten konnten nicht gefunden werden. Bitte prüfe die URL auf Fehler und drücke dann den reload-Button in deinem Browser.';

        return $this->handle($title, $caption, $description);
    }

    public function getOffline() {
        $title = '503';
        $caption = 'Die Seiten sind aktuell offline';
        $description =
            'Aufgrund von umfangreichen Wartungsarbeiten sind die Webseiten im Moment leider nicht zu erreichen. Bitte versuche es sp�ter noch einmal.';

        return $this->handle($title, $caption, $description);
    }

    public function getError() {
        $title = '500';
        $caption = 'Interner Fehler aufgetreten!';
        $description = 'Es ist ein interner Fehler aufgetreten.';
        # <br />[ID: {$exception->getIdentifier()}]";

        return $this->handle($title, $caption, $description);
    }

    protected function handle($title, $caption, $description): AbstractResult
    {
        $result = new Content('plain', true);
        $result->assignArray(['title' => $title, 'caption' => $caption, 'description' => $description]);

        return $result;
    }
}

