<?php

namespace SFW2\Routing\ControllerMap;

class ControllerData
{
    public function __construct(
        protected readonly string $className,
        protected readonly array  $additionalData = []
    )
    {
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
}