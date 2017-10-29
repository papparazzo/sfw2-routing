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

namespace SFW2\Routing\Dispatcher;

use SFW2\Routing\Dispatcher\Handler;

class Json extends Handler {

    public function handle() {
        if(!is_array($this->container['content'])) {
            $this->container['content'] = [
                "error"    => true,
                "errormsg" => 'Es ist ein interner Fehler aufgetreten.'
            ];
        }
        if(!isset($this->container['content']['error'])) {
            $this->container['content'] = [
                "error"    => true,
                "errormsg" => 'Es ist ein interner Fehler aufgetreten.'
            ];
        }
        header('Content-type: application/json');
        echo json_encode($this->container['content']);
    }
}