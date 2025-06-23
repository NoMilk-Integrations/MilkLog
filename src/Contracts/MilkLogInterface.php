<?php

namespace RootAccessPlease\MilkLog\Contracts;

interface MilkLogInterface
{
    public function alert(string $message): self;
    public function error(string $message): self;
    public function warning(string $message): self;
    public function info(string $message): self;

    public function channel(string $channel): self;
    public function tags(array $tags): self;
    public function logChannel(string $channel): self;
    public function properties(array $properties): self;

    public function inform(): void;
}