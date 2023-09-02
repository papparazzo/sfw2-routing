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
 *  along with this program. If not, see <https://www.gnu.org/licenses/agpl.txt>.
 *
 */

namespace SFW2\Routing;

abstract class AbstractController {

    protected int $pathId;

    public function __construct(int $pathId) {
        $this->pathId = $pathId;
    }

    public function appendAdditionalData(array $data): void {

    }

    abstract public function index(): Content;

    /**
     * @throws HttpNotFoundException
     *
     * Übersicht auf Landingpage
     */
    public function preview(bool $all = false): AbstractResult {
        throw new HttpNotFoundException();
    }

    /**
     * @throws HttpNotFoundException
     */
    public function create(): AbstractResult {
        throw new HttpNotFoundException();
    }

    /**
     * @throws HttpNotFoundException
     */
    public function read(bool $all = false): AbstractResult {
        unset($all);
        throw new HttpNotFoundException();
    }

    /**
     * @throws HttpNotFoundException
     */
    public function update(bool $all = false): AbstractResult {
        unset($all);
        throw new HttpNotFoundException();
    }

    /**
     * @throws HttpNotFoundException
     */
    public function delete(bool $all = false): AbstractResult {
        unset($all);
        throw new HttpNotFoundException();
    }
}
