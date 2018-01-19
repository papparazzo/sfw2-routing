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

namespace SFW2\Routing;

class User {

    const SEX_MALE    = 'MALE';
    const SEX_FEMAL   = 'FEMALE';
    const SEX_UNKNOWN = 'UNKNOWN';

    protected $userid        = -1;
    protected $isAdmin       = false;
    protected $firstName     = '';
    protected $lastName      = '';
    protected $sex           = self::SEX_UNKNOWN;
    protected $birthday      = null;
    protected $mailAddr      = '';

    protected $phone        = '';
    protected $mobile       = '';

    protected $loginName = '';

    public function __construct(int $userId = -1, $isAdmin = false, string $firstName = '') {
        $this->userid        = $userId;
        $this->isAdmin       = $isAdmin;
        $this->firstName     = $firstName;
        $this->lastName      = '';
        $this->sex           = self::SEX_UNKNOWN;
        $this->birthday      = null;
        $this->mailAddr      = '';
        $this->phone        = '';
        $this->mobile       = '';
        $this->loginName = '';
    }

    public function reset() {
        $this->authenticated = false;
        $this->firstName     = '';
        $this->lastName      = '';
        $this->mailAddr      = '';
        $this->userid        = -1;
        $this->isAdmin       = false;
    }

    public function isAuthenticated() {
        return $this->authenticated;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getUserName() {
        return substr($this->firstName, 0, 1) . '. ' . $this->lastName;
    }

    public function getMailAddr() {
        return $this->mailAddr;
    }

    public function getUserId() {
        return $this->userid;
    }

    public function isAdmin() {
        return $this->isAdmin;
    }
}

