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

namespace SFW2\Routing\Result;

use SFW2\Routing\Result;

class File extends Result {

    protected $path = '';

    protected $file = '';

    protected $alias = '';

    protected $isTemp = false;

    public function __construct(string $path, string $file, string $alias = '', bool $isTemp = false) {
        if($alias == '') {
            $alias = $file;
        }
        $this->alias = $alias;
        $this->path = $path . DIRECTORY_SEPARATOR;
        $this->file = $file;
        $this->isTemp = $isTemp;
    }

     protected $isTempFile = false;

    #$file = $this->data['path'] . $this->data['fileName'];

    public function __destruct() {
        if($this->isTemp) {
            unlink($this->path . $this->file);
        }
    }

    public function getAliasName() : string {
        return $this->alias;
    }

    public function isTempFile() : bool {
        return $this->isTempFile();
    }


}
