<?php

namespace Wincy\EasySms\Strategies;

use Wincy\EasySms\Contracts\StrategyInterface;

class OrderStrategy implements StrategyInterface
{
    /**
     * @param array $gateways
     * 
     * @return array
     */
    public function apply(array $gateways)
    {
        return array_keys($gateways);
    }
}