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

namespace SFW2\Routing\Controller;

use SFW2\Routing\Controller;
use \SFW2\Routing\Result\Content;

class StaticController extends Controller {

    protected $template;

    public function __construct(int $controllerId, string $template) {
        parent::__construct($controllerId);
        $this->template = $template;
    }

    public function index() {
        $content = new Content($this->template);
        # FIXME Adressen ändern!!!
        $content->assign('chairman', 'Herr Bla');
        $content->assign('mailaddr', 'ddd');
        #$content->appendCSSFile($file);
        #$content->assign('title', 'Hallod');
        return $content;
    }
}
