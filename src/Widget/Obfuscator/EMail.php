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

class EMail extends Obfuscator {

    protected function obfuscateDisplayName($displayName) {
        return str_replace(
            '@',
            '<br class="noshow" />@',
            htmlentities($displayName, ENT_COMPAT, 'utf-8', false)
        );
    }

    protected function encodeAddr($addr, $subject) {
        if($subject) {
            $subject = '?subject=' . $subject;
        }

        $addr    = 'mailto:' . $addr . $subject;
        $keycode = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $ret     = '';

        for($i = 0; $i < mb_strlen($addr); ++$i) {
            $char = mb_substr($addr, $i, 1);

            $pos = mb_strpos($keycode, mb_strtoupper($char));

            if($pos === false) {
                $ret .= $char;
                continue;
            }

            $pos = $pos - 14;

            if($pos < 0) {
                $pos = mb_strlen($keycode) + $pos;
            }

            if($char == mb_strtoupper($char)) {
                $char = $keycode[$pos];
            } else {
                $char = mb_strtolower($keycode[$pos]);
            }
            $ret .= $char;
        }
        return $ret;
    }
}