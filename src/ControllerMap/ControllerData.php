<?php

namespace SFW2\Routing\ControllerMap;

readonly class ControllerData
{
    /**
     * @param class-string $className
     * @param non-empty-string $action
     * @param array<array-key, mixed> $additionalData
     */
    public function __construct(
        protected string $className,
        protected string $action = 'index',
        protected array  $additionalData = []
    ) {
    }

    /**
     * @return class-string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return non-empty-string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
}