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

namespace SFW2;

class Content {

    protected $jsFiles     = [];
    protected $cssFiles    = [];


    public function __construct(Core\View $view) {
        ;
    }



    public function addJSFile($file) {
        $this->jsFiles[$file] = $file;
    }

    public function addCSSFile($file) {
        $this->cssFiles[$file] = $file;
    }

    public function getJSFiles() : Array {
        return $this->jsFiles;
    }

    public function getCSSFiles() : Array {
        return $this->cssFiles;
    }

    public function appendJSFiles(Array $files) {
        $this->jsFiles = array_merge($this->jsFiles, $files);
    }

    public function appendJSFile(string $file) {
        $this->jsFiles[] = $file;
    }

    public function appendCSSFiles(Array $files) {
        $this->cssFiles = array_merge($this->cssFiles, $files);
    }

    public function appendCSSFile(string $file) {
        $this->cssFiles[] = $file;
    }

    public function getContent() : string {

    }




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