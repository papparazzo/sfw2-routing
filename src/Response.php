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

class Response {

    public function __construct() {
        ;
    }


    public function doit(Resolver $resolver, Request $request) : Content {
        try {
            return $resolver->getContent($request);
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
                    throw new SFW2Exception('Unknown Exception occured', 0, $ex);
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

