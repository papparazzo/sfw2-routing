<?php

/**
 *  SFW2 - SimpleFrameWork
 *
 *  Copyright (C) 2018  Stefan Paproth
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

use SFW2\Routing\Resolver\ResolverException;

class File extends AbstractResult {

    protected string $path = '';

    protected string $file = '';

    protected string $alias = '';

    protected bool $isTemp = false;

    public function __construct(string $path, string $file, string $alias = '', bool $isTemp = false) {

        if($alias == '') {
            $alias = $file;
        }
        $this->alias = $alias;
        $this->path = $path . DIRECTORY_SEPARATOR;
        $this->file = $file;
        $this->isTemp = $isTemp;

        if(!file_exists($this->getFilePathName())) {
            throw new ResolverException("file <{$this->getFilePathName()}> does not exists", ResolverException::FILE_NOT_FOUND);
        }
    }

    public function __destruct() {
        if($this->isTemp) {
            unlink($this->path . $this->file);
        }
    }

    public function getFilePathName() : string {
        return $this->path . $this->file;
    }

    public function getAliasName() : string {
        return $this->alias;
    }

    public function isTempFile() : bool {
        return $this->isTemp;
    }

    public function getFileSize() : int {
        return filesize($this->path . $this->file);
    }

    public function getMimeType() : string {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $this->path . $this->file);
        finfo_close($finfo);
        return $mimetype;
    }
}
