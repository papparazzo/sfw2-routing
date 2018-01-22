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

use SFW2\Core\View;

abstract class Obfuscator {

    protected $displayName  = null;
    protected $addr         = null;
    protected $inheritStyle = false;

    public function __construct($addr, $displayName = null, $subject = null, $inheritStyle = false) {
        $this->inheritStyle = $inheritStyle;
        if(empty($addr)) {
            return;
        }
        $this->addr        = $this->encodeAddr($addr, $subject);
        $this->displayName = $this->obfuscateDisplayName($displayName ?? $addr);
    }

    public function __toString() {
        if($this->addr == null) {
            return '';
        }
        $view = new View(dirname(__FILE__) . '/Template/Obfuscator.phtml'); // FIXME
        $view->assign("addr",         $this->addr);
        $view->assign("displayName",  $this->displayName);
        $view->assign("inheritStyle", $this->inheritStyle);
        return $view->getContent();
    }

    abstract protected function obfuscateDisplayName($displayName);

    abstract protected function encodeAddr($addr, $subject);
}