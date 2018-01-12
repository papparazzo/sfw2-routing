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

class AuthenticationController extends Controller {

    public function index() {
        $content = new \SFW2\Routing\Result\Content('content/authentication/login');
        $content->assign('isAllreadyLoggedIn', true);
        $content->assign('firstname', 'Trude');

        $content->assign('title', 'Hallo');

        $content->assign('description', 'Hallo Des');
        return $content;
    }

    public function resetPassword() {
        $content = new \SFW2\Routing\Result\Content('content/authentication/loginreset');

        #$content->assign('state', 'start');
        #$content->assign('state', 'send');
        #$content->assign('state', 'ok');
        $content->assign('state', 'error');
        $content->assign('name', 'Hans');
        $content->assign('expire', '3 Wochen');

        $content->assign('title', 'Hallo');

        $content->assign('description', 'Hallo Des');
        return $content;

    }

    public function authenticate() {
        $content = new \SFW2\Routing\Result\Content('decorate');
        #$content = new \SFW2\Routing\Result\Content('home');
        $content->assign('title', 'Hallo');
        $content->assign('caption', 'Hallo Caption');
        $content->assign('description', 'Hallo Des');
        return $content;
    }

}
