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

use SFW2\Core\Database;

class User {

    const MAX_RETRIES = 100;

    protected $userid        = 0;
    protected $isAdmin       = false;
    protected $firstName     = '';
    protected $lastName      = '';
    protected $mailAddr      = '';

    protected $authenticated = false;

    /**
     * @var \SFW2\Core\Database
     */
    protected $database = null;

    public function __construct(Database $database, $userId = 0) {
        $this->database = $database;
        $this->loadUserById($userId);
    }

    public function loadUserById($userId) {
        if($userId == 0) {
            $this->reset();
            return true;
        }

        $stmt =
            "SELECT `Id`, `FirstName`, `LastName`, `Email`, `Password`, `Admin` " .
            "FROM `sfw2_user` " .
            "WHERE `Id` = '%s' " .
            "AND `Active` = '1'";

        $rv = $this->database->select($stmt, [$userId]);

        if(count($rv) != 1) {
            return false;
        }
        $rv = $rv[0];
        $this->firstName = $rv['FirstName'];
        $this->lastName  = $rv['LastName'];
        $this->mailAddr  = $rv['Email'];
        $this->userid    = $rv['Id'];
        $this->isAdmin   = $rv['Admin'] == '1' ? true : false;

        return $this->authenticated = true;
    }

    public function authenticateUser($loginName, $pwd) {
        $this->reset();
        $stmt =
            "SELECT `Id`, `FirstName`, `LastName`, `Email`, `Password`, `Admin`, " .
            "IF(CURRENT_TIMESTAMP > `LastTry` + POW(2, `Retries`) - 1, 1, 0) " .
            "AS `OnTime` " .
            "FROM `sfw2_user` " .
            "WHERE `LoginName` LIKE '%s' " .
            "AND `Active` = '1'";

        $rv = $this->database->select($stmt, [$loginName]);

        if(count($rv) != 1) {
            return false;
        }

        $rv = $rv[0];
        if($rv['OnTime'] == 0) {
            return false;
        }

        if(!$this->checkPassword($rv['Id'], $rv['Password'], $pwd)) {
            $this->updateRetries($loginName, false);
            return false;
        }

        $this->updateRetries($loginName, true);

        $this->firstName = $rv['FirstName'];
        $this->lastName  = $rv['LastName'];
        $this->mailAddr  = $rv['Email'];
        $this->userid    = $rv['Id'];
        $this->isAdmin   = $rv['Admin'] == '1' ? true : false;

        return $this->authenticated = true;
    }

    public function setPassword($userId, $password) {
        $stmt =
            "UPDATE `sfw2_user` " .
            "SET `Password` = '%s' " .
            "WHERE `Id` = '%s' ";

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->database->update($stmt, array($hash, $userId));
    }

    public function reset($firstName = '', $lastName = '', $mailAddr = '') {
        $this->authenticated = false;
        $this->firstName     = $firstName;
        $this->lastName      = $lastName;
        $this->mailAddr      = $mailAddr;
        $this->userid        = 0;
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
        return mb_substr($this->firstName, 0, 1) . '. ' . $this->lastName;
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

    protected function checkPassword($userId, $hash, $password) {
        if(!password_verify($password, $hash)) {
            return false;
        }

        if(!password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $this->setPassword($userId, $password);
        }
        return true;
    }

    protected function updateRetries($loginName, $sucess) {
        $stmt = "UPDATE `sfw2_user` ";
        if($sucess) {
            $stmt .= "SET `Retries` = 0 ";
        } else {
            $stmt .=
                "SET `Active` = IF(`Retries` + 1 < " .
                self::MAX_RETRIES .  ", 1, 0), " .
                "`Retries` = IF(`Retries` + 1 < " .
                self::MAX_RETRIES .  ", `Retries` + 1, 0) ";
        }
        $stmt .=
            "WHERE `LoginName` = '%s' " .
            "AND `Active` = 1 " .
            "AND CURRENT_TIMESTAMP > `LastTry` +  POW(2, `Retries`) - 1";

        $this->database->update($stmt, array($loginName));
    }
}