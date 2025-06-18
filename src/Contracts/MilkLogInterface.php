<?php

namespace RootAccessPlease\MilkLog\Contracts;

interface MilkLogInterface
{
    public function alert(string $message, array $context = []): self;
    public function error(string $message, array $context = []): self;
    public function info(string $message, array $context = []): self;

    public function channel(string $channel): self;
    public function tags(array $tags): self;
    public function logChannel(string $channel): self;

    public function inform(): void;
}