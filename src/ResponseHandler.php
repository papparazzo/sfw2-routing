<?php

/**
 *  SFW2 - SimpleFrameWork
 *
 *  Copyright (C) 2017  Stefan Paproth
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

namespace SFW2\Routing;

use SFW2\Routing\Resolver\ResolverException;
use SFW2\Routing\Result\Content;

use SFW2\Core\Config;
use SFW2\Core\SFW2Exception;

use Throwable;

class ResponseHandler {

    /**
     * @var \SFW2\Core\Config
     */
    protected $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    public function getContent(Request $request, Resolver $resolver) : Result {
        try {
            return $resolver->getResult($request);
        } catch(ResolverException $exception) {
            switch($exception->getCode()) {
                case ResolverException::PAGE_NOT_FOUND:
                    return $this->getPageNotFound();

                case ResolverException::FILE_NOT_FOUND:
                    return $this->getFileNotFound();

                case ResolverException::NO_DATA_FOUND:
                    return $this->getNoDataFound();

                case ResolverException::NO_PERMISSION:
                    return $this->getNoPermission();

                default:
                    return $this->getError($exception);
            }
        } catch(Throwable $exception) {
            if(!($exception instanceof SFW2Exception)) {
                $exception = new SFW2Exception(
                    $exception->getMessage(),
                    SFW2Exception::UNKNOWN,
                    $exception
                );
            }
            return $this->getError($exception);
        }
    }

    public function getFileNotFound() {
        header("HTTP/1.0 404 Not Found");
        $title = '404';
        $caption = 'Datei nicht vorhanden';
        $description =
            'Die gewünschte Datei konnten nicht gefunden werden. ' .
            'Bitte prüfe die URL auf Fehler und ' .
            'drücke dann den reload-Button in deinem Browser.';
        return $this->handle($title, $caption, $description);
    }

    public function getNoDataFound() {
        header("HTTP/1.0 404 Not Found");
        $title = '404';
        $caption = 'Daten nicht vorhanden';
        $description =
            'Die angeforderten Daten konnten nicht gefunden werden. ' .
            'Bitte prüfe die URL auf Fehler und ' .
            'drücke dann den reload-Button in deinem Browser.';
        return $this->handle($title, $caption, $description);
    }

    public function getNoPermission() {
        header("HTTP/1.0 403 Forbidden");
        $title = '403';
        $caption = 'Keine Berechtigung';
        $description =
            'Dir fehlt die Berechtigung für diese Seite. Bitte melde '.
            'dich an und probiere es erneut.';
        return $this->handle($title, $caption, $description);
    }

    public function getPageNotFound() {
        header("HTTP/1.0 404 Not Found");
        $title = '404';
        $caption = 'Seite nicht vorhanden';
        $description =
            'Die gewünschte Seite konnte nicht ' .
            'gefunden werden. Bitte prüfe die URL auf Fehler und ' .
            'drücke dann den reload-Button in deinem Browser.';
        return $this->handle($title, $caption, $description);
    }

    public function getOffline() {
        $title = 'Offline!';
        $caption = 'Die Seiten sind aktuell offline';
        $description =
            'Aufgrund von umfangreichen Wartungsarbeiten sind die ' .
            'Webseiten im Moment leider nicht zu erreichen. ' .
            'Bitte versuche es später noch einmal.';
        return $this->handle($title, $caption, $description);
    }

    public function getError(SFW2Exception $exception) {
        $title = 'Achtung!';
        $caption = 'Schwerwiegender Fehler aufgetreten!';
        $description =
            'Es ist ein interner Fehler [ID: ' . $exception->getIdentifier() . '] ' .
            'aufgetreten. ' . PHP_EOL . 'Bitte wende Dich umgehend an den ' .
            '<a href="mailto: ' . $this->config->getVal('project', 'eMailWebMaster') .
            '?subject=Fehler-ID: ' . $exception->getIdentifier() .
            '">Webmaster</a>.';

        $debug = null;
        if($this->config->getVal('debug', 'on', false)) {
            $debug = $exception;
        } else {
            $this->saveError($exception);
        }
        return $this->handle($title, $caption, $description, $debug);
    }

    protected function handle($title, $caption, $description, $debugData = null) : Result {
        $result = new Content('plain');
        $result->assignArray([
            'title'       => $title,
            'caption'     => $caption,
            'description' => $description,
            'debugData'   => $debugData
        ]);
        return $result;
    }

    protected function saveError(SFW2Exception $exception) {
        $path = $this->config->getVal('path', 'log');

        if($path == '') {
            return;
        }
        $fd = fopen(
            $path . DIRECTORY_SEPARATOR . $exception->getIdentifier() . '.log',
            'a'
        );
        fwrite($fd, $exception->getTimeStamp());
        fwrite($fd, $exception->__toString());
        fclose($fd);
    }
}

