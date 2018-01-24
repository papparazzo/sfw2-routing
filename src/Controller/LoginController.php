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

class LoginController extends Controller {

    /**
     * @var User
     */
    protected $user;

    protected $loginResetPath = '';

    public function __construct(int $pathId, User $user, Path $path, $loginResetPathId = null) {
        parent::__construct($pathId);
        $this->user = $user;

        if($loginResetPathId != null) {
            $this->loginResetPath = $path->getPath($loginResetPathId);
        }
    }

    public function index() {
        return $this->showLoginScreen();
    }

    public function authenticate() {
        return $this->showLoginScreen(true);

        $content = new Content('plain');
        $content->assign('title', 'Hallo');
        $content->assign('caption', 'Hallo Caption');
        $content->assign('description', 'Hallo Des');

/**
 *         $rv = array("error" => true);
        $usr = $this->config->dto->getSimpleText('usr');
        $pwd = $this->config->dto->getHash('pwd');

        $user = new \SFW\User($this->config->database, $this->config->session);
        if(!$user->authenticateUser($usr, $pwd)){
            return $rv;
        }
        $this->config->session->regenerateSession();
        $this->config->user = $user;
        return array(
            "error" => false,
            "firstname" => $user->getFirstName()
        );

 *
 */
        return $content;
    }

    protected function showLoginScreen($showError = false) {
        $content = new Content('content/login/login');
        $content->assign('loginResetPath', $this->loginResetPath);
        $content->assign('showError', $showError);
        $content->assign('isAllreadyLoggedIn', $this->user->isAuthenticated());
        $content->assign('firstname', $this->user->getFirstName());
        $content->assign('title', 'Hallo');
        return $content;
    }

/*
    public function logoff() {
        $this->config->user->reset();
        return array("error" => false);
    }
  */








    public function gettoken() {
        return array(
            'error' => false,
            'token' => $this->config->session->generateToken()
        );
    }

    public function check() {
        return array(
            'error' => false,
            'islin' => $this->config->user->isAuthenticated()
        );
    }


}
