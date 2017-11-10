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

use Dice\Dice;

abstract class Controller {

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var \Dice\Dice
     */
    protected $container;

    public function __construct(Dice $container, $title = null) {
        $this->container = $container;

        if(is_null($title)) {
            $title =  $this->conf->getVal('project', 'title');
        }
        $this->title = $title;
    }

    public function getTitle() {
        return $this->title;
    }

    abstract function index();


/*

    protected $showNewLabel = true;
    protected $pathId       = -1;

        $this->dto        = $dto;
        $this->ep         = new Error\Provider();
        $this->dispatcher = $dispatcher;
        $this->registry   = $registry;
        $this->auth       = $registry->getSession()->getGlobalEntry('auth');
        $this->conf       = $registry->getConfig();
        $this->setPathId();


    public function updateModificationDate() {
        $stmt =
            "UPDATE `sfw_path` " .
            "SET `LastModified` = NOW() ".
            "WHERE PathId = '%s'";

        $this->registry->getDatabase()->update($stmt, array($this->pathId));
    }

    public function getPathId() {
        if($this->pathId == -1) {
            throw new \SFW\Controller\Exception(
                'path-Id not set',
                \SFW\Controller\Exception::INVAL_PATH_ID
            );
        }
        return $this->pathId;
    }


    public function showNewLabel() {
        return $this->showNewLabel;
    }

    public function getUserName() {
        return
            \mb_substr($this->auth->getFirstName(), 0, 1) . '. ' .
            $this->auth->getLastName();
    }

    public function getUserId() {
        return $this->auth->getUserId();
    }

    public function getModificationDate() {
        if($this->modiDate == null) {
            throw new \SFW\Controller\Exception(
                'modification-date not set',
                \SFW\Controller\Exception::INVAL_MODI_DATE
            );
        }
        return $this->modiDate;
    }

    protected function setPathId() {
        $data = array();
        $data[] = $this->dispatcher->getModule();
        $data[] = $this->dispatcher->getController();

        $stmt =
            "SELECT `sfw_path`.`PathId`, `sfw_path`.`LastModified` " .
            "FROM `sfw_path` " .
            "WHERE `sfw_path`.`Module` = '%s' " .
            "AND `sfw_path`.`Controller` = '%s' ";


        if($this->dispatcher->getAction() == Dispatcher::DEFAULT_ACTION) {
            $stmt .= "AND `sfw_path`.`Action` IS NULL ";
        } else {
            $stmt .= "AND `sfw_path`.`Action` = '%s' ";
            $data[] = $this->dispatcher->getAction();
        }

        $rv = $this->registry->getDatabase()->selectRow($stmt, $data);

        if(empty($rv)) {
            return;
        }

        $this->pathId   = $rv['PathId'];
        $this->modiDate = new \SFW\View\Helper\Date($rv['LastModified']);
    }
 */
}