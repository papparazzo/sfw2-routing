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

namespace SFW2\Routing\Widget\Obfuscator;

class Phone extends Obfuscator {

    protected function obfuscateDisplayName($displayName) {
        $displayName = htmlentities($displayName, ENT_COMPAT, 'utf-8', false);
        $pos = (int)(mb_strlen($displayName) / 2);
        return
            mb_substr($displayName, 0, $pos) . '<br class="hidden"/>' .
            mb_substr($displayName, $pos);
    }

    protected function encodeAddr($addr, $subject) {
        $addr = preg_replace('#[^0-9]#', '', $addr . $subject);

        $addr    = 'tel:' . $addr;

        $a = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '/', '+', '-', ' '];
        $b = ['b', 'z', 'd', 'k', 'r', 'F', 'T', 'h', 'L', 'M', 'q', 'p', '.', 'y'];

        return str_replace($a, $b, $addr);
    }
}