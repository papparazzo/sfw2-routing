<?php

namespace SFW2\Routing\ControllerMap;

use Psr\Container\ContainerInterface;
use SFW2\Core\Container;


class ControllerData
{
    public function __construct(
        protected readonly string $className,
        protected Container $additionalData = new Container()
    )
    {
    }

    public function getClassName(): string {
        return $this->className;
    }

    public function getAdditionalData(): ContainerInterface {
        return $this->additionalData;
    }
}