<?php

/**
 *  SFW2 - SimpleFrameWork
 *
 *  Copyright (C) 2020  Stefan Paproth
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

namespace SFW2\Routing\Router;

use SFW2\Core\SFW2Exception;
use Throwable;

class Exception extends SFW2Exception
{
    final public const BAD_REQUEST = 400;  // Invalid data given
    final public const FORBIDDEN = 403;  // No permission;
    final public const NOT_FOUND = 404;  // Page not found

    final public const INTERNAL_SERVER_ERROR = 500;  // unknown error
    final public const SERVICE_UNAVAILABLE = 503;  // page offline

    public function __construct(string $msg, int $code = self::INTERNAL_SERVER_ERROR, Throwable $prev = null)
    {
        parent::__construct($msg, $code, $prev);
    }


    /*

        public function getContent(Request $request, Resolver $resolver) : AbstractResult {
                switch($exception->getCode()) {
                    case ResolverException::PAGE_NOT_FOUND:
                        return $this->getPageNotFound();

                    case ResolverException::FILE_NOT_FOUND:
                    case ResolverException::NO_DATA_FOUND:
                        return $this->getNoDataFound();

                    case ResolverException::NO_PERMISSION:
                        return $this->getNoPermission();

                    case ResolverException::INVALID_DATA_GIVEN:
                        return $this->getInvalidDataGiven();

                    default:
                        return $this->getError($exception);
                }
            } catch(Throwable $exception) {
                if(!($exception instanceof SFW2Exception)) {
                    $exception = new SFW2Exception($exception->getMessage(), SFW2Exception::UNKNOWN, $exception);
                }
                return $this->getError($exception);
            }
        }
    */

    public function getInvalidDataGiven() {
        header("HTTP/1.0 400 Bad Request");
        $title = '400';
        $caption = 'Ung�ltige Daten';
        $description = 'Die Anfrage-Nachricht enthielt ung�ltige Daten. Bitte pr�fe die URL auf Fehler und dr�cke dann den reload-Button in deinem Browser.';

        return $this->handle($title, $caption, $description);
    }

    public function getNoPermission() {
        header("HTTP/1.0 403 Forbidden");
        $title = '403';
        $caption = 'Keine Berechtigung';
        $description = 'Dir fehlt die Berechtigung f�r diese Seite. Bitte melde dich an und probiere es erneut.';

        return $this->handle($title, $caption, $description);
    }

    public function getNoDataFound() {
        header("HTTP/1.0 404 Not Found");
        $title = '404';
        $caption = 'Daten nicht vorhanden';
        $description =
            'Die angeforderten Daten konnten nicht gefunden werden. Bitte pr�fe die URL auf Fehler und dr�cke dann den reload-Button in deinem Browser.';

        return $this->handle($title, $caption, $description);
    }

    public function getPageNotFound() {
        header("HTTP/1.0 404 Not Found");
        $title = '404';
        $caption = 'Seite nicht vorhanden';
        $description = 'Die gew�nschte Seite konnte nicht gefunden werden. Bitte pr�fe die URL auf Fehler und dr�cke dann den reload-Button in deinem Browser.';

        return $this->handle($title, $caption, $description);
    }

    public function getOffline() {
        header("HTTP/1.0 503 Service Unavailable");
        $title = '503!';
        $caption = 'Die Seiten sind aktuell offline';
        $description =
            'Aufgrund von umfangreichen Wartungsarbeiten sind die Webseiten im Moment leider nicht zu erreichen. Bitte versuche es sp�ter noch einmal.';

        return $this->handle($title, $caption, $description);
    }

    public function getError(Exception $exception) {
        header("HTTP/1.0 500 Internal Server Error");
        $title = '500';
        $caption = 'Interner Fehler aufgetreten!';
        $description = 'Es ist ein interner Fehler aufgetreten. <br />[ID: ' . $this->identifier . ']';

        return $this->handle($title, $caption, $description);
    }

    protected function handle($title, $caption, $description): AbstractResult
    {
        $result = new Content('plain', true);
        $result->assignArray(['title' => $title, 'caption' => $caption, 'description' => $description]);

        return $result;
    }
}



/**
 * @throws \SFW2\Routing\Resolver\Exception
 * /
protected function hasFullPermission(int $pathId, string $action): bool
{
    if (is_null($this->permission)) {
        return true;
    }

    switch ($this->permission->getPermission($pathId, $action)) {
        case PermissionInterface::VORBIDEN:
            throw new ResolverException('permission not allowed', ResolverException::NO_PERMISSION);

        case PermissionInterface::FULL:
            return true;
        case PermissionInterface::RESTRICTED:
            return false;
    }
}
 * */