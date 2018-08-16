<?php

namespace Wincy\EasySms\Contracts;

interface StrategyInterface
{
    public function apply(array $gateways);
}