<?php

namespace SFW2\Routing\ControllerMap;

class ControllerData
{
    /**
     * @var array<string, string>
     */
    private array $actionData = [];

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
     * @param array<string, string> $actionData
     * @return self
     */
    public function withActionParams(array $actionData): self
    {
        $new = clone $this;
        $new->actionData = $actionData;

        return $new;
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
     * @return array<string, string>
     */
    public function getActionData(): array
    {
        return $this->actionData;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
}