<?php

/**
 *  SFW2 - SimpleFrameWork
 *
 *  Copyright (C) 2018  Stefan Paproth
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
use SFW2\Routing\Result\Content;
use SFW2\Routing\Path;
use SFW2\Routing\User;

use SFW2\Core\Session;

class LoginController extends Controller {

    /**
     * @var \SFW2\Routing\User
     */
    protected $user;

    /**
     * @var \SFW2\Core\Session
     */
    protected $session;

    protected $loginResetPath = '';

    public function __construct(int $pathId, Path $path, User $user, Session $session, $loginResetPathId = null) {
        parent::__construct($pathId);
        $this->user = $user;
        $this->session = $session;

        if($loginResetPathId != null) {
            $this->loginResetPath = $path->getPath($loginResetPathId);
        }
    }

    public function index($all = false) {
        $content = new Content('content/login/login');
        $content->assign('loginResetPath', $this->loginResetPath);
        $content->assign('isAllreadyLoggedIn', $this->user->isAuthenticated());
        $content->assign('firstname', $this->user->getFirstName());
        $content->assign('lastPage', $this->session->getGlobalEntry('current_path'), '');
        return $content;
    }

    public function authenticate() {
        $error = false;
        if(!$this->user->authenticateUser(
            filter_input(INPUT_POST, 'usr'),
            filter_input(INPUT_POST, 'pwd')
        )) {
            $error = true;
        }

        $this->session->setGlobalEntry(User::class, $this->user->getUserId());
        $this->session->regenerateSession();

        $content = new Content(null, $error);
        $content->assign('user', $this->user->getFirstName());
        return $content;
    }

    public function logoff() {
        $this->user->reset();
        $this->session->setGlobalEntry(User::class, $this->user->getUserId());
        $content = new Content('content/login/logoff');
        $content->assign('lastPage', $this->session->getGlobalEntry('current_path'), '');
        return $content;
    }
}
