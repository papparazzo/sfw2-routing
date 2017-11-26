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

class ResponseHandler {

    /**
     * @var Request
     */
    protected $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function getContent(Resolver $resolver) : Result {

        try {
            # if(isset($this->server['HTTP_X_REQUESTED_WITH'])) {
            #     return new Dispatcher\Handler\XML($this->registry, $data);
            # }
            # if(isset($this->server['HTTP_X_REQUESTED_WITH'])) {
            #     return new Dispatcher\Handler\Json($this->registry, $data);
            # }
            # return new Html($this->registry, $data);

            return $this->resolver->getResult($request);
        } catch(ResolverException $ex) {
            switch($ex->getCode()) {
                case ResolverException::PAGE_NOT_FOUND:
                    return $this->pageNotFound();

                case ResolverException::FILE_NOT_FOUND:
                    return $this->fileNotFound();

                case ResolverException::NO_DATA_FOUND:
                    return $this->noDataFound();

                case ResolverException::NO_PERMISSION:
                    return $this->noPermission();

                default:
                    return $this->error($ex);
            }
        }
    }

    public function fileNotFound() {
        header("HTTP/1.0 404 Not Found");
        $title = '404';
        $caption = 'Datei nicht vorhanden';
        $description =
            'Die gewünschte Datei konnten nicht gefunden werden. ' .
            'Bitte prüfe die URL auf Fehler und ' .
            'drücke dann den reload-Button in deinem Browser.';
        return $this->handle($title, $caption, $description);
    }

    public function noDataFound() {
        header("HTTP/1.0 404 Not Found");
        $title = '404';
        $caption = 'Daten nicht vorhanden';
        $description =
            'Die angeforderten Daten konnten nicht gefunden werden. ' .
            'Bitte prüfe die URL auf Fehler und ' .
            'drücke dann den reload-Button in deinem Browser.';
        return $this->handle($title, $caption, $description);
    }

    public function noPermission() {
        header("HTTP/1.0 403 Forbidden");
        $title = '403';
        $caption = 'Keine Berechtigung';
        $description =
            'Dir fehlt die Berechtigung für diese Seite. Bitte melde '.
            'dich an und probiere es erneut.';
        return $this->handle($title, $caption, $description);
    }

    public function pageNotFound() {
        header("HTTP/1.0 404 Not Found");
        $title = '404';
        $caption = 'Seite nicht vorhanden';
        $description =
            'Die gewünschte Seite konnte nicht ' .
            'gefunden werden. Bitte prüfe die URL auf Fehler und ' .
            'drücke dann den reload-Button in deinem Browser.';
        return $this->handle($title, $caption, $description);
    }

    public function offline() {
        $title = 'Offline!';
        $caption = 'Die Seiten sind aktuell offline';
        $description =
            'Aufgrund von umfangreichen Wartungsarbeiten sind die ' .
            'Webseiten im Moment leider nicht zu erreichen. ' .
            'Bitte versuche es später noch einmal.';
        return $this->handle($title, $caption, $description);
    }

    public function error(SFW2Exception $exception, $debug = false) {
        $title = 'Achtung!';
        $caption = 'Schwerwiegender Fehler aufgetreten!';
        $description =
            'Es ist ein interner Fehler [ID: ' . $exception->getIdentifier() . '] ' .
            'aufgetreten. ' . PHP_EOL . 'Bitte wende Dich umgehend an den ' .
            '<a href="mailto: ' . $this->config->getVal('project', 'eMailWebMaster') .
            '?subject=Fehler-ID: ' . $exception->getIdentifier() .
            '">Webmaster</a>.';

        return $this->handle($title, $caption, $description, $debug ? $exception : null);
    }

    protected function handle($title, $caption, $description, $debug = null) {
        $data = [
            'title'       => $title,
            'caption'     => $caption,
            'description' => $description,
        ];
        if(!is_null($debug)) {
            $data['debug'] = $debug;
        }
        return $data;
    }
}

