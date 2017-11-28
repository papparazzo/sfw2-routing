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

namespace SFW2\Routing\ResponseType;

use SFW2\Routing\ResponseType;
use SFW2\Routing\Result\File as FileContent;

class File extends ResponseType {

    public function dispatch(FileContent $file) {

        $file = $this->data['path'] . $this->data['fileName'];

        if(!file_exists($file)) {
            return false;
        }

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: ' . $file->getMimeType());
        header('Content-Disposition: attachment; filename="' . $file->getAliasName() . '";');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . $file->getFileSize());
        readfile($file);

        if($file->isTempFile()) {
            unlink($file);
        }
    }
}
