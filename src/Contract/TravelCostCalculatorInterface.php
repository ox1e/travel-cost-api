<?php

namespace App\Contract;

use App\DTO\TravelCostRequest;

interface TravelCostCalculatorInterface
{
    public function calculate(TravelCostRequest $request): float;
}
