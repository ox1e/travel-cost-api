<?php

namespace App\Contract;

interface DiscountStrategyInterface
{
    public function apply(float $cost, \DateTime $startDate, \DateTime $paymentDate): float;
}
