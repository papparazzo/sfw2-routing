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

use SFW2\Routing\Bootstrap\Exception AS BootstrapException;
use SFW2\Routing\User;

use SFW2\Core\Database;
use SFW2\Core\Session;
use SFW2\Core\Config;

use Dice\Dice;
use ErrorException;

class Bootstrap {

    /**
     * @var \Dice\Dice
     */
    protected $container;

    /**
     * @var \SFW2\Core\Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @var array
     */
    protected $server;

    /**
     * @var array
     */
    protected $get;

    /**
     * @var array
     */
    protected $post;

    /**
     * @param array $server
     * @param array $get
     * @param array $post
     * @throws BootstrapException
     */
    public function __construct(array $server, array $get, array $post) {
        if(defined('SFW2')) {
            throw new BootstrapException(
                'Framework allready launched',
                BootstrapException::ALLREADY_LAUNCHED
            );
        }
        define('SFW2', true);
        $this->container = new Dice;
        $this->rootPath = __DIR__;
        $this->server   = $server;
        $this->get      = $get;
        $this->post     = $post;
    }

    public function run(string $configPath) {
        $this->loadConfig($configPath);
        $this->setUpEnvironment();
        $this->setUpContainer();
        $this->setUpRuntime();

        $response = $this->container->create(ResponseHandler::class);
        $request = $this->container->create(Request::class);

        if($this->isOffline($this->container->create(Session::class))) {
            $result = $response->getOffline();
        } else {
            $resolver = $this->container->create(Resolver::class);
            $result = $response->getContent($request, $resolver);
        }
        $path = $this->container->create(Path::class);
        $pathId = $path->getPathId($request->getPath());

        $dispatcher = $this->container->create(Dispatcher::class);
        $dispatcher->dispatch($pathId, $result, $this->container);
    }

    protected function loadConfig(string $configPath) {
        $this->container->addRules([
            Config::class => [
                'shared' => true,
                'constructParams' => [
                    $configPath . DIRECTORY_SEPARATOR . 'conf.common.php',
                    $this->rootPath . DIRECTORY_SEPARATOR . 'conf.common.php'
                ]
            ]
        ]);
        $this->config = $this->container->create(Config::class);
    }

    protected function setUpEnvironment() {
        if($this->config->getVal('debug', 'on', false)) {
            error_reporting(E_ALL);
            ini_set('display_errors', true);
        } else {
            error_reporting(0);
            ini_set('display_errors', false);
        }

        set_error_handler([$this, 'errorHandler']);
        mb_internal_encoding('UTF-8');
        ini_set('memory_limit', $this->config->getVal('misc', 'memoryLimit'));
        ini_set(LC_ALL, $this->config->getVal('misc', 'locale'));
        setlocale(LC_TIME, $this->config->getVal('misc', 'locale') . ".UTF-8");
        date_default_timezone_set($this->config->getVal('misc', 'timeZone'));
    }

    protected function setUpContainer() {
        $this->container->addRules([
            Session::class => [
                'shared' => true,
                'constructParams' => [
                    $this->server['SERVER_NAME']
                ]
            ],
            Database::class => [
                'shared' => true,
                'constructParams' => [
                    $this->config->getVal('database', 'host'),
                    $this->config->getVal('database', 'user'),
                    $this->config->getVal('database', 'pwd'),
                    $this->config->getVal('database', 'db'),
                ]
            ],
            Path::class => ['shared' => true],
            Menu::class => ['shared' => true],
            Request::class => [
                'shared' => true,
                'constructParams' => [
                    $this->server,
                    $this->get,
                    $this->post
                ]
            ],
            Resolver::class => [
                'shared' => true,
                'substitutions' => [
                    Dice::class => $this->container
                ]
            ],
            Permission::class => [
                'shared' => true
            ]
        ]);
    }

    protected function setUpRuntime() {
        $session = $this->container->create(Session::class);
        $currentUser = $session->getGlobalEntry(User::class);
        $this->container->addRules([
            User::class => [
                'shared' => true,
                'constructParams' => [$currentUser]
            ]
        ]);
    }

    protected function isOffline(Session $session) {
        if(!$this->config->getVal('site', 'offline')) {
            return false;
        }

        if($session->isGlobalEntrySet('bypass')) {
            return false;
        }

        if(
            isset($this->get['bypass']) &&
            $this->get['bypass'] == $this->config->getVal('site', 'offlineBypassToken')
        ) {
            $session->setGlobalEntry('bypass', true);
            return false;
        }
        return true;
    }

    public function errorHandler($errno, $errstr, $errfile, $errline) {
        if(!$this->config->getVal('debug', 'on', false)) {
            return true;
        }

        if(!(error_reporting() & $errno)) {
            return false;
        }
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}
