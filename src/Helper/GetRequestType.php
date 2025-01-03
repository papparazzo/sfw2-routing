<?php

/**
 *  SFW2 - SimpleFrameWork
 *
 *  Copyright (C) 2024  Stefan Paproth
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

namespace SFW2\Routing\Helper;

use Psr\Http\Message\ServerRequestInterface;

final class GetRequestType
{
    public static function isAjaxRequest(ServerRequestInterface $request): bool
    {
        if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            return true;
        }
        return false;
    }

    public static function isXmlRequest(ServerRequestInterface $request): bool
    {
        if ($request->getHeaderLine('Accept') === 'application/xml') {
            return true;
        }
        return false;
    }

    public static function isJsonRequest(ServerRequestInterface $request): bool
    {
        if ($request->getHeaderLine('Accept') === 'application/json') {
            return true;
        }
        return false;
    }

    public static function isFormRequest(ServerRequestInterface $request): bool
    {
        $data = $request->getQueryParams();
        if(isset($data['getForm'])) {
            return true;
        }
        return false;
    }

    public static function isRestricted(ServerRequestInterface $request): bool
    {
        $perm = $request->getAttribute('sfw2_authority');

        if (is_null($perm)) {
            return false;
        }

        if (isset($perm['restricted']) && $perm['restricted']) {
            return true;
        }
        return false;
    }
}
