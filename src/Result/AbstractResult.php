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

abstract class AbstractResult {

    protected bool $error = false;

    protected bool $hasModifiedData = false;

    public function __construct(bool $hasErrors = false, bool $hasModifiedData = false) {
        $this->error = $hasErrors;
        $this->hasModifiedData = $hasModifiedData;
    }

    public function dataWereModified() {
        $this->hasModifiedData = true;
    }

    public function hasModifiedData(): bool {
        return $this->hasModifiedData;
    }

    public function setError(bool $error): void {
        $this->error = $error;
    }

    public function hasErrors(): bool {
        return $this->error;
    }

}
