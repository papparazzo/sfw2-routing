<?php

/**
 *  SFW2 - SimpleFrameWork
 *
 *  Copyright (C) 2019  Stefan Paproth
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

use SFW2\Core\View as BaseView;

use SFW2\Routing\PathMap\PathMap;

use DateTime;
use DateTimeZone;

class View extends BaseView {

    protected $request;

    protected $pathMap;

    public function __construct(string $template, Request $request, PathMap $pathMap) {
        parent::__construct($template);
        $this->request  = $request;
        $this->pathMap = $pathMap;
    }

    public function getCurrentPath() : string {
        return $this->request->getPath();
    }

    public function getPathSimplified() : string {
        return $this->request->getPathSimplified();
    }

    public function getPathById(int $pathId) : string {
        return $this->pathMap->getPath($pathId);
    }

    protected function showContent() {
        if(!isset($this->vars['modificationDate']) || $this->vars['modificationDate'] == '') {
            $this->vars['modificationDate'] = new DateTime('@' . filemtime($this->template), new DateTimeZone('Europe/Berlin'));
        }

        if(is_string($this->vars['modificationDate'])) {
            $this->vars['modificationDate'] = new DateTime($this->vars['modificationDate'], new DateTimeZone('Europe/Berlin'));
        }

        #Mi., 11. Mai. 2016
        $this->vars['modificationDate'] = strftime('%a., %d. %b. %Y', $this->vars['modificationDate']->getTimestamp());
        parent::showContent();
    }
}
