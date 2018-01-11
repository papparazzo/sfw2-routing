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

class SitemapController extends Controller {

    public function index() {
        $content = new Content('content/sitemap');
        # FIXME Adressen ändern!!!
        #$content->appendCSSFile($file);
        #$content->assign('title', 'Hallod');
        $this->container->create('SFW2\Core\Database');
        $content->assign('sitemapdata', $this->container->create('SFW2\Routing\Menu')->getFullMenu());
        return $content;
    }
}
