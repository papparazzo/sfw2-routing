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

namespace SFW2\Routing;

use Handlebars\Loader;
use InvalidArgumentException;

class TemplateLoader implements Loader
{
     private string $extension = '.handlebars';

    public function __construct(
        private readonly array $templateFolders,
        private readonly string $defaultNamespace = ''
    )
    {
        if(empty($this->templateFolders)) {
            throw new InvalidArgumentException("no templates-folder given");
        }
        if($this->defaultNamespace != '' && !isset($this->templateFolders[$this->defaultNamespace])) {
            throw new InvalidArgumentException("default <$this->defaultNamespace> not found in templates");
        }
    }

    public function load($name): string
    {
        $path = strtr($name, '\\', DIRECTORY_SEPARATOR) . $this->extension;
        foreach($this->templateFolders as $prefix => $dir) {
            if(!str_starts_with($name, $prefix)) {
                continue;
            }
            $file = $dir . DIRECTORY_SEPARATOR . substr($path, strlen($prefix));

            if(file_exists($file)) {
                return file_get_contents($file);
            }
        }
        if($this->defaultNamespace == '') {
            throw new InvalidArgumentException("Template <$name> not found.");
        }
        $file = $this->templateFolders[$this->defaultNamespace] . DIRECTORY_SEPARATOR  . $path;

        if(!file_exists($file)) {
            throw new InvalidArgumentException("Template <$name> not found.");
        }
        return file_get_contents($file);
    }
}