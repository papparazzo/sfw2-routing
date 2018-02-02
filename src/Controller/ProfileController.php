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

use SFW2\Core\Database;

class ProfileController extends Controller {

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var User
     */
    protected $user;

    public function __construct(int $pathId, Database $database, User $user) {
        parent::__construct($pathId);
        $this->database = $database;
        $this->user = $user;
    }

    public function index($all = false) {
        $content = new Content('content/blog/blog');

        $content->assign('menu',       array());#$this->ctrl->getMenu()->getMenuArray());
        $content->assign('deleteable', $this->permission->deleteOwnAllowed($this->pathId));
        $content->assign('divisions', $this->getDivisions());
        $content->assign('isAdmin', $this->user->isAdmin());
        $content->assign('title', (string)$this->title);
        $content->assign('items', $this->loadEntries());
        return $content;


        $this->addJSFile('profile');
        $userid = $this->getUserId();

        $stmt =
            "SELECT `sfw_users`.`FirstName`, `sfw_users`.`LastName`, " .
            "`sfw_users`.`Sex`, `sfw_users`.`LoginName`, " .
            "IF(`sfw_users`.`Birthday` = '0000-00-00', ''," .
            "`sfw_users`.`Birthday`) AS `Birthday`, `sfw_users`.`Email`, " .
            "`sfw_users`.`Phone1`, `sfw_users`.`Phone2`, " .
            "`sfw_position`.`Position`, `sfw_division`.`Alias`, " .
            "`sfw_users`.`Active` " .
            "FROM `sfw_users` " .
            "LEFT JOIN `sfw_position` " .
            "ON `sfw_position`.`UserId` = `sfw_users`.`Id` " .
            "LEFT JOIN `sfw_division` " .
            "ON `sfw_division`.`DivisionId` = `sfw_position`.`DivisionId` " .
            "WHERE `sfw_users`.`id` = '%s'";

        $data = $this->db->selectRow($stmt, array($userid));
        $data['Birthday'] = $this->db->convertFromMysqlDate($data['Birthday']);






        $stmt =
            "SELECT `sfw_users`.`Id`, " .
            "CONCAT(`sfw_users`.`LastName`, ', ', `sfw_users`.`FirstName`) " .
            "AS `Name`" .
            "FROM `sfw_users` ";

        $users = array();
        $users = array_merge(
            array(array('Id' => '-1', 'Name' => '[Neu anlegen]')),
            $this->db->select($stmt)
        );

        $stmt =
            "SELECT `sfw_position`.`Id`, `sfw_position`.`Position`, " .
            "`sfw_division`.`Name` AS `Division`, `sfw_position`.`UserId` " .
            "FROM `sfw_position` " .
            "LEFT JOIN `sfw_division` " .
            "ON `sfw_division`.`DivisionId` = `sfw_position`.`DivisionId` " .
            "LEFT JOIN `sfw_users` " .
            "ON `sfw_users`.`id` = `sfw_position`.`UserId` " .
            "WHERE `sfw_position`.`UserId` IN('-1', '%s') ";

        $positions = array();
        $positions = array_merge(
            array(array(
                'Id'       => '-1',
                'UserId'   => '-1',
                'Position' => '[keine]',
                'Division' => ''
            )),
            $this->db->select($stmt, array($userid))
        );

        $data['Image'] = \SFW\Helper::getImageFileName(
            '/public/images/content/thumb/',
            $data['FirstName'],
            $data['LastName']
        );

        $view = new \SFW\View();
        $view->assign('data',      $data);
        $view->assign('isadmin',   $this->ctrl->isAdmin());
        $view->assign('userid',    $userid);
        $view->assign('users',     $users);
        $view->assign('positions', $positions);
        $view->assignTpl(
            $this->conf->getTemplateFile('PageContent/Profile')
        );

        return $view->getContent();
    }





    public function saveUser() {
        if($this->user->isAdmin()) {
            $userid = $this->getDTO()->getNumeric('user');
        }
        $this->saveUserData($userid);
    }

    public function changeUser() {
        if($this->user->isAdmin()) {
            $userid = $this->getDTO()->getNumeric('id');
        }
    }

    private function saveUserData($userid) {

        $tmp = array();

        $tmp['Sex'      ] = $this->dto->getArrayValue('sex', true, 'Die Anrede', array('MALE', 'FEMALE'));
        $tmp['FirstName'] = $this->dto->getName('firstname', true, 'Der Vorname');
        $tmp['LastName' ] = $this->dto->getName('lastname', true, 'Der Nachname');

        $tmp['Birthday' ] = $this->dto->getDate('birthday', false, 'Das Geburtsdatum');

        $tmp['Email'    ] = $this->dto->getEMailAddr('email', false, 'Die E-Mail-Adresse');
        $tmp['Phone1'   ] = $this->dto->getPhoneNb('phone1', true, 'Die Telefonnummer');
        $tmp['Phone2'   ] = $this->dto->getPhoneNb('phone2', false, 'Die 2. Telefonnummer');

        if($this->isAdmin()) {
            $tmp['Active'   ] = $this->dto->getBool('active');
            $tmp['LoginName'] = $this->dto->getName('loginname', true, 'Der Loginname');
            $tmp['Position' ] = $this->dto->getId('position', true, 'Die Position');

            $add = array();
            $add[] = "`sfw_position`.`id` = '%s'";
            $add[] = "`sfw_position`.`UserId` IN('-1', '%s') ";

            $cnt = $this->db->selectCount(
                'sfw_position',
                $add,
                array($tmp['Position'], $userid)
            );

            if($tmp['Position'] != -1 && $cnt != 1) {
                $this->dto->getErrorProvider()->addError(
                    SFW_Error_Provider::IS_WRONG,
                    array('<NAME>' => 'Die Position'),
                    'position'
                );
            }

            if($tmp['Position'] == -1) {
                $stmt =
                    "UPDATE `sfw_position` " .
                    "SET `UserId` = '%s' " .
                    "WHERE `UserId` = '%s' ";

                $params = array('-1', $userid);
            } else {
                $stmt =
                    "UPDATE `sfw_position` " .
                    "SET `UserId` = '%s' " .
                    "WHERE `Id` = '%s' ";

                $params = array($userid, $tmp['Position']);
            }

            if($this->db->update($stmt, $params) > 1) {
                $this->dto->getErrorProvider()->addError(
                    SFW_Error_Provider::INT_ERR
                );
            }

            if($tmp['LoginName'] != '') {
                $add = array();
                $add[] = "`sfw_users`.`id` != '%s'";
                $add[] = "`sfw_users`.`LoginName` = '%s'";

                $cnt = $this->db->selectCount(
                    'sfw_users',
                    $add,
                    array($userid, $tmp['LoginName'])
                );

                if($cnt != 0) {
                    $this->dto->getErrorProvider()->addError(
                        SFW_Error_Provider::EXISTS,
                        array('<NAME>' => 'Der Loginname'),
                        'loginname'
                    );
                }
            }
        }

        if($this->dto->getErrorProvider()->hasErrors()) {
            return $tmp;
        }

        if($userid == -1) {
            $stmt = "INSERT INTO `sfw_users` SET ";
        } else {
            $stmt = "UPDATE `sfw_users` SET ";
        }

        $stmt .=
            "`Sex` = '%s'," .
            "`FirstName` = '%s', " .
            "`LastName` = '%s', " .
            "`Email` = '%s', " .
            "`Phone1` = '%s', " .
            "`Phone2` = '%s', " .
            "`Birthday` = '%s'";

        $params = array();
        $params[] = $tmp['Sex'      ];
        $params[] = $tmp['FirstName'];
        $params[] = $tmp['LastName' ];
        $params[] = $tmp['Email'    ];
        $params[] = $tmp['Phone1'   ];
        $params[] = $tmp['Phone2'   ];
        $params[] = $tmp['Birthday' ];

        if($this->ctrl->isAdmin()) {
            $stmt .=
                ", `Active` = '%s'" .
                ", `LoginName` = '%s'";
            $params[] = $tmp['Active'   ];
            $params[] = $tmp['LoginName'];
        }

        // TODO: permission, roll und position vereinen!!
        // TODO: Achtung: E-Mail addr wie asdf@t-online.de wird als nicht valide erkannt!!!!
        $params[] = $userid;

        if($userid != -1) {
            $stmt .=  "WHERE `sfw_users`.`id` = '%s'";
        }

        $this->db->update($stmt, $params);
        $this->dto->setSaveSuccess();
        return $tmp;
    }
}
