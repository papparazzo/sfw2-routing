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
use SFW2\Routing\Result\Content;
use SFW2\Routing\User;

class AuthenticationController extends Controller {

    /**
     * @var User
     */
    protected $user;

    public function __construct(int $pathId, User $user) {
        parent::__construct($pathId);
        $this->user = $user;
    }

    public function index() {
        $content = new Content('content/authentication/login');
        $content->assign('isAllreadyLoggedIn', $this->user->isAuthenticated());
        $content->assign('firstname', $this->user->getFirstName());
        $content->assign('title', 'Hallo');
        return $content;
    }

    public function resetPassword() {
        $content = new Content('content/authentication/loginreset');

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
        $content = new Content('decorate');
        $content->assign('title', 'Hallo');
        $content->assign('caption', 'Hallo Caption');
        $content->assign('description', 'Hallo Des');
        return $content;
    }

}




/*
class Authentication extends \SFW\Controller {

    public function login() {
        $rv = array("error" => true);
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
    }

    public function logoff() {
        $this->config->user->reset();
        return array("error" => false);
    }

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
 *




class LoginReset extends \SFW\Controller {

    const EXPIRE_DATE_OFFSET = 86400; #24 * 60 * 60;

    public function index() {
        $view = new \SFW\View();
        $view->assign('state', 'start');
        return $view->getContent(
            $this->config->getTemplateFile('LoginReset')
        );
    }

    public function tryreset() {
        $state = 'start';
        $tmp = [];

        $tmp['name'] = 't';#$this->dto->getName(
#            'name',
#            true,
#            'Der Loginname'
#        );
        $tmp['addr'] = 'u';#$this->dto->getEMailAddr(
#            'addr',
#            true,
#            'Die E-Mail-Adresse'
#        );
        if(
           # !$this->dto->getErrorProvider()->hasErrors() &&
            $this->operate($tmp['addr'], $tmp['name'])
        ) {
        }
            $state = 'send';

        $view = new \SFW\View();
        $view->assign('state', $state);
        $view->assign('tmp',   $tmp);
        $view->assign('expire', $this->getExpireDate(
            self::EXPIRE_DATE_OFFSET)
        );
        return $view->getContent(
            $this->config->getTemplateFile('LoginReset')
        );
    }

    public function confirm() {
        $state = 'error';
        #if($this->validateHash()) {
        #    $state = 'ok';
        #}
        $view = new \SFW\View();
        $view->assign('state', $state);
        $view->assign('expire', $this->getExpireDate(
            self::EXPIRE_DATE_OFFSET)
        );

        return $view->getContent(
            $this->config->getTemplateFile('LoginReset')
        );
    }

    protected function operate($addr, &$name) {
        $hash = md5($name . $addr . time() .  \SFW\Helper::getRandomInt());

        $stmt =
            "UPDATE `sfw2_user` " .
            "SET `ResetExpireDate` = '%s', " .
            "`ResetHash` = '%s' " .
            "WHERE `Email` = '%s' AND `LoginName` = '%s' ";

        $val = $this->config->database->update(
            $stmt,
            array($this->getMySQLExpireDate(), $hash, $addr, $name)
        );

        if($val !== 1) {
#            $this->dto->getErrorProvider()->addError(
#                SFW_Error_Provider::IS_WRONG,
#                array('<NAME>' => 'der Loginname bzw. Die E-Mail-Adresse'),
#                array('name', 'addr')
#            );
            return false;
        }

        $stmt =
            "SELECT CONCAT(`FirstName`, ' ', `LastName`) AS `Name` " .
            "FROM `sfw2_user` " .
            "WHERE `Email` = '%s' AND `LoginName` = '%s'";

        $uname = $this->config->database->selectSingle(
            $stmt,
            array($addr, $name)
        );

        $mail = new SFW_Mailer();
        if(!$mail->confirmPasswordReset($addr, $uname, $hash)) {
            $this->dto->getErrorProvider()->addError(
                SFW_Error_Provider::SEND_FAILED
            );
            return false;
        }
        $name = $uname;
        return true;
    }

    protected function validateHash() {
return false;
        /*
        $hash = '';
        try {
            $hash = $this->dto->getHash('hash');
        } catch(Exception $e) {
            // -#- NOOP
        }

        $stmt =
            "SELECT `Id`, `LoginName`, `EMail`, " .
            "CONCAT(`FirstName`, ' ', `LastName`) AS `Name` " .
            "FROM `sfw2_user` " .
            "WHERE `ResetExpireDate` >= NOW() " .
            "AND `ResetHash` = '%s'";

        $row = $this->db->selectRow($stmt, array($hash));

        if(empty($row)){
            return false;
        }

        $pwd = SFW_Helper::generatePassword();
        $stmt =
            "UPDATE `sfw2_user` " .
            "SET `ResetExpireDate` = NULL, " .
            "`ResetHash` = '', " .
            "`Password` = MD5('%s') " .
            "WHERE `Id` = '%s'";

        if($this->db->update($stmt, array($pwd, $row['Id'])) !== 1) {
            return false;
        }

        $mail = new SFW_Mailer();
        if(!$mail->sendNewPassword(
            $row['EMail'],
            $row['Name'],
            $row['LoginName'],
            $pwd
        )) {
            $this->dto->getErrorProvider()->addError(
                SFW_Error_Provider::SEND_FAILED
            );
            return false;
        }
        return true;
    }

    protected function getMySQLExpireDate() {
        $time = time() + self::EXPIRE_DATE_OFFSET;
        return date('Y-m-d H:i:s', $time);
    }

    protected function getExpireDate($date) {
        return intval($date / 60 / 60) . ' Stunden';
    }
}
*/