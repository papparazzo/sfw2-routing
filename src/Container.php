<?php

/**
 *  SFW2 - SimpleFrameWork
 *
 *  Copyright (C) 2023  Stefan Paproth
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

declare(strict_types=1);

namespace SFW2\Routing;

use Exception;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    public function __construct(
        protected array $data = []
    )
    {
    }

    /**
     * @throws Exception
     */
    public function get(string $id): mixed
    {
        if(!$this->has($id)) {
            throw new Exception("item with id <$id> not found"); // TODO implement NotFoundException from NotFoundExceptionInterface
        }
        return $this->data[$id];
    }

    public function has(string $id): bool
    {
        if(isset($this->data[$id])) {
            return true;
        }
        return false;
    }
}