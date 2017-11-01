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

use SFW2\Routing\Bootstrap\BootstrapException;
use SFW2\Core\SFW2Exception;
use Dice\Dice;
use Throwable;

class Bootstrap {

    /**
     * @var \Dice\Dice
     */
    protected $container;

    /**
     * @var \SFW\Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $rootPath;

    public function __construct() {
        if(defined('SFW2')) {
            throw new BootstrapException(
                'Framework allready launched',
                BootstrapException::ALLREADY_LAUNCHED
            );
        }
        define('SFW2', true);
        $this->rootPath = __DIR__;
    }

    public function run(string $configPath) {
        $this->container = new Dice;
        $this->container->addRules([
            'SFW2\Core\Config' =>
            [
                'shared' => true,
                'constructParams' => [
                    $configPath . DIRECTORY_SEPARATOR . 'conf.common.php',
                    $this->rootPath . DIRECTORY_SEPARATOR . 'conf.common.php'
                ]
            ]
        ]);
        $this->config = $this->container->create('SFW2\Core\Config');

        if($this->config->getVal('debug', 'on', false)) {
            error_reporting(E_ALL);
            ini_set('display_errors', true);
        } else {
            error_reporting(0);
            ini_set('display_errors', false);
        }

        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'excpetionHandler']);
        mb_internal_encoding('UTF-8');
        ini_set('memory_limit', $this->config->getVal('misc', 'memoryLimit'));
        ini_set(LC_ALL, $this->config->getVal('misc', 'locale'));
        setlocale(LC_TIME, $this->config->getVal('misc', 'locale') . ".UTF-8");
        date_default_timezone_set($this->config->getVal('misc', 'timeZone'));

        $this->container->addRules([
            'SFW2\Core\Session' =>
            [
                'shared' => true,
                'constructParams' => [
                    $_SERVER['SERVER_NAME']
                ]
            ]
        ]);

        $this->container->addRules([
            'SFW2\Core\Database' =>
            [
                'shared' => true,
                'constructParams' => [
                    $this->config->getVal('database', 'host'),
                    $this->config->getVal('database', 'usr'),
                    $this->config->getVal('database', 'pwd'),
                    $this->config->getVal('database', 'db'),
                ]
            ]
        ]);

        if($this->isOffline()) {
            $dispatcher = new Dispatcher();

            require 'web/templates/exframe.phtml';


            #$handler = new Response\Handler\Offline($this->config);
            #$handler->handle();
            return;
        }


        $resolver = new Resolver([], $this->container);
        $resolver->getContent(new Request($_SERVER));






        $ctrlConf = $configPath . 'conf.controller.php';
        if(!is_file($ctrlConf)) {
            throw new BootstrapException(
                'File "' . $ctrlConf . '" does not exist',
                BootstrapException::CONTROLLER_ARRAY_NOT_SET
            );
        }

        $ctrls = require_once $this->rootPath . 'SFW/conf.controller.php';
        $ctrls += require_once $ctrlConf;
        $request = new Request($server);

        /*
        $resolver = new ControllerResolver($this->config, $ctrls);

        $data = array();
        $data['content'] = $resolver->getContent($request);
        $data['title'] = $this->config->getVal('project', 'title');
        $data['menu'] = $this->config->menu->getMenu();
        $data['authenticated'] = false;
        $data['jsfiles'] = array(
            'ttps://code.jquery.com/jquery-3.2.1.slim.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js',
            'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js'
        );
        $data['cssfiles'] = array(
            'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css',
            '/public/css/common.css'
        );


        $handler = new Response\Handler\Standard($this->config, $data);
        $handler->handle();
        */
    }

    public function errorHandler($errno, $errstr, $errfile, $errline, $context) {
        if(!$this->config->getVal('debug', 'on', false)) {
            return true;
        }


        $this->printErrorAndDie($errno, $errstr, $errfile, $errline, debug_backtrace());
    }

    public function excpetionHandler(Throwable $exception) {
        if(!($exception instanceof SFW2Exception)) {
            $exception = new SFW2Exception(
                $exception->getMessage(),
                SFW2Exception::UNKNOWN,
                $exception
            );
        }
        $this->saveError($exception);
        $dispatch = new Dispatcher();
#header("HTTP/1.0 500 Internal Server Error");

        $this->printErrorAndDie(0, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace());
    }

    protected function printErrorAndDie($errno, $errstr, $errfile, $errline, $backTrace) {

        echo $errno . ': ', $errstr . ' in ' . $errfile . ' on line ' . $errline;
        echo '<br />';
        echo '<br />';
        foreach(array_reverse($backTrace) as $k => $v) {
            echo '#' . $k . ' ' . $v['file'] . ':' . $v['line'] . '<br />';
        }
        die();
    }

    protected function isOffline() {
        if(!$this->config->getVal('site', 'offline')) {
            return false;
        }

        $session = $this->container->create('SFW2\Core\Session');

        if($session->isGlobalEntrySet('bypass')) {
            return false;
        }

        if(
            isset($_GET['bypass']) &&
            $_GET['bypass'] == $this->config->getVal('site', 'offlineBypassToken')
        ) {
            $session->setGlobalEntry('bypass', true);
            return false;
        }
        return true;
    }

    protected function saveError(SFW2Exception $exception) {
        $path = $this->config->getVal('path', 'log');

        if($path == '') {
            return;
        }
        $fd = fopen(
            $path . DIRECTORY_SEPARATOR . $exception->getIdentifier() . '.log',
            'a'
        );
        fwrite($fd, $exception->getTimeStamp());
        fwrite($fd, $exception->__toString());
        fclose($fd);
    }
}
