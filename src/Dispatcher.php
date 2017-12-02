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

use SFW2\Routing\Result\File;
use SFW2\Routing\Result\Redirect;

class Dispatcher {

    /**
     * @var \SFW2\Routing\Request
     */
    public $request = null;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function dispatch(Result $result) {
        if($result instanceof File) {

        }

        if($result instanceof Redirect) {

        }

        switch($this->request->getRequestType()) {
            case Request::REQUEST_TYPE_AJAX_JSON:
                $response = new ResponseType\Json($result);
                break;

            case Request::REQUEST_TYPE_AJAX_XML:
                $response = new ResponseType\Xml($result);
                break;

            case Request::REQUEST_TYPE_HTML:
                $response = new ResponseType\Html($result);
                break;
        }
        $response->dispatch();
    }
}
